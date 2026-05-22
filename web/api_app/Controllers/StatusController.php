<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Controllers;

use CaiLama\WebApi\Auth\ApiTokenGuard;
use CaiLama\WebApi\Db\ConnectionFactory;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;
use Throwable;

final class StatusController
{
    /**
     * Protected status endpoint for the DB API shell. It reports only neutral
     * database states and never exposes DSNs, usernames or driver errors.
     */
    public function show(Request $request, array $config): Response
    {
        if (!ApiTokenGuard::hasAnyScope($request, $config, ['status:read', 'admin'])) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }
        if ($request->query !== []) {
            return $this->error('query_not_allowed', 'Query parameters are not allowed for this endpoint.', 400);
        }
        if ($request->body !== '') {
            return $this->error('body_not_allowed', 'Request body is not allowed for this endpoint.', 400);
        }

        $cailamaProbe = $this->databaseProbe($config, 'cailama');

        return Response::json([
            'status' => 'ok',
            'api' => $config['api_name'] ?? 'cailama-db-api',
            'version' => $config['version'] ?? '0.1.0',
            'capabilities' => [
                'pdo_mysql' => in_array('mysql', \PDO::getAvailableDrivers(), true) ? 'available' : 'missing',
            ],
            'databases' => [
                'cailama' => $cailamaProbe['status'],
            ],
            'diagnostics' => [
                'cailama' => $cailamaProbe['diagnostic'],
            ],
        ]);
    }

    private function databaseProbe(array $config, string $name): array
    {
        try {
            $connection = ConnectionFactory::fromConfig($config, $name);
            $connection->query('SELECT 1');
            return ['status' => 'ok', 'diagnostic' => 'ok'];
        } catch (Throwable $exc) {
            $database = $config['databases'][$name] ?? [];
            if (!is_array($database) || !($database['enabled'] ?? false)) {
                return ['status' => 'not_configured', 'diagnostic' => 'not_configured'];
            }
            return ['status' => 'error', 'diagnostic' => $this->classifyDatabaseError($exc)];
        }
    }

    private function classifyDatabaseError(Throwable $exc): string
    {
        $message = strtolower($exc->getMessage());
        if (str_contains($message, 'could not find driver')) {
            return 'driver_missing';
        }
        if (str_contains($message, '[1045]')) {
            return 'auth_failed';
        }
        if (str_contains($message, '[1049]')) {
            return 'unknown_database';
        }
        if (str_contains($message, '[2002]')) {
            return 'connection_failed';
        }
        if (str_contains($message, '[2005]')) {
            return 'unknown_host';
        }
        return 'error';
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
