<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Schema;

use PDO;
use RuntimeException;

final class SchemaInstaller
{
    public function apply(PDO $pdo, string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('Schema file is not readable.');
        }

        $sql = file_get_contents($path);
        if (!is_string($sql)) {
            throw new RuntimeException('Schema file cannot be read.');
        }

        $stats = [
            'statements_seen' => 0,
            'statements_executed' => 0,
        ];

        try {
            foreach ($this->statements($sql) as $statement) {
                $statement = trim($statement);
                if ($statement === '') {
                    continue;
                }
                if (!$this->isAllowedSchemaStatement($statement)) {
                    throw new RuntimeException('Schema rejected a database-level SQL statement.');
                }
                $stats['statements_seen']++;
                $pdo->exec($statement);
                $stats['statements_executed']++;
            }
        } catch (RuntimeException $exc) {
            throw $exc;
        } catch (\Throwable $exc) {
            throw new RuntimeException('Schema setup failed.', 0, $exc);
        }

        return $stats;
    }

    private function statements(string $sql): \Generator
    {
        $buffer = '';
        $single = false;
        $double = false;
        $backtick = false;
        $lineComment = false;
        $blockComment = false;
        $escape = false;
        $previous = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

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

        if (trim($buffer) !== '') {
            yield $buffer;
        }
    }

    private function isAllowedSchemaStatement(string $statement): bool
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
}
