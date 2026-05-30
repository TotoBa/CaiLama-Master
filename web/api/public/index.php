<?php
declare(strict_types=1);

$candidates = [
    dirname(__DIR__, 2) . '/api_app/bootstrap.php',
    dirname(__DIR__, 2) . '/api-app/bootstrap.php',
    dirname(__DIR__, 3) . '/api-app/bootstrap.php',
];

foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        require $candidate;
        return;
    }
}

http_response_code(500);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'error' => [
        'code' => 'api_app_missing',
        'message' => 'API application is not configured.',
    ],
], JSON_UNESCAPED_SLASHES);
