<?php
declare(strict_types=1);

use CaiLama\WebApi\Controllers\StatusController;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;
use CaiLama\WebApi\Router;

$config = require __DIR__ . '/init.php';

$router = new Router();
$router->get('/api/v1/status', StatusController::class, 'show');

try {
    $router->dispatch(Request::fromGlobals(), $config)->send();
} catch (Throwable) {
    Response::json([
        'error' => [
            'code' => 'internal_error',
            'message' => 'Internal server error.',
        ],
    ], 500)->send();
}
