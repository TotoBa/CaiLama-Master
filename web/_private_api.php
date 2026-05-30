<?php
declare(strict_types=1);

function cailama_api_app_init_path(): string
{
    $candidates = [
        __DIR__ . '/api_app/init.php',
        dirname(__DIR__) . '/api-app/init.php',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    http_response_code(500);
    echo 'CaiLama API configuration error.';
    exit;
}

/**
 * @return array<string, mixed>
 */
function cailama_api_config(): array
{
    static $config = null;
    if (!is_array($config)) {
        $config = require cailama_api_app_init_path();
    }

    return $config;
}
