{extends file="layouts/base.tpl"}

{block name="content"}
  {include file="partials/hero.tpl" hero=$page.hero}

  <section id="master">
    <div class="section-inner split">
      <div class="section-head">
        <h2>Website-Deployment.</h2>
        <p>
          Öffentliche Dateien aus <code>web/</code> werden nach <code>public/</code>
          geladen. Der private Website-Bereich aus <code>web-smarty/</code> wird
          als <code>smarty/</code> daneben bereitgestellt.
        </p>
      </div>
      <pre><code>scripts/deploy-website.sh
curl -I -L https://cailama.org/
bash scripts/check-ecosystem.sh</code></pre>
    </div>
  </section>

  <section class="band">
    <div class="section-inner">
      <div class="section-head">
        <h2>Master-Regeln.</h2>
      </div>
      {include file="partials/card-grid.tpl" cards=$page.rules grid_class="grid-3"}
    </div>
  </section>

  <section>
    <div class="section-inner split">
      <div class="section-head">
        <h2>Pflichtchecks.</h2>
        <p>Diese Checks sichern die Master-Grenzen und das Web-Deployment.</p>
      </div>
      <pre><code>find web web-smarty -name '*.php' -print0 | xargs -0 -n1 php -l
php web/index.php >/tmp/cailama-index.html
php web/projects.php >/tmp/cailama-projects.html
php web/status.php >/tmp/cailama-status.html
bash scripts/check-ecosystem.sh
git diff --check</code></pre>
    </div>
  </section>

  <section class="band">
    <div class="section-inner">
      <div class="section-head">
        <h2>Master-Dokumentation.</h2>
      </div>
      {include file="partials/card-grid.tpl" cards=$page.docs grid_class="grid-3"}
    </div>
  </section>
{/block}
