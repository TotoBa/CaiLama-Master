{extends file="layouts/base.tpl"}

{block name="content"}
  {include file="partials/hero.tpl" hero=$page.hero}

  <section>
    <div class="section-inner">
      <div class="grid-2">
        {foreach $page.phases as $phase}
          <article class="card">
            <span class="tag {$phase.tag_class|default:''}">{$phase.tag}</span>
            <h3>{$phase.title}</h3>
            <ul class="rich-list">
              {foreach $phase.items as $item}
                <li>{$item}</li>
              {/foreach}
            </ul>
          </article>
        {/foreach}
      </div>
    </div>
  </section>
{/block}
