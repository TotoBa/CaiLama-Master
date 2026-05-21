<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Detailuebersicht der CaiLama-Repositories und ihrer aktuellen Aufgaben.">
  <title>CaiLama - Projekte</title>
  <link rel="canonical" href="https://cailama.org/projects.php">
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
        <a aria-current="page" href="projects.php">Projekte</a>
        <a href="architecture.php">Architektur</a>
        <a href="roadmap.php">Roadmap</a>
        <a href="operations.php">Betrieb</a>
        <a href="reference.php">Referenz</a>
        <a href="login.php">Login</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="page-hero">
      <div class="page-hero-inner">
        <p class="eyebrow">Repository-Stand</p>
        <h1>Vier Repos, ein System.</h1>
        <p class="page-lead">
          CaiLama ist bewusst auf getrennte Verantwortlichkeiten geschnitten:
          Produktkern, LLM-Infrastruktur, Search/RAG-Dienst und Master-Doku.
        </p>
      </div>
    </section>

    <section id="cailama">
      <div class="section-inner split">
        <div class="section-head">
          <h2>CaiLama</h2>
          <p>
            Das Hauptsystem ist der reifste Teil des Oekosystems. Es verbindet
            PGN-I/O, statische Brettwahrheit, Stockfish-Pipeline,
            Spielerprofile, Plattformimporte, Training, Agent-CLI und
            DGT-nahe Workflows.
          </p>
          <div class="button-row">
            <a class="button light" href="https://github.com/TotoBa/CaiLama">Repository</a>
          </div>
        </div>
        <div class="grid-2">
          <article class="card">
            <span class="tag">Vorhanden</span>
            <h3>Modulare Basis</h3>
            <p>Analyse, chess_eval, database, player_profile, knowledge, queue, training, agent und hardware sind als Bausteine vorhanden.</p>
          </article>
          <article class="card">
            <span class="tag copper">Laufend</span>
            <h3>PTG und DB-Hybrid</h3>
            <p>PTG Phase 2 verbindet Queue und optional classify/analyze vor der Kartengenerierung; DB-Zugriff soll zwischen nativ, API und hybrid waehlbar werden.</p>
          </article>
          <article class="card">
            <span class="tag blue">Integration</span>
            <h3>SearchAdapter</h3>
            <p>Interne Suche, Kontext, RAG und DWZ laufen zuerst ueber CaiLama-Search; Browser-Websuche bleibt Fallback.</p>
          </article>
          <article class="card">
            <span class="tag red">Datenschutz</span>
            <h3>Leistungsprofile</h3>
            <p>Personalisierung braucht Retention, Export, Ambiguitaetsbehandlung und PII-Minimierung.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="router" class="band">
      <div class="section-inner split">
        <div class="section-head">
          <h2>CaiLama-LLM-Router</h2>
          <p>
            Der Router kapselt lokale und entfernte Modellbackends hinter
            OpenAI-kompatiblen Endpunkten. Er entscheidet nicht ueber
            Schachfachlogik, sondern ueber Modellzugriff, Aliase, Fallbacks
            und Betriebsverhalten.
          </p>
          <div class="button-row">
            <a class="button light" href="https://github.com/TotoBa/CaiLama-LLM-Router">Repository</a>
          </div>
        </div>
        <div class="grid-2">
          <article class="card">
            <span class="tag copper">API</span>
            <h3>OpenAI-kompatibel</h3>
            <p><code>/v1/chat/completions</code>, <code>/v1/models</code> und <code>/health</code> bilden die stabile Konsumoberflaeche.</p>
          </article>
          <article class="card">
            <span class="tag">Aliase</span>
            <h3>Rollenmodelle</h3>
            <p>Aliasgruppen wie <code>chess-small</code>, <code>chess-large</code>, <code>chess-coach</code>, <code>chess-analyst</code> und <code>chess-researcher</code> bedienen CaiLama.</p>
          </article>
          <article class="card">
            <span class="tag red">Fallback</span>
            <h3>Folgehaertung</h3>
            <p>Streaming-Fehler, Config-Hot-Reload, backend-spezifische Modellnamen und <code>mypy src</code> sind umgesetzt und getestet; neue Router-Arbeit startet nur mit neuem Auftrag.</p>
          </article>
          <article class="card">
            <span class="tag blue">Logging</span>
            <h3>Privacy-safe</h3>
            <p>JSONL-Diagnostik soll Latenzen und Backend-Zustaende erfassen, aber keine Prompt-/Response-Inhalte loggen.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="search">
      <div class="section-inner split">
        <div class="section-head">
          <h2>CaiLama-Search</h2>
          <p>
            CaiLama-Search ist der Such-, DWZ- und RAG-Dienst. Er stellt eine
            kontrollierte Alternative zu allgemeinem Webscraping bereit und
            wird zum Wissenskontext fuer Analyse- und Research-Rollen.
          </p>
          <div class="button-row">
            <a class="button light" href="https://github.com/TotoBa/CaiLama-Search">Repository</a>
          </div>
        </div>
        <div class="grid-2">
          <article class="card">
            <span class="tag red">Search</span>
            <h3>Key-Defaults</h3>
            <p>Docker verlangt explizite Master- und API-Keys, bindet lokal und erlaubt Admin-Token per Environment.</p>
          </article>
          <article class="card">
            <span class="tag blue">Kontext</span>
            <h3>RAG-Vertrag</h3>
            <p><code>/v1/search</code> liefert normalisierte Items; <code>/v1/context</code> liefert Kontextbloecke plus kompatible Quellen.</p>
          </article>
          <article class="card">
            <span class="tag">DWZ</span>
            <h3>Download-basierter Import</h3>
            <p>Der DWZ-Pfad setzt auf oeffentliche Downloads und Cache-Strategie, nicht auf deaktivierte tokenisierte Schnittstellen.</p>
          </article>
          <article class="card">
            <span class="tag copper">Ausbau</span>
            <h3>Observability</h3>
            <p>Crawler-Whitelists, Robots-Gruppen, Source-Validierung und Reindex-Tracking sind getestet; privacy-safe Suchmetriken und synthetische Goldsets sind vorbereitet.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="master" class="band">
      <div class="section-inner split">
        <div class="section-head">
          <h2>CaiLama-Master</h2>
          <p>
            Der Master ist Koordination, Webseite und Status. Er bleibt leicht,
            ignoriert die Unter-Repos und enthaelt keine Runtime-Logik.
          </p>
          <div class="button-row">
            <a class="button light" href="https://github.com/TotoBa/CaiLama-Master">Repository</a>
          </div>
        </div>
        <div class="grid-2">
          <article class="card">
            <span class="tag moss">Webseite</span>
            <h3>cailama.org</h3>
            <p>Die Website ist die lesbare Master-Doku: Projektstand, Architektur, Roadmap und Betrieb.</p>
          </article>
          <article class="card">
            <span class="tag">Pruefung</span>
            <h3>Checkskript</h3>
            <p><code>scripts/check-ecosystem.sh</code> prueft Unter-Repos, Ignore-Regeln, Pflichtdateien und Web-Deployment.</p>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama-Projekte</span>
      <a href="index.php">Start</a>
    </div>
  </footer>
</body>
</html>
