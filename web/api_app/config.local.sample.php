<?php
declare(strict_types=1);

/*
 * Copy this file to config.local.php on the target webspace and replace all
 * placeholder values there. The real config.local.php is intentionally ignored
 * by Git and protected from normal website deployment deletion.
 */
return [
    'auth' => [
        'enabled' => true,
    ],
    'databases' => [
        'auth' => [
            'enabled' => true,
            'dsn' => 'mysql:host=localhost;dbname=cailama_auth;charset=utf8mb4',
            'user' => 'replace_with_provider_auth_user',
            'password' => 'replace_with_provider_auth_password',
        ],
        'cailama' => [
            'enabled' => true,
            'dsn' => 'mysql:host=localhost;dbname=cailama_data;charset=utf8mb4',
            'user' => 'replace_with_provider_cailama_user',
            'password' => 'replace_with_provider_cailama_password',
        ],
    ],
];
