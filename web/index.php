<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="CaiLama ist ein modulares Schachtrainingssystem mit Analyse, Training, LLM-Router, Search/RAG und DGT-nahen Workflows.">
  <meta property="og:title" content="CaiLama">
  <meta property="og:description" content="Modulares Schachtraining mit Analyse, LLM, Search/RAG und personalisierten Trainingspfaden.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://cailama.org/">
  <meta property="og:image" content="https://raw.githubusercontent.com/TotoBa/CaiLama/main/img/logo-big.png">
  <title>CaiLama - Schachtraining als System</title>
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
        <a href="reference.php">Referenz</a>
        <a href="login.php">Login</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="hero">
      <div class="hero-inner hero-grid">
        <div>
          <p class="eyebrow">Schachtraining, Analyse und lokale Modellinfrastruktur</p>
          <h1>CaiLama</h1>
          <p class="lead">
            CaiLama ist ein praxistaugliches Schachanalyse-, Trainings- und
            Automatisierungs-Oekosystem. Es verbindet PGNs, Stockfish,
            LLM-Kommentierung, DGT-Board-Training, Wissenssuche,
            Spielerprofile und langfristige Trainingsentwicklung.
          </p>
          <div class="hero-actions">
            <a class="button primary" href="projects.php">Projektstand ansehen</a>
            <a class="button" href="architecture.php">Architektur verstehen</a>
            <a class="button" href="https://github.com/TotoBa/CaiLama">GitHub</a>
          </div>
        </div>
        <img class="hero-logo" src="https://raw.githubusercontent.com/TotoBa/CaiLama/main/img/logo-big.png" alt="CaiLama Logo">
      </div>
    </section>

    <section>
      <div class="section-inner">
        <div class="section-head">
          <h2>Aktueller Stand.</h2>
          <p>
            Das Oekosystem besteht aus einem reifen Kernsystem und zwei
            spezialisierten Diensten. Der Master haelt Roadmap, Webseite,
            Status und Cross-Repo-Regeln zusammen.
          </p>
        </div>
        <div class="grid-4">
          <article class="metric">
            <strong>4</strong>
            <p>Repos mit klarer Verantwortlichkeit.</p>
          </article>
          <article class="metric">
            <strong>3</strong>
            <p>aktive Integrationslinien: Training, Router, Search.</p>
          </article>
          <article class="metric">
            <strong>200</strong>
            <p>cailama.org ist erreichbar und liefert die statische Site aus.</p>
          </article>
          <article class="metric">
            <strong>0</strong>
            <p>Unter-Repo-Dateien im Master-Index.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Was CaiLama leisten soll.</h2>
          <p>
            Das System soll nicht nur Fragen beantworten, sondern aktiv
            Analyse- und Trainingsaufgaben ausfuehren.
          </p>
        </div>
        <div class="grid-3">
          <article class="card">
            <span class="tag">Analyse</span>
            <h3>PGN und Stockfish</h3>
            <p>
              Partien importieren, Hauptvarianten verarbeiten,
              Stockfish-Analysen erhalten, menschliche Kommentare ergaenzen
              und gueltige PGN-Artefakte erzeugen.
            </p>
          </article>
          <article class="card">
            <span class="tag copper">Training</span>
            <h3>Personalisierte Aufgaben</h3>
            <p>
              Schluesselstellungen erkennen, Fehlerarten klassifizieren,
              wiederkehrende Muster ableiten und daraus konkrete
              Trainingskarten sowie DGT-nahe Einheiten erzeugen.
            </p>
          </article>
          <article class="card">
            <span class="tag blue">Recherche</span>
            <h3>Search und RAG</h3>
            <p>
              Schachspezifische Quellen, DWZ-Daten, eigene Analysen und
              Trainingsmaterialien als kontrollierten Kontext fuer Rollen wie
              Researcher und Analyst bereitstellen.
            </p>
          </article>
        </div>
      </div>
    </section>

    <section>
      <div class="section-inner">
        <div class="section-head">
          <h2>Repo-Uebersicht.</h2>
          <p>
            Kein Monorepo: Jedes Repository hat einen eigenen Zweck. Die
            Kopplung laeuft ueber dokumentierte Schnittstellen.
          </p>
        </div>
        <div class="grid-2">
          <article class="repo-card">
            <div>
              <span class="tag">Hauptsystem</span>
              <h3>CaiLama</h3>
              <p>Schachanalyse, Training, Profile, Agent-CLI, PGN, Stockfish, DGT-nahe Workflows.</p>
              <ul>
                <li>DB-Hybridpfad: native DB, Webspace-API und Backup-Betrieb.</li>
                <li>PTG Phase 2, SearchAdapter, DWZ und RAG als Standardpfade.</li>
                <li>LLM-Rollen ueber Router-Aliase.</li>
              </ul>
            </div>
            <a href="projects.php#cailama">Details</a>
          </article>
          <article class="repo-card">
            <div>
              <span class="tag copper">Router</span>
              <h3>CaiLama-LLM-Router</h3>
              <p>OpenAI-kompatible Modellzugriffe, Backends, Aliase, Fallbacks und JSONL-Betriebsdaten.</p>
              <ul>
                <li>Streaming-Fehlerbehandlung und Config-Hot-Reload klaeren.</li>
                <li>Backend-spezifische Modellnamen je Alias testen.</li>
                <li>Keine Schachproduktlogik im Router.</li>
              </ul>
            </div>
            <a href="projects.php#router">Details</a>
          </article>
          <article class="repo-card">
            <div>
              <span class="tag red">Search</span>
              <h3>CaiLama-Search</h3>
              <p>FastAPI, Meilisearch, DWZ, Crawler, Quellen, Kontext-API und spaeter semantisches Retrieval.</p>
              <ul>
                <li>Explizite Meili-Keys, Admin-Token und lokale Docker-Bindings.</li>
                <li>Vertrag fuer <code>/v1/search</code>, <code>/v1/context</code>, <code>/v1/dwz/*</code>.</li>
                <li>Quellenprovenienz fuer RAG-Antworten.</li>
              </ul>
            </div>
            <a href="projects.php#search">Details</a>
          </article>
          <article class="repo-card">
            <div>
              <span class="tag moss">Koordination</span>
              <h3>CaiLama-Master</h3>
              <p>Gesamt-Doku, Status, Roadmap, Regeln, Checkskripte, LLM-Referenz und Cross-Repo-Dokumentation.</p>
              <ul>
                <li>Unter-Repos bleiben ignoriert.</li>
                <li>Keine Runtime-Logik, keine Secrets.</li>
                <li>Diese Website ist die Human-Version der Gesamt-Dokumentation.</li>
                <li><code>llms.txt</code>, Markdown und JSON bilden die LLM-freundliche Referenz.</li>
              </ul>
            </div>
            <a href="operations.php#master">Details</a>
          </article>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Naechste Plattformstufe.</h2>
          <p>
            Der Fokus liegt auf Integrationsdisziplin: DB-Zugriff sauber
            schneiden, Search als Standardpfad nutzen, Training personalisieren
            und erst danach breiter ausbauen.
          </p>
        </div>
        <div class="timeline">
          <article class="step">
            <strong>Jetzt</strong>
            <p>DB-Hybrid, Search/DWZ/RAG-Standardpfad, Router-Streaming, Search-Quellenpolitik.</p>
          </article>
          <article class="step">
            <strong>Danach</strong>
            <p>PTG Phase 2, DWZ-Identity-Linking, Search- und PTG-Observability.</p>
          </article>
          <article class="step">
            <strong>Spaeter</strong>
            <p>RAG-Analysepakete und einheitliche Job-Orchestrierung.</p>
          </article>
          <article class="step">
            <strong>Ausbau</strong>
            <p>Privacy-safe Observability und optionale semantische Suche.</p>
          </article>
        </div>
      </div>
    </section>

    <section>
      <div class="section-inner">
        <div class="section-head">
          <h2>Human und LLM-freundlich.</h2>
          <p>
            Die Master-Doku dokumentiert das gesamte Oekosystem. Menschen lesen
            die HTML-Seiten; Agenten und LLMs nutzen <code>llms.txt</code>,
            <code>ecosystem-reference.md</code> und <code>data/ecosystem.json</code>.
          </p>
        </div>
        <div class="grid-3">
          <article class="doc-card">
            <h3>Human-Version</h3>
            <p>Projektseiten, Architektur, Roadmap und Betrieb als einheitliche Website.</p>
            <p><a href="reference.php">Referenz oeffnen</a></p>
          </article>
          <article class="doc-card">
            <h3>LLM-Markdown</h3>
            <p>Kompakte Gesamtreferenz fuer Codex, Kimi und andere Agenten.</p>
            <p><a href="ecosystem-reference.md">ecosystem-reference.md</a></p>
          </article>
          <article class="doc-card">
            <h3>Maschinenlesbar</h3>
            <p>Strukturierte JSON-Quelle fuer Tools, Validierung und Automatisierung.</p>
            <p><a href="data/ecosystem.json">data/ecosystem.json</a></p>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama - Schachtraining als System</span>
      <a href="https://github.com/TotoBa/CaiLama-Master">Master-Repository</a>
    </div>
  </footer>
</body>
</html>
