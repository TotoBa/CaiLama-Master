<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Roadmap und nächste Integrationsschritte für das CaiLama-Ökosystem.">
  <title>CaiLama - Roadmap</title>
  <link rel="canonical" href="https://cailama.org/roadmap.php">
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
        <a href="status.php">Status</a>
        <a href="projects.php">Projekte</a>
        <a href="architecture.php">Architektur</a>
        <a aria-current="page" href="roadmap.php">Roadmap</a>
        <a href="operations.php">Betrieb</a>
        <a href="reference.php">Referenz</a>
        <a href="login.php">Login</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="page-hero">
      <div class="page-hero-inner">
        <p class="eyebrow">Priorisierung</p>
        <h1>Erst härten, dann personalisieren.</h1>
        <p class="page-lead">
          Die Roadmap folgt dem Statusplan: DB-Zugriff und Search-Vertrag
          sind im Grundschnitt angebunden, CaiLama wird daran weiter gehärtet,
          personalisiertes Training wird geschlossen und danach RAG, Jobs und
          Observability ausgebaut.
        </p>
      </div>
    </section>

    <section>
      <div class="section-inner">
        <div class="timeline">
          <article class="step">
            <strong>Jetzt</strong>
            <p>DB-Hybrid ist konfigurierbar, Single-DB-Webspace läuft, Search/DWZ/RAG ist Standardpfad und Router bekommt eine kleine Infrastrukturhärtung.</p>
          </article>
          <article class="step">
            <strong>Danach</strong>
            <p>PTG-Artefakte an DGT-/Review-Flows anschliessen, DWZ-Identity-Linking, Search- und PTG-Observability.</p>
          </article>
          <article class="step">
            <strong>Später</strong>
            <p>RAG-Analysepakete, einheitliche Job-Orchestrierung und spezialisierte Modelle erst nach Benchmark-Baseline.</p>
          </article>
          <article class="step">
            <strong>Ausbau</strong>
            <p>Benchmarks, Observability und semantische Retrieval-Evaluation.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Jetzt.</h2>
          <p>Die nächsten Schritte liegen bei DB-Hybrid, Provenienz und Search-Qualität.</p>
        </div>
        <div class="grid-2">
          <article class="card">
            <span class="tag red">CaiLama</span>
            <h3>DB-Hybrid</h3>
            <ul class="rich-list">
              <li><code>database.access_mode</code> für <code>native</code>, <code>api</code> und <code>hybrid</code> ist definiert.</li>
              <li>Lokale MariaDB/MySQL als Aufbau- und Backup-Pfad erhalten.</li>
              <li>Fachlicher DB-API-Statusclient nutzt geschütztes <code>POST /api/v1/status</code> ohne SQL-over-HTTP oder Secret-Ausgabe.</li>
              <li>Webspace-API verarbeitet serverseitig hochgeladene <code>.sql</code>/<code>.sql.gz</code>-Dumps über no-query/no-body-Import-Endpunkte.</li>
              <li>Fehlende Importdateien werden abgelehnt; erfolgreiche Importe löschen die Dump-Datei.</li>
              <li>Private Webspace-Konfig liegt außerhalb des Public-Webroots; Status, Append, Reset und Admin-Schema-Setup haben getrennte Keys.</li>
              <li>Provider-Schemas werden über geschützte PHP-Endpunkte im Webspace gesetzt, nicht über direkten lokalen Provider-DB-Zugriff.</li>
              <li>Single-Database-Mode ist live: Login-Tabellen und Fachdaten liegen gemeinsam in <code>databases.cailama</code>.</li>
              <li><code>POST /api/v1/status</code> meldet <code>databases.cailama: ok</code>; Schema-Setup läuft über die Webspace-API.</li>
              <li>Keine generische SQL-over-HTTP-API einführen.</li>
              <li>Fachliche Read-/Write-Endpunkte und Hybrid-Sync bleiben Folgearbeit.</li>
            </ul>
          </article>
          <article class="card">
            <span class="tag">CaiLama</span>
            <h3>Search/DWZ/RAG-Standardpfad</h3>
            <ul class="rich-list">
              <li><code>/v1/search</code>, <code>/v1/context</code> und <code>/v1/dwz/*</code> als Standard nutzen.</li>
              <li><code>items</code>/<code>results</code> und <code>context</code>/<code>sources</code> normalisieren.</li>
              <li><code>web_search</code> und <code>search_dwz</code> nutzen den <code>SearchAdapter</code>.</li>
              <li>Recherchefragen schlagen <code>search_rag</code> vor.</li>
            </ul>
          </article>
          <article class="card">
            <span class="tag blue">Router</span>
            <h3>Infrastrukturhärtung</h3>
            <ul class="rich-list">
              <li>Streaming-Fehlerbehandlung für <code>stream: true</code> ist getestet.</li>
              <li>Config-Hot-Reload ist optional verfügbar.</li>
              <li>Backend-spezifisches Modell-Mapping per Alias ist abgesichert.</li>
              <li><code>mypy src</code> ist bereinigt.</li>
              <li>Backend-API-Key-Weitergabe ist umgesetzt; offen bleiben Usage-Metriken und optionaler Usage-CLI-Befehl.</li>
            </ul>
          </article>
          <article class="card">
            <span class="tag copper">CaiLama-Search</span>
            <h3>Search-Ausbau</h3>
            <ul class="rich-list">
              <li>Crawler-Whitelists, Robots-Gruppen und Source-Policy sind getestet.</li>
              <li>Quellen-CRUD, Robots-Validierung und Reindex-Tracking sind abgesichert.</li>
              <li>Synthetische Goldsets für Suche, DWZ und RAG-Kontext sind vorbereitet.</li>
              <li>Goldset-Testindex-Seeding ist localhost-geschützt vorbereitet.</li>
              <li><code>goldsets smoke</code> automatisiert Test-Meili, synthetisches Seeding, API-Start ohne Scheduler und Goldset-Run.</li>
              <li>Optionale semantische Retrieval-Schicht ist vorhanden, default-off und wird gegen Goldsets evaluiert.</li>
              <li>API-Qualität, Job-Orchestrierung und Retrieval-Benchmarks gezielt nachziehen.</li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <section>
      <div class="section-inner">
        <div class="section-head">
          <h2>Danach.</h2>
          <p>Die Produktwirkung entsteht, wenn Datenbasis, Training und Feedback geschlossen werden.</p>
        </div>
        <div class="grid-2">
          <article class="card">
            <span class="tag copper">CaiLama</span>
            <h3>PTG Phase 2 und Folgehärtung</h3>
            <ul class="rich-list">
              <li>Importierte Partien in Feature-Signale überführen.</li>
              <li>Classify/analyze-Stufen live gegen den Router verifizieren.</li>
              <li>Kommentierte PGN, drei bis sieben Schlüsselstellungen, Trainingsfragen, Trainings-JSON und Qualitätsgates sind offline erzeugt und getestet.</li>
              <li>DGT-nahe Abholung der Artefakte und Review-Priorisierung als nächste Scheibe umsetzen.</li>
              <li>Schwächenprofil und Kartenqueue nachvollziehbar ableiten.</li>
              <li>Review-Ergebnisse in Schwierigkeit, Priorität und Wiederholung zurückführen.</li>
              <li>Datenschutz für Leistungsprofile klären.</li>
            </ul>
          </article>
          <article class="card">
            <span class="tag blue">CaiLama + Search</span>
            <h3>DWZ-Identity-Linking</h3>
            <ul class="rich-list">
              <li>Plattformprofile mit DWZ-Treffern verknüpfen.</li>
              <li>Mehrdeutige Treffer manuell bestätigen lassen.</li>
              <li>Vereins-, Verbands- und Rating-Kontext für Training nutzen.</li>
              <li>PII-Minimierung und Export-/Retention-Regeln dokumentieren.</li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Später und Ausbau.</h2>
          <p>Breitere Plattformfeatures kommen, wenn DB-Hybrid, SearchAdapter und PTG stabil sind.</p>
        </div>
        <div class="grid-3">
          <article class="card">
            <h3>RAG-Analysepakete</h3>
            <p>Researcher/Analyst-Rollen mit Search-Kontext für Dossiers, Gegnerprofile und evidenzbasierte Berichte.</p>
          </article>
          <article class="card">
            <h3>Unified Job Layer</h3>
            <p>Import, Crawl, Game-Analyse, PTG und Reindex als koordinierte Job-Landschaft.</p>
          </article>
          <article class="card">
            <h3>Observability</h3>
            <p>Privacy-safe KPIs für Router, Search und Training, ohne Prompt-/Response- oder Secret-Inhalte.</p>
          </article>
          <article class="card">
            <h3>Benchmarks</h3>
            <p>Master-geführte Benchmark-Ergebnisse für Router, Search und Training mit synthetischen oder anonymisierten Daten.</p>
          </article>
          <article class="card">
            <h3>Semantische Evaluation</h3>
            <p>Default-off Hybrid-Retrieval gegen Goldset-Baseline, Recall, Latenz und Fallback über bestehendem Meili-Lexikalindex messen.</p>
          </article>
          <article class="card">
            <h3>Quellenpolitik</h3>
            <p>Whitelists, Trafilatura, Robots, Rate-Limits und rechtliche Grenzen stabil dokumentieren.</p>
          </article>
          <article class="card">
            <h3>Modelle benchmarken</h3>
            <p>Bestes Modell je Aufgabe statt Lieblingsmodell: router, small, large und task bewusst einsetzen.</p>
          </article>
          <article class="card">
            <h3>Spezialisiertes Training</h3>
            <p>Modellanpassungen erst nach Datenfreigabe, Eval-Trennung und Benchmark-Baseline; Bereitstellung bleibt über den Router.</p>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama-Roadmap</span>
      <a href="operations.php">Betrieb und Qualität</a>
    </div>
  </footer>
</body>
</html>
