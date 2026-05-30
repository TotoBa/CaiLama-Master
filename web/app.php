<?php
declare(strict_types=1);

use CaiLama\WebApi\Auth\SessionManager;
use CaiLama\WebApi\Auth\UserProfileService;
use CaiLama\WebApi\Db\ConnectionFactory;

require __DIR__ . '/_private_api.php';
$config = cailama_api_config();
$session = new SessionManager($config['session'] ?? []);
$session->start();
$user = $session->currentUser();

if ($user === null) {
    header('Location: login.php', true, 303);
    exit;
}

try {
    $pdo = ConnectionFactory::fromConfig($config, 'cailama');
    $user = (new UserProfileService($pdo, $config['auth'] ?? []))->attachProfile($user);
} catch (Throwable) {
    // Profile lookup is optional.
}

$privateApp = dirname(__DIR__) . '/web-smarty';
if (!is_file($privateApp . '/bootstrap.php')) {
    $privateApp = dirname(__DIR__) . '/smarty';
}
require_once $privateApp . '/bootstrap.php';

$page = require $privateApp . '/content/pages/app.php';
$page['user'] = $user;
$page['csrf_token'] = $session->csrfToken();
$page['api_base'] = 'app-api.php?path=';
$page['debug'] = isset($_GET['debug']) && (string) $_GET['debug'] === '1';

cailama_render('app/chat.tpl', $page);
