{extends file="layouts/base.tpl"}

{block name="content"}
  {include file="partials/hero.tpl" hero=$page.hero}

  <section>
    <div class="section-inner">
      <div class="grid-2">
        {foreach $page.projects as $project}
          <article id="{$project.id}" class="repo-card">
            <div>
              <span class="tag {$project.tag_class|default:''}">{$project.tag}</span>
              <h3>{$project.name}</h3>
              <p>{$project.summary}</p>
              <p><strong>Stand:</strong> {$project.status}</p>
              <p><strong>Offen:</strong> {$project.open}</p>
            </div>
            <a href="{$project.repo_url}">Repository</a>
          </article>
        {/foreach}
      </div>
    </div>
  </section>
{/block}
