<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'CaiLama\\WebApi\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use CaiLama\WebApi\Controllers\StatusController;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;
use CaiLama\WebApi\Router;

$config = require __DIR__ . '/config.php';

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
