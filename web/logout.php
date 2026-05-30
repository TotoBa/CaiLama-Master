<?php
declare(strict_types=1);

use CaiLama\WebApi\Auth\SessionManager;

require __DIR__ . '/_private_api.php';
$config = cailama_api_config();
$session = new SessionManager($config['session'] ?? []);
$session->start();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null;
    if ($session->validateCsrf($token)) {
        $session->logout();
    }
}

header('Location: login.php', true, 303);
