{assign var=heroClass value=$hero_class|default:'page-hero'}
<section class="{$heroClass}">
  <div class="{if $heroClass == 'hero'}hero-inner hero-grid{else}page-hero-inner{/if}">
    <div>
      {if isset($hero.eyebrow)}
        <p class="eyebrow">{$hero.eyebrow}</p>
      {/if}
      <h1>{$hero.headline}</h1>
      {if isset($hero.lead)}
        <p class="{if $heroClass == 'hero'}lead{else}page-lead{/if}">
          {$hero.lead}
        </p>
      {/if}
      {if isset($hero.actions)}
        <div class="hero-actions">
          {foreach $hero.actions as $action}
            <a class="button {$action.class|default:''}" href="{$action.href}">{$action.label}</a>
          {/foreach}
        </div>
      {/if}
    </div>
  </div>
</section>
