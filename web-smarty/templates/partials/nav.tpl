<header class="site-header">
  <nav class="nav" aria-label="Hauptnavigation">
    <a class="brand" href="index.php">
      <img src="{$site.logo_small}" alt="">
      <span>{$site.name}</span>
    </a>
    <div class="nav-links">
      {foreach $nav as $item}
        <a href="{$item.href}"{if $item.id == $page.active_nav} aria-current="page"{/if}>{$item.label}</a>
      {/foreach}
    </div>
  </nav>
</header>
