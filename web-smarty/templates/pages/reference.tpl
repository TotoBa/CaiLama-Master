{extends file="layouts/base.tpl"}

{block name="content"}
  {include file="partials/hero.tpl" hero=$page.hero}

  <section>
    <div class="section-inner">
      <div class="section-head">
        <h2>Human-Version.</h2>
        <p>Diese Seiten bilden die strukturierte Web-Dokumentation für das gesamte CaiLama-Ökosystem.</p>
      </div>
      <div class="grid-3">
        {foreach $page.human_refs as $ref}
          <article class="doc-card">
            <h3>{$ref.title}</h3>
            <p>{$ref.text}</p>
            <p><a href="{$ref.href}">{$ref.href}</a></p>
          </article>
        {/foreach}
      </div>
    </div>
  </section>

  <section class="band">
    <div class="section-inner">
      <div class="section-head">
        <h2>LLM-freundliche Version.</h2>
      </div>
      <div class="grid-3">
        {foreach $page.llm_refs as $ref}
          <article class="doc-card">
            <span class="tag {$ref.tag_class|default:''}">{$ref.tag}</span>
            <h3>{$ref.title}</h3>
            <p>{$ref.text}</p>
            <p><a href="{$ref.href}">{$ref.href}</a></p>
          </article>
        {/foreach}
      </div>
    </div>
  </section>

  <section>
    <div class="section-inner split">
      <div class="section-head">
        <h2>Agenten-Kontext.</h2>
        <p>
          Ein LLM soll zuerst <code>llms.txt</code>, dann
          <code>ecosystem-reference.md</code> und bei strukturierter Verarbeitung
          <code>data/ecosystem.json</code> lesen. Für konkrete Codearbeit gilt
          danach das jeweilige <code>AGENTS.md</code> im Ziel-Repository.
        </p>
      </div>
      <pre><code>https://cailama.org/llms.txt
https://cailama.org/ecosystem-reference.md
https://cailama.org/data/ecosystem.json</code></pre>
    </div>
  </section>
{/block}
