<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Human- und LLM-freundliche Gesamt-Dokumentation des CaiLama-Ökosystems.">
  <title>CaiLama - Referenz</title>
  <link rel="canonical" href="https://cailama.org/reference.php">
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
        <a href="operations.php">Betrieb</a>
        <a aria-current="page" href="reference.php">Referenz</a>
        <a href="login.php">Login</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="page-hero">
      <div class="page-hero-inner">
        <p class="eyebrow">Human und LLM</p>
        <h1>Gesamt-Doku des Ökosystems.</h1>
        <p class="page-lead">
          Die Master-Doku ist die gemeinsame Referenz für Menschen und
          Agenten. Die HTML-Seiten sind lesbar gestaltet; Markdown und JSON
          sind als Nachschlagewerk für LLMs und Automatisierung gedacht.
        </p>
      </div>
    </section>

    <section>
      <div class="section-inner">
        <div class="section-head">
          <h2>Human-Version.</h2>
          <p>Diese Seiten bilden die strukturierte Web-Dokumentation für das gesamte CaiLama-Ökosystem.</p>
        </div>
        <div class="grid-3">
          <article class="doc-card">
            <h3>Projekte</h3>
            <p>Detailstand von CaiLama, Router, Search und Master.</p>
            <p><a href="projects.php">projects.php</a></p>
          </article>
          <article class="doc-card">
            <h3>Architektur</h3>
            <p>Schnittstellen, Flüsse, Analyse- und Trainingskette.</p>
            <p><a href="architecture.php">architecture.php</a></p>
          </article>
          <article class="doc-card">
            <h3>Roadmap</h3>
            <p>Priorisierte Umsetzung aus dem aktuellen Statusplan.</p>
            <p><a href="roadmap.php">roadmap.php</a></p>
          </article>
          <article class="doc-card">
            <h3>Betrieb</h3>
            <p>Webspace, Checks, Master-Regeln und Qualität.</p>
            <p><a href="operations.php">operations.php</a></p>
          </article>
          <article class="doc-card">
            <h3>GitHub</h3>
            <p>Quell-Repositories und versionierte Projektartefakte.</p>
            <p><a href="https://github.com/TotoBa/CaiLama-Master">CaiLama-Master</a></p>
          </article>
          <article class="doc-card">
            <h3>Website</h3>
            <p>Diese statische Site ist unter cailama.org erreichbar.</p>
            <p><a href="index.php">Startseite</a></p>
          </article>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>LLM-freundliche Version.</h2>
          <p>Diese Dateien sind knapp, stabil verlinkbar und maschinenlesbar.</p>
        </div>
        <div class="grid-3">
          <article class="doc-card">
            <span class="tag blue">Text</span>
            <h3>llms.txt</h3>
            <p>Minimaler Einstiegspunkt für LLM-Crawler und Agenten.</p>
            <p><a href="llms.txt">llms.txt</a></p>
          </article>
          <article class="doc-card">
            <span class="tag">Markdown</span>
            <h3>Ecosystem Reference</h3>
            <p>LLM-freundliche Gesamtreferenz mit Repos, Rollen, Schnittstellen, Roadmap und Regeln.</p>
            <p><a href="ecosystem-reference.md">ecosystem-reference.md</a></p>
          </article>
          <article class="doc-card">
            <span class="tag copper">JSON</span>
            <h3>Machine Data</h3>
            <p>Strukturierte Maschinenreferenz für Automatisierung, Agenten und Validierung.</p>
            <p><a href="data/ecosystem.json">data/ecosystem.json</a></p>
          </article>
        </div>
      </div>
    </section>

    <section>
      <div class="section-inner split">
        <div class="section-head">
          <h2>Agenten-Kontext.</h2>
          <p>
            Ein LLM soll zuerst <code>llms.txt</code>, dann <code>ecosystem-reference.md</code> und
            bei strukturierter Verarbeitung <code>data/ecosystem.json</code> lesen. Für
            konkrete Codearbeit gilt danach das jeweilige <code>AGENTS.md</code> im
            Ziel-Repository. Für Kimi-CLI gibt es im Master zusätzlich den
            Skill <code>skills/kimi-cli-cailama-ecosystem/SKILL.md</code>.
          </p>
        </div>
        <pre><code>https://cailama.org/llms.txt
https://cailama.org/ecosystem-reference.md
https://cailama.org/data/ecosystem.json</code></pre>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama-Referenz</span>
      <a href="index.php">Start</a>
    </div>
  </footer>
</body>
</html>
