<footer class="footer">
  <div class="section-inner">
    <span>{$page.footer_label|default:$site.name}</span>
    {if isset($page.footer_links)}
      {foreach $page.footer_links as $link}
        <a href="{$link.href}">{$link.label}</a>
      {/foreach}
    {/if}
    <a href="mailto:{$site.contact_email}">Kontakt</a>
  </div>
</footer>
