<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Architektur, Datenflüsse und Schnittstellen des CaiLama-Ökosystems.">
  <title>CaiLama - Architektur</title>
  <link rel="canonical" href="https://cailama.org/architecture.php">
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
        <a aria-current="page" href="architecture.php">Architektur</a>
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
        <p class="eyebrow">Systemkarte</p>
        <h1>Klare Grenzen, klare Flüsse.</h1>
        <p class="page-lead">
          CaiLama bleibt Produktkern. Router und Search bleiben getrennte
          Dienste. Der Master dokumentiert und prüft, koppelt aber keine
          Runtime-Komponenten.
        </p>
      </div>
    </section>

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
              <p>CLI, Agent, Profile, Training, PGN, Stockfish, DGT-nahe Workflows.</p>
            </div>
            <div class="arrow">→</div>
            <div class="flow-node">
              <strong>LLM-Router</strong>
              <p>OpenAI-kompatible API, Modell-Aliase, Backends, Fallbacks.</p>
            </div>
            <div class="arrow">↘</div>
            <div class="flow-node">
              <strong>Search</strong>
              <p>Suche, Kontext, DWZ, Quellen, Meilisearch-Indizes.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Verträge.</h2>
          <p>Die wichtigsten Schnittstellen sind stabil genug, um die nächsten Integrationen zu planen.</p>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Richtung</th>
                <th>Vertrag</th>
                <th>Wichtige Regeln</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>CaiLama → LLM-Router</td>
                <td><code>/v1/chat/completions</code>, <code>/v1/models</code>, <code>/health</code></td>
                <td>Rollen-Aliase, keine Provider-Secrets im Master, Router ohne Schachproduktlogik.</td>
              </tr>
              <tr>
                <td>CaiLama → Search</td>
                <td><code>POST /v1/search</code>, <code>POST /v1/context</code>, <code>/v1/dwz/search</code>, <code>/v1/dwz/player/{pkz}</code></td>
                <td>Normalisierte Items/Results und Context/Sources; Browser-Websuche nur als expliziter Fallback.</td>
              </tr>
              <tr>
                <td>CaiLama → Webspace-DB-API</td>
                <td><code>POST /api/v1/status</code>, <code>POST /api/v1/imports/cailama/append</code>, <code>POST /api/v1/imports/cailama/reset</code>, <code>POST /api/v1/admin/schema/*</code></td>
                <td>Status, Import und Schema-Setup nur mit Bearer-Key, ohne Query oder Body. Fehlende Dump-Datei wird abgelehnt, erfolgreiche Importe löschen die Datei.</td>
              </tr>
              <tr>
                <td>Master → alle</td>
                <td>Dokumentation, Roadmap, Checkskripte</td>
                <td>Keine Runtime-Kopplung, keine Submodules, keine Unter-Repo-Dateien im Master.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section>
      <div class="section-inner">
        <div class="section-head">
          <h2>Analyse- und Trainingskette.</h2>
          <p>Der aktuelle Zielpfad baut auf vorhandenen CaiLama-Modulen auf und lässt Router/Search anreichern statt dominieren.</p>
        </div>
        <div class="grid-4">
          <article class="step">
            <strong>1. Ingest</strong>
            <p>PGNs, Plattformpartien, Spielerprofile und Ratings sammeln.</p>
          </article>
          <article class="step">
            <strong>2. Analyse</strong>
            <p>Stockfish, Heuristiksignale, Fehlerklassen und Schwächenprofil.</p>
          </article>
          <article class="step">
            <strong>3. Training</strong>
            <p>Karten, Queue, Review und DGT-nahe Einheiten erzeugen.</p>
          </article>
          <article class="step">
            <strong>4. Feedback</strong>
            <p>Review-Resultate in Priorität, Schwierigkeit und Wiederholung zurückführen.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Architekturprinzipien.</h2>
        </div>
        <div class="grid-3">
          <article class="card">
            <h3>Keine Parallelstrukturen</h3>
            <p>Vorhandene Module werden wiederverwendet; neue Fachlogik entsteht zuerst importierbar und testbar.</p>
          </article>
          <article class="card">
            <h3>HTTP/API-Kopplung</h3>
            <p>Dienste werden über klare Endpunkte verbunden, nicht hart ineinander verwoben.</p>
          </article>
          <article class="card">
            <h3>Konfiguration getrennt</h3>
            <p>Code, Doku und Beispiele enthalten keine lokalen Credentials oder produktiven Secrets.</p>
          </article>
          <article class="card">
            <h3>Messbare Qualität</h3>
            <p>Produktnahe Analyse-, Search-, Router- und OCR/FEN-Pfade brauchen Benchmark-Artefakte statt nur subjektiver Einschätzung.</p>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama-Architektur</span>
      <a href="roadmap.php">Roadmap</a>
    </div>
  </footer>
</body>
</html>
