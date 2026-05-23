{extends file="layouts/base.tpl"}

{block name="content"}
  {include file="partials/hero.tpl" hero=$page.hero}

  <section>
    <div class="section-inner">
      <div class="section-head">
        <h2>Hauptfluss.</h2>
        <p>Die Richtung ist bewusst einfach: CaiLama konsumiert Router und Search. Beide Dienste bleiben eigenständig deploybar.</p>
      </div>
      <div class="diagram">
        <div class="flow">
          <div class="flow-node">
            <strong>CaiLama</strong>
            <p>Analyse, Schlüsselstellungen, Karten, Review.</p>
          </div>
          <div class="arrow">→</div>
          <div class="flow-node">
            <strong>LLM-Router</strong>
            <p>Modelle, Aliase, Fallbacks, Usage.</p>
          </div>
          <div class="arrow">↘</div>
          <div class="flow-node">
            <strong>CaiLama-Search</strong>
            <p>Quellen, DWZ, Kontext, RAG.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="band">
    <div class="section-inner">
      <div class="section-head">
        <h2>Verträge.</h2>
      </div>
      {include file="partials/table.tpl" headers=['Richtung','Vertrag','Regeln'] rows=$page.contracts}
    </div>
  </section>

  <section>
    <div class="section-inner">
      <div class="section-head">
        <h2>Architekturprinzipien.</h2>
      </div>
      {include file="partials/card-grid.tpl" cards=$page.principles grid_class="grid-3"}
    </div>
  </section>
{/block}
