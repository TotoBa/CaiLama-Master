<?php
declare(strict_types=1);

function cailama_private_app_dir(): string
{
    $candidates = [
        dirname(__DIR__) . '/smarty',
        dirname(__DIR__) . '/web-smarty',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate . '/bootstrap.php')) {
            return $candidate;
        }
    }

    http_response_code(500);
    echo 'CaiLama website configuration error.';
    exit;
}

function cailama_render_public_page(string $pageId, ?string $template = null): void
{
    $privateApp = cailama_private_app_dir();

    require_once $privateApp . '/bootstrap.php';

    cailama_render_page($pageId, $template);
}
