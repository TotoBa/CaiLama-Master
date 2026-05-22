<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Controllers;

use CaiLama\WebApi\Auth\ApiTokenGuard;
use CaiLama\WebApi\Db\ConnectionFactory;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Import\SqlDumpImporter;
use CaiLama\WebApi\Response;
use RuntimeException;

final class ImportController
{
    public function append(Request $request, array $config): Response
    {
        return $this->runConfiguredImport($request, $config, 'append');
    }

    public function reset(Request $request, array $config): Response
    {
        return $this->runConfiguredImport($request, $config, 'reset');
    }

    private function runConfiguredImport(Request $request, array $config, string $mode): Response
    {
        $requiredScopes = $mode === 'reset' ? ['db_import:reset', 'admin'] : ['db_import:write', 'admin'];
        if (!ApiTokenGuard::hasAnyScope($request, $config, $requiredScopes)) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }
        if ($request->body !== '') {
            return $this->error('body_not_allowed', 'Request body is not allowed for this endpoint.', 400);
        }
        if ($request->query !== []) {
            return $this->error('query_not_allowed', 'Query parameters are not allowed for this endpoint.', 400);
        }

        $imports = $config['imports'] ?? [];
        if (!is_array($imports) || !($imports['enabled'] ?? false)) {
            return $this->error('not_configured', 'Import API is not configured.', 503);
        }

        $filename = trim((string) ($imports['filename'] ?? ''));
        $dryRun = (bool) ($imports['dry_run'] ?? false);

        if ($filename === '' || basename($filename) !== $filename) {
            return $this->error('invalid_config', 'Invalid configured import filename.', 503);
        }
        if ($mode === 'reset' && !($imports['allow_reset'] ?? false)) {
            return $this->error('reset_disabled', 'Reset imports are disabled.', 403);
        }

        try {
            $file = $this->resolveImportFile($imports, $filename);
            $this->validateExtension($imports, $file);
            $this->validateSize($imports, $file);
            $this->extendExecutionTime($imports);

            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $stats = (new SqlDumpImporter())->import($pdo, $file, $mode, $dryRun);
            $fileDeleted = false;
            if (!$dryRun) {
                $fileDeleted = $this->deleteImportFile($file);
            }
        } catch (RuntimeException $exc) {
            if ($exc->getMessage() === 'No configured import file is available.') {
                return $this->error('no_import_file', 'No configured import file is available.', 409);
            }
            if ($exc->getMessage() === 'Import succeeded, but cleanup of the import file failed.') {
                return $this->error('cleanup_failed', 'Import succeeded, but cleanup of the import file failed.', 500);
            }
            return $this->error('import_failed', $exc->getMessage(), 400);
        } catch (\Throwable) {
            return $this->error('import_failed', 'Import failed.', 500);
        }

        return Response::json([
            'status' => $dryRun ? 'validated' : 'imported',
            'target_database' => 'cailama',
            'filename' => $filename,
            'mode' => $mode,
            'file_deleted' => $fileDeleted,
            'stats' => $stats,
        ]);
    }

    private function resolveImportFile(array $imports, string $filename): string
    {
        $dropDir = trim((string) ($imports['drop_dir'] ?? ''));
        if ($dropDir === '') {
            throw new RuntimeException('Import drop directory is not configured.');
        }
        if ($dropDir[0] !== '/') {
            $dropDir = __DIR__ . '/../' . $dropDir;
        }

        $dropRoot = realpath($dropDir);
        if ($dropRoot === false || !is_dir($dropRoot)) {
            throw new RuntimeException('Import drop directory is not available.');
        }

        $file = realpath($dropRoot . '/' . $filename);
        if ($file === false || !is_file($file) || !is_readable($file)) {
            throw new RuntimeException('No configured import file is available.');
        }
        if (!str_starts_with($file, rtrim($dropRoot, '/') . '/')) {
            throw new RuntimeException('Import file is outside the drop directory.');
        }
        return $file;
    }

    private function deleteImportFile(string $file): bool
    {
        if (!is_file($file)) {
            return true;
        }
        if (!@unlink($file)) {
            throw new RuntimeException('Import succeeded, but cleanup of the import file failed.');
        }
        return true;
    }

    private function validateExtension(array $imports, string $file): void
    {
        $allowed = $imports['allowed_extensions'] ?? ['sql', 'gz'];
        if (!is_array($allowed)) {
            $allowed = ['sql', 'gz'];
        }
        $lower = strtolower($file);
        foreach ($allowed as $extension) {
            $suffix = '.' . ltrim(strtolower((string) $extension), '.');
            if (str_ends_with($lower, $suffix)) {
                return;
            }
        }
        throw new RuntimeException('Import file extension is not allowed.');
    }

    private function validateSize(array $imports, string $file): void
    {
        $maxBytes = (int) ($imports['max_file_bytes'] ?? 0);
        if ($maxBytes <= 0) {
            return;
        }
        $size = filesize($file);
        if ($size !== false && $size > $maxBytes) {
            throw new RuntimeException('Import file is too large for the configured limit.');
        }
    }

    private function extendExecutionTime(array $imports): void
    {
        $seconds = (int) ($imports['max_execution_seconds'] ?? 0);
        if ($seconds > 0 && function_exists('set_time_limit')) {
            @set_time_limit($seconds);
        }
    }

    private function error(string $code, string $message, int $status): Response
    {
        return Response::json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }
}
