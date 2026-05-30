<?php
declare(strict_types=1);

use CaiLama\WebApi\Auth\SessionManager;
use CaiLama\WebApi\Auth\UserProfileService;
use CaiLama\WebApi\Controllers\WebAppProxyController;
use CaiLama\WebApi\Db\ConnectionFactory;
use CaiLama\WebApi\Http\Request;

require __DIR__ . '/_private_api.php';
$config = cailama_api_config();
$session = new SessionManager($config['session'] ?? []);
$session->start();

$user = $session->currentUser();
if ($user === null) {
    http_response_code(401);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error' => ['code' => 'unauthorized', 'message' => 'Login required.']]);
    exit;
}

$path = is_string($_GET['path'] ?? null) ? '/' . ltrim((string) $_GET['path'], '/') : '/';
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$body = file_get_contents('php://input');
if (!is_string($body)) {
    $body = '';
}

$request = new Request(
    $method,
    '/app/api' . $path,
    $_GET,
    [],
    $body,
    false,
);

$controller = new WebAppProxyController();
$response = $controller->dispatch($request, $config, $session);
$response->send();
