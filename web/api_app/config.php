<?php
declare(strict_types=1);

$config = [
    'api_name' => 'cailama-db-api',
    'version' => '0.2.0',
    'debug' => false,
    'max_body_bytes' => 1048576,
    'cors_allowed_origins' => [],
    'session' => [
        'name' => 'cailama_session',
        'cookie_lifetime' => 0,
        'cookie_secure' => 'auto',
        'cookie_samesite' => 'Lax',
        'idle_timeout_seconds' => 1800,
    ],
    'auth' => [
        'enabled' => false,
        'users_table' => 'web_users',
        'id_column' => 'id',
        'email_column' => 'email',
        'display_name_column' => 'display_name',
        'password_hash_column' => 'password_hash',
        'status_column' => 'status',
        'active_status' => 'active',
        'max_attempts_per_session' => 5,
        'attempt_window_seconds' => 600,
    ],
    'databases' => [
        'auth' => [
            'enabled' => false,
            'dsn' => '',
            'user' => '',
            'password' => '',
            'options' => [],
        ],
        'cailama' => [
            'enabled' => false,
            'dsn' => '',
            'user' => '',
            'password' => '',
            'options' => [],
        ],
    ],
];

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $local = require $localConfig;
    if (is_array($local)) {
        $config = array_replace_recursive($config, $local);
    }
}

return $config;
