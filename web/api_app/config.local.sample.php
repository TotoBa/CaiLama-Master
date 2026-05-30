<?php
declare(strict_types=1);

/*
 * Use this file only as a placeholder reference. Real config.local.php files
 * belong outside the public document root. The setup script can write and
 * deploy that private file from local operator configuration.
 */
return [
    'api_tokens' => [
        [
            'name' => 'local-sync',
            'hash' => 'sha256:replace_with_sha256_hash_of_bearer_token',
            'scopes' => ['status:read', 'db_import:write', 'db_import:reset', 'benchmark:write', 'benchmark:feedback', 'admin'],
        ],
    ],
    'imports' => [
        'enabled' => true,
        /*
         * Relative paths are resolved from /public/api_app/ on the webspace.
         * Configure a non-public sibling directory in the private webspace
         * area. Do not put real local paths into this sample file.
         */
        'drop_dir' => '../../private-imports',
        'filename' => 'cailama-import.sql.gz',
        'allowed_extensions' => ['sql', 'sql.gz'],
        'max_file_bytes' => 2147483648,
        'allow_reset' => false,
        'max_execution_seconds' => 1800,
    ],
    'auth' => [
        'enabled' => true,
    ],
    'origin' => [
        'base_url' => 'https://replace-with-origin-host.example',
        'proxy_key' => 'replace_with_origin_proxy_key',
        'hmac_secret' => 'replace_with_origin_hmac_secret',
        'timeout_seconds' => 20,
    ],
    'benchmark_feedback' => [
        /*
         * Optional public URL prefix for mirrored chess piece images.
         * Keep local runtime paths out of this file. Example:
         *   'piece_asset_base_url' => '/assets/chesspieces',
         *   'piece_sets' => ['merida' => 'merida'],
         *   'default_piece_set' => 'merida',
         */
        'piece_asset_base_url' => '',
        'piece_sets' => [],
        'default_piece_set' => '',
    ],
    'databases' => [
        'cailama' => [
            'enabled' => true,
            // Single provider database for website login and CaiLama application data.
            'dsn' => 'mysql:host=localhost;dbname=cailama_data;charset=utf8mb4',
            'user' => 'replace_with_provider_cailama_user',
            'password' => 'replace_with_provider_cailama_password',
        ],
    ],
];
