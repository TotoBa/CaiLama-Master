{extends file="layouts/base.tpl"}

{block name="content"}
  {include file="partials/hero.tpl" hero=$page.hero}

  <section>
    <div class="section-inner">
      <div class="section-head">
        <h2>Aktueller Stand.</h2>
        <p>Der Master hält den tatsächlichen Stand der getrennten Repositories zusammen.</p>
      </div>
      {include file="partials/metric-grid.tpl" metrics=$page.metrics}
    </div>
  </section>

  <section class="band">
    <div class="section-inner">
      <div class="section-head">
        <h2>Repo-Übersicht.</h2>
      </div>
      <div class="grid-2">
        {foreach $page.repos as $repo}
          <article class="repo-card">
            <div>
              <span class="tag {$repo.tag_class|default:''}">{$repo.tag}</span>
              <h3>{$repo.name}</h3>
              <p>{$repo.summary}</p>
              <ul>
                {foreach $repo.points as $point}
                  <li>{$point}</li>
                {/foreach}
              </ul>
            </div>
            <a href="{$repo.href}">Details</a>
          </article>
        {/foreach}
      </div>
    </div>
  </section>

  <section>
    <div class="section-inner">
      <div class="section-head">
        <h2>Nächste Plattformstufe.</h2>
      </div>
      {include file="partials/timeline.tpl" items=$page.next}
    </div>
  </section>
{/block}
