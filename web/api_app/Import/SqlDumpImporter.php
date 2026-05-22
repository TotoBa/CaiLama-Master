<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Import;

use PDO;
use RuntimeException;

final class SqlDumpImporter
{
    private const APPEND_ALLOWED_KEYWORDS = [
        'BEGIN',
        'COMMIT',
        'INSERT',
        'LOCK',
        'ROLLBACK',
        'SET',
        'START',
        'UNLOCK',
    ];

    private const DESTRUCTIVE_KEYWORDS = [
        'ALTER',
        'CREATE',
        'DELETE',
        'DROP',
        'RENAME',
        'TRUNCATE',
        'UPDATE',
    ];

    public function import(PDO $pdo, string $path, string $mode, bool $dryRun = false): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('Import file is not readable.');
        }
        if (!in_array($mode, ['reset', 'append'], true)) {
            throw new RuntimeException('Import mode must be reset or append.');
        }

        $stats = [
            'mode' => $mode,
            'dry_run' => $dryRun,
            'statements_seen' => 0,
            'statements_executed' => 0,
            'statements_skipped' => 0,
            'tables_dropped' => 0,
        ];

        if ($mode === 'reset' && !$dryRun) {
            $stats['tables_dropped'] = $this->dropAllTables($pdo);
        }

        try {
            foreach ($this->statements($path) as $statement) {
                $statement = trim($statement);
                if ($statement === '') {
                    continue;
                }
                $stats['statements_seen']++;

                if ($mode === 'append' && !$this->isAllowedAppendStatement($statement)) {
                    throw new RuntimeException('Append mode rejected a non-append SQL statement.');
                }
                if ($mode === 'reset' && !$this->isAllowedResetStatement($statement)) {
                    throw new RuntimeException('Reset mode rejected a database-level SQL statement.');
                }

                if ($dryRun) {
                    $stats['statements_skipped']++;
                    continue;
                }

                $pdo->exec($statement);
                $stats['statements_executed']++;
            }
        } catch (RuntimeException $exc) {
            throw $exc;
        } catch (\Throwable $exc) {
            throw new RuntimeException('SQL import failed.', 0, $exc);
        }

        return $stats;
    }

    private function statements(string $path): \Generator
    {
        $buffer = '';
        $single = false;
        $double = false;
        $backtick = false;
        $lineComment = false;
        $blockComment = false;
        $escape = false;
        $previous = '';
        $isGz = str_ends_with(strtolower($path), '.gz');
        $handle = $this->open($path, $isGz);

        try {
            while (($chunk = $this->read($handle, $isGz)) !== '') {
                $length = strlen($chunk);
                for ($i = 0; $i < $length; $i++) {
                    $char = $chunk[$i];
                    $next = $i + 1 < $length ? $chunk[$i + 1] : '';

                    if ($lineComment) {
                        $buffer .= $char;
                        if ($char === "\n") {
                            $lineComment = false;
                        }
                        $previous = $char;
                        continue;
                    }
                    if ($blockComment) {
                        $buffer .= $char;
                        if ($previous === '*' && $char === '/') {
                            $blockComment = false;
                        }
                        $previous = $char;
                        continue;
                    }

                    if (!$single && !$double && !$backtick) {
                        if ($char === '-' && $next === '-' && ($previous === '' || ctype_space($previous))) {
                            $lineComment = true;
                            $buffer .= $char;
                            $previous = $char;
                            continue;
                        }
                        if ($char === '#') {
                            $lineComment = true;
                            $buffer .= $char;
                            $previous = $char;
                            continue;
                        }
                        if ($char === '/' && $next === '*') {
                            $blockComment = true;
                            $buffer .= $char;
                            $previous = $char;
                            continue;
                        }
                    }

                    if ($escape) {
                        $buffer .= $char;
                        $escape = false;
                        $previous = $char;
                        continue;
                    }
                    if (($single || $double) && $char === '\\') {
                        $buffer .= $char;
                        $escape = true;
                        $previous = $char;
                        continue;
                    }

                    if (!$double && !$backtick && $char === "'") {
                        $single = !$single;
                    } elseif (!$single && !$backtick && $char === '"') {
                        $double = !$double;
                    } elseif (!$single && !$double && $char === '`') {
                        $backtick = !$backtick;
                    }

                    if (!$single && !$double && !$backtick && $char === ';') {
                        yield $buffer;
                        $buffer = '';
                        $previous = '';
                        continue;
                    }

                    $buffer .= $char;
                    $previous = $char;
                }
            }
        } finally {
            $this->close($handle, $isGz);
        }

        if (trim($buffer) !== '') {
            yield $buffer;
        }
    }

    private function isAllowedAppendStatement(string $statement): bool
    {
        $keyword = $this->keyword($statement);
        if ($keyword === '') {
            return true;
        }
        if (in_array($keyword, self::DESTRUCTIVE_KEYWORDS, true)) {
            return false;
        }
        return in_array($keyword, self::APPEND_ALLOWED_KEYWORDS, true);
    }

    private function isAllowedResetStatement(string $statement): bool
    {
        $clean = preg_replace('/\/\*![0-9]{5}\s*(.*?)\*\//s', '$1', $statement) ?? $statement;
        $clean = preg_replace('/\/\*.*?\*\//s', ' ', $clean) ?? $clean;
        if (preg_match('/\b(CREATE|DROP)\s+DATABASE\b/i', $clean)) {
            return false;
        }
        if (preg_match('/^\s*USE\s+/i', $clean)) {
            return false;
        }
        return true;
    }

    private function keyword(string $statement): string
    {
        $clean = preg_replace('/\/\*![0-9]{5}\s*(.*?)\*\//s', '$1', $statement) ?? $statement;
        $clean = preg_replace('/\/\*.*?\*\//s', ' ', $clean) ?? $clean;
        $clean = preg_replace('/^\s*(--[^\n]*|#[^\n]*)/m', ' ', $clean) ?? $clean;
        if (!preg_match('/^\s*([A-Za-z]+)/', $clean, $matches)) {
            return '';
        }
        return strtoupper($matches[1]);
    }

    private function dropAllTables(PDO $pdo): int
    {
        $tables = [];
        foreach ($pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'") as $row) {
            $tables[] = (string) array_values($row)[0];
        }

        if (!$tables) {
            return 0;
        }

        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            foreach ($tables as $table) {
                $pdo->exec('DROP TABLE IF EXISTS `' . str_replace('`', '``', $table) . '`');
            }
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        }
        return count($tables);
    }

    private function open(string $path, bool $isGz): mixed
    {
        if ($isGz) {
            $handle = gzopen($path, 'rb');
        } else {
            $handle = fopen($path, 'rb');
        }
        if ($handle === false) {
            throw new RuntimeException('Import file cannot be opened.');
        }
        return $handle;
    }

    private function read(mixed $handle, bool $isGz): string
    {
        if ($isGz) {
            $chunk = gzread($handle, 65536);
        } else {
            $chunk = fread($handle, 65536);
        }
        return $chunk === false ? '' : $chunk;
    }

    private function close(mixed $handle, bool $isGz): void
    {
        if ($isGz) {
            gzclose($handle);
        } else {
            fclose($handle);
        }
    }
}
