<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="{$page.meta_description}">
{if isset($page.noindex) && $page.noindex}
  <meta name="robots" content="noindex, nofollow">
{/if}
{if isset($page.og)}
  <meta property="og:title" content="{$page.og.title}">
  <meta property="og:description" content="{$page.og.description}">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{$site.base_url}{$page.canonical_path}">
  <meta property="og:image" content="{$site.logo_big}">
{/if}
<title>{$page.title}</title>
<link rel="canonical" href="{$site.base_url}{$page.canonical_path}">
<link rel="stylesheet" href="assets/styles.css">
<link rel="icon" href="./favicon.ico" type="image/x-icon">
