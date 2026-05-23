<?php
declare(strict_types=1);

use Smarty\Smarty;

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    echo 'CaiLama website dependency error. Required dependency: smarty/smarty ^5.0.';
    exit;
}

require_once $autoload;

function cailama_smarty(): Smarty
{
    $smarty = new Smarty();

    $smarty->setTemplateDir(__DIR__ . '/templates');
    $smarty->setCompileDir(__DIR__ . '/cache/templates_c');
    $smarty->setCacheDir(__DIR__ . '/cache/smarty');
    $smarty->escape_html = true;

    $site = require __DIR__ . '/content/site.php';
    $nav = require __DIR__ . '/content/nav.php';

    $smarty->assign('site', $site);
    $smarty->assign('nav', $nav);

    return $smarty;
}

function cailama_render(string $template, array $page): void
{
    $smarty = cailama_smarty();
    $smarty->assign('page', $page);
    $smarty->display($template);
}

function cailama_render_page(string $pageId, ?string $template = null): void
{
    if (!preg_match('/^[a-z][a-z0-9_-]*$/', $pageId)) {
        http_response_code(404);
        echo 'CaiLama website page not found.';
        exit;
    }

    $pageFile = __DIR__ . '/content/pages/' . $pageId . '.php';
    if (!is_file($pageFile)) {
        http_response_code(404);
        echo 'CaiLama website page not found.';
        exit;
    }

    $page = require $pageFile;
    cailama_render($template ?? ('pages/' . $pageId . '.tpl'), $page);
}
