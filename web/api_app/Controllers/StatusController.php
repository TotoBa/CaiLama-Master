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

        $payload = [
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
        ];

        if (ApiTokenGuard::hasAnyScope($request, $config, ['admin'])) {
            $payload['website_auth'] = $this->websiteAuthDiagnostics($config, $cailamaProbe['status']);
        }

        return Response::json($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function websiteAuthDiagnostics(array $config, string $databaseStatus): array
    {
        if ($databaseStatus !== 'ok' || !($config['auth']['enabled'] ?? false)) {
            return ['enabled' => (bool) ($config['auth']['enabled'] ?? false), 'database' => $databaseStatus];
        }

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $schemaVersion = $pdo->query(
                'SELECT schema_version FROM cailama_schema_meta WHERE id = 1 LIMIT 1',
            )->fetchColumn();
            $usersTable = (string) ($config['auth']['users_table'] ?? 'web_users');
            $loginColumn = (string) ($config['auth']['login_column'] ?? 'login_name');
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $usersTable)
                || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $loginColumn)) {
                return ['enabled' => true, 'database' => 'error', 'diagnostic' => 'invalid_auth_config'];
            }
            $sql = sprintf(
                'SELECT w.%1$s AS login_name,
                        p.profile_key,
                        p.training_name,
                        p.display_name AS player_display_name
                 FROM `%2$s` w
                 LEFT JOIN cailama_player_profiles p
                   ON p.id = w.player_profile_id
                  AND p.status = \'active\'
                 WHERE w.%1$s = :login_name
                 LIMIT 1',
                $loginColumn,
                $usersTable,
            );
            $statement = $pdo->prepare($sql);
            $statement->execute(['login_name' => 'testuser']);
            $row = $statement->fetch();
            $linked = is_array($row)
                && ($row['training_name'] ?? '') === 'totomanie'
                && ($row['profile_key'] ?? '') === 'torsten-baublies-totomanie';

            return [
                'enabled' => true,
                'database' => 'ok',
                'schema_version' => is_string($schemaVersion) ? $schemaVersion : null,
                'testuser_profile' => [
                    'linked' => $linked,
                    'training_name' => is_array($row) ? (string) ($row['training_name'] ?? '') : '',
                    'profile_key' => is_array($row) ? (string) ($row['profile_key'] ?? '') : '',
                    'player_display_name' => is_array($row) ? (string) ($row['player_display_name'] ?? '') : '',
                ],
            ];
        } catch (Throwable) {
            return ['enabled' => true, 'database' => 'error', 'diagnostic' => 'profile_lookup_failed'];
        }
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
