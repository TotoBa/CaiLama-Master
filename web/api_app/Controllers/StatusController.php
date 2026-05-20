<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Controllers;

use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;

final class StatusController
{
    /**
     * Status stub for the DB API shell.
     *
     * The real DB connection and API-key checks are intentionally not wired
     * until the hosting secrets and schema are provisioned outside the repo.
     */
    public function show(Request $request, array $config): Response
    {
        return Response::json([
            'status' => 'ok',
            'api' => $config['api_name'] ?? 'cailama-db-api',
            'version' => $config['version'] ?? '0.1.0',
            'db' => ($config['db_enabled'] ?? false) ? 'configured' : 'not_configured',
        ]);
    }
}
