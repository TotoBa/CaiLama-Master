<?php
declare(strict_types=1);

use CaiLama\WebApi\Controllers\StatusController;
use CaiLama\WebApi\Controllers\ImportController;
use CaiLama\WebApi\Controllers\SchemaController;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;
use CaiLama\WebApi\Router;

$config = require __DIR__ . '/init.php';

$router = new Router();
$router->post('/api/v1/status', StatusController::class, 'show');
$router->post('/api/v1/imports/cailama/append', ImportController::class, 'append');
$router->post('/api/v1/imports/cailama/reset', ImportController::class, 'reset');
$router->post('/api/v1/admin/schema/auth', SchemaController::class, 'auth');
$router->post('/api/v1/admin/schema/cailama', SchemaController::class, 'cailama');
$router->post('/api/v1/admin/schema/all', SchemaController::class, 'all');

try {
    $request = Request::fromGlobals((int) ($config['max_body_bytes'] ?? 1048576));
    if ($request->bodyTooLarge) {
        Response::json([
            'error' => [
                'code' => 'body_too_large',
                'message' => 'Request body is too large.',
            ],
        ], 413)->send();
        return;
    }
    $router->dispatch($request, $config)->send();
} catch (Throwable) {
    Response::json([
        'error' => [
            'code' => 'internal_error',
            'message' => 'Internal server error.',
        ],
    ], 500)->send();
}
