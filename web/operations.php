<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Betrieb, Website-Deployment und Qualitaetsregeln des CaiLama-Master-Repositories.">
  <title>CaiLama - Betrieb</title>
  <link rel="stylesheet" href="assets/styles.css">
  <link rel="icon" href="./favicon.ico" type="image/x-icon">
</head>
<body>
  <header class="site-header">
    <nav class="nav" aria-label="Hauptnavigation">
      <a class="brand" href="index.php">
        <img src="https://raw.githubusercontent.com/TotoBa/CaiLama/main/img/logo-small.png" alt="">
        <span>CaiLama</span>
      </a>
      <div class="nav-links">
        <a href="projects.php">Projekte</a>
        <a href="architecture.php">Architektur</a>
        <a href="roadmap.php">Roadmap</a>
        <a aria-current="page" href="operations.php">Betrieb</a>
        <a href="reference.php">Referenz</a>
        <a href="login.php">Login</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="page-hero">
      <div class="page-hero-inner">
        <p class="eyebrow">Master-Betrieb</p>
        <h1>Webseite, Checks, keine Secrets.</h1>
        <p class="page-lead">
          Der Master ist die lesbare Doku-Schicht: statische Website,
          Roadmap, Status, Cross-Repo-Regeln und lokale Orchestrierungschecks.
        </p>
      </div>
    </section>

    <section id="master">
      <div class="section-inner split">
        <div class="section-head">
          <h2>Website-Deployment.</h2>
          <p>
            <code>https://cailama.org/</code> wird aus einem host-spezifischen
            PHP-Webspace ausgeliefert. Versionierte Quelle ist <code>web/</code>.
          </p>
        </div>
        <pre><code>scripts/deploy-website.sh &lt;webspace-public-dir&gt;
curl -I -L https://cailama.org/
bash scripts/check-ecosystem.sh</code></pre>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Master-Regeln.</h2>
        </div>
        <div class="grid-3">
          <article class="card">
            <h3>Keine Unter-Repos</h3>
            <p><code>CaiLama/</code>, <code>CaiLama-LLM-Router/</code> und <code>CaiLama-Search/</code> bleiben ignoriert und werden nicht als Submodules angelegt.</p>
          </article>
          <article class="card">
            <h3>Keine Secrets</h3>
            <p>Keine <code>.env</code>, Tokens, API-Keys, Zertifikate, Passwoerter oder lokalen Credential-Werte im Master.</p>
          </article>
          <article class="card">
            <h3>Keine Runtime</h3>
            <p>Der Master koordiniert, dokumentiert und prueft. Produktive Logik gehoert in die Ziel-Repos.</p>
          </article>
          <article class="card">
            <h3>Login-Konfig</h3>
            <p>Die Website liest echte Auth- und CaiLama-DB-Zugaenge nur aus der ignorierten <code>config.local.php</code>.</p>
          </article>
        </div>
      </div>
    </section>

    <section>
      <div class="section-inner split">
        <div class="section-head">
          <h2>Pflichtchecks.</h2>
          <p>Diese Checks sichern die Master-Grenzen und das Web-Deployment.</p>
        </div>
        <pre><code>pwd
git rev-parse --show-toplevel
git status --short
find . -maxdepth 2 -name .git -type d
git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search prompt.md
bash scripts/check-ecosystem.sh
git diff --check</code></pre>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Master-Dokumentation.</h2>
          <p>Die Markdown- und JSON-Doku bleibt die Quellwahrheit fuer Regeln und LLM-Kontext; die Website ist die lesbare Ausgabe.</p>
        </div>
        <div class="grid-3">
          <article class="doc-card">
            <h3>docs/roadmap.md</h3>
            <p>Roadmap aus dem Statusplan mit Ziel-Repos und Koordinationspunkten.</p>
          </article>
          <article class="doc-card">
            <h3>docs/integrations.md</h3>
            <p>Schnittstellen, Rollen, Endpunkte und Smoke-Test-Grenzen.</p>
          </article>
          <article class="doc-card">
            <h3>docs/quality.md</h3>
            <p>Index-Regeln, Statusdateien, TODO-Konsistenz und Inhaltspruefung.</p>
          </article>
          <article class="doc-card">
            <h3>docs/local-setup.md</h3>
            <p>Lokaler Checkout, Webspace und Konfiguration ohne Secrets.</p>
          </article>
          <article class="doc-card">
            <h3>docs/orchestration.md</h3>
            <p>Plan-Dateien, Cross-Repo-Aufgaben und Pflege des Master-Repos.</p>
          </article>
          <article class="doc-card">
            <h3>docs/website.md</h3>
            <p>URL, Quellpfad, Deploypfad und Grenzen der Webserver-Konfiguration.</p>
          </article>
          <article class="doc-card">
            <h3>skills/kimi-cli-cailama-ecosystem</h3>
            <p>Kimi-CLI-Skill fuer Website- und Online-Doku-Kontext ohne Secret- oder Live-Zugriff.</p>
          </article>
          <article class="doc-card">
            <h3>docs/ecosystem-reference.md</h3>
            <p>LLM-freundliche Gesamtreferenz fuer alle Repositories, Schnittstellen und Regeln.</p>
          </article>
          <article class="doc-card">
            <h3>docs/data/ecosystem.json</h3>
            <p>Maschinenlesbare Struktur der Repos, Endpunkte, Rollen und Roadmap.</p>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama-Betrieb</span>
      <a href="index.php">Start</a>
    </div>
  </footer>
</body>
</html>
