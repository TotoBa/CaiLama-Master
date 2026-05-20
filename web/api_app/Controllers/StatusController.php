<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Controllers;

use CaiLama\WebApi\Db\ConnectionFactory;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;
use Throwable;

final class StatusController
{
    /**
     * Status endpoint for the DB API shell. It reports only neutral database
     * states and never exposes DSNs, usernames or driver errors.
     */
    public function show(Request $request, array $config): Response
    {
        return Response::json([
            'status' => 'ok',
            'api' => $config['api_name'] ?? 'cailama-db-api',
            'version' => $config['version'] ?? '0.1.0',
            'databases' => [
                'auth' => $this->databaseStatus($config, 'auth'),
                'cailama' => $this->databaseStatus($config, 'cailama'),
            ],
        ]);
    }

    private function databaseStatus(array $config, string $name): string
    {
        try {
            $connection = ConnectionFactory::fromConfig($config, $name);
            $connection->query('SELECT 1');
            return 'ok';
        } catch (Throwable) {
            $database = $config['databases'][$name] ?? [];
            return is_array($database) && ($database['enabled'] ?? false) ? 'error' : 'not_configured';
        }
    }
}
