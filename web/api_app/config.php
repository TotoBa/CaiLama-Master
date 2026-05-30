<?php
declare(strict_types=1);

$config = [
    'api_name' => 'cailama-db-api',
    'version' => '0.6.0',
    'debug' => false,
    'max_body_bytes' => 1048576,
    'cors_allowed_origins' => [],
    'api_tokens' => [
        /*
         * Configure token hashes only in config.local.php.
         *
         * [
         *   'name' => 'local-sync',
         *   'hash' => 'sha256:<hash-of-bearer-token>',
         *   'scopes' => ['status:read', 'db_import:write', 'db_import:reset', 'benchmark:write', 'benchmark:feedback', 'admin'],
         * ]
         */
    ],
    'imports' => [
        'enabled' => false,
        'drop_dir' => '',
        'filename' => 'cailama-import.sql.gz',
        'allowed_extensions' => ['sql', 'sql.gz'],
        'max_file_bytes' => 2147483648,
        'allow_reset' => false,
        'max_execution_seconds' => 1800,
    ],
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
        'login_column' => 'login_name',
        'email_column' => 'email',
        'display_name_column' => 'display_name',
        'password_hash_column' => 'password_hash',
        'status_column' => 'status',
        'active_status' => 'active',
        'max_attempts_per_session' => 5,
        'attempt_window_seconds' => 600,
    ],
    'origin' => [
        'base_url' => '',
        'proxy_key' => '',
        'hmac_secret' => '',
        'timeout_seconds' => 20,
        'allowed_routes' => [
            'llm_chat' => ['method' => 'POST', 'path' => '/v1/llm/chat', 'scope' => 'llm:chat'],
            'search_query' => ['method' => 'POST', 'path' => '/v1/search/query', 'scope' => 'search:query'],
            'jobs_create' => ['method' => 'POST', 'path' => '/v1/jobs', 'scope' => 'jobs:write'],
            'jobs_list' => ['method' => 'POST', 'path' => '/v1/jobs/list', 'scope' => 'jobs:write'],
            'jobs_status' => ['method' => 'POST', 'path' => '/v1/jobs/status', 'scope' => 'jobs:write'],
            'jobs_result' => ['method' => 'POST', 'path' => '/v1/jobs/result', 'scope' => 'jobs:write'],
        ],
    ],
    'web_api' => [
        'base_url' => '',
        'session_token' => '',
        'timeout_seconds' => 60,
    ],
    'benchmark_feedback' => [
        'piece_asset_base_url' => '',
        'piece_sets' => [],
        'default_piece_set' => '',
    ],
    'databases' => [
        'cailama' => [
            'enabled' => false,
            'dsn' => '',
            'user' => '',
            'password' => '',
            'options' => [],
        ],
    ],
];

$privateConfigCandidates = [];
$envConfig = getenv('CAILAMA_WEB_API_CONFIG');
if (is_string($envConfig) && trim($envConfig) !== '') {
    $privateConfigCandidates[] = trim($envConfig);
}
$privateConfigCandidates[] = __DIR__ . '/../private-api/api/config.local.php';
$privateConfigCandidates[] = __DIR__ . '/../../private-api/api/config.local.php';
$privateConfigCandidates[] = __DIR__ . '/config.local.php';

foreach ($privateConfigCandidates as $localConfig) {
    if (!is_file($localConfig)) {
        continue;
    }
    $local = require $localConfig;
    if (is_array($local)) {
        $config = array_replace_recursive($config, $local);
    }
    break;
}

return $config;
