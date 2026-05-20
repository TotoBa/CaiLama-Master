<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Roadmap und naechste Integrationsschritte fuer das CaiLama-Oekosystem.">
  <title>CaiLama - Roadmap</title>
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
        <a aria-current="page" href="roadmap.php">Roadmap</a>
        <a href="operations.php">Betrieb</a>
        <a href="reference.php">Referenz</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="page-hero">
      <div class="page-hero-inner">
        <p class="eyebrow">Priorisierung</p>
        <h1>Erst haerten, dann personalisieren.</h1>
        <p class="page-lead">
          Die Roadmap folgt dem Statusplan: DB-Zugriff und Search-Vertrag
          sauber schneiden, CaiLama daran anbinden, personalisiertes Training
          schliessen und danach RAG, Jobs und Observability ausbauen.
        </p>
      </div>
    </section>

    <section>
      <div class="section-inner">
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
            <p>RAG-Analysepakete, einheitliche Job-Orchestrierung.</p>
          </article>
          <article class="step">
            <strong>Ausbau</strong>
            <p>Observability, optionale semantische Retrieval-Schicht.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Jetzt.</h2>
          <p>Die besten Quick Wins betreffen Sicherheits- und Integrationsdisziplin.</p>
        </div>
        <div class="grid-2">
          <article class="card">
            <span class="tag red">CaiLama</span>
            <h3>DB-Hybrid</h3>
            <ul class="rich-list">
              <li>Konfigurationsmodus fuer <code>native</code>, <code>api</code> und <code>hybrid</code> definieren.</li>
              <li>Lokale MariaDB/MySQL als Aufbau- und Backup-Pfad erhalten.</li>
              <li>Provider-Datenbank ueber fachliche Webspace-API anbinden.</li>
              <li>Keine generische SQL-over-HTTP-API einfuehren.</li>
            </ul>
          </article>
          <article class="card">
            <span class="tag">CaiLama</span>
            <h3>Search/DWZ/RAG-Standardpfad</h3>
            <ul class="rich-list">
              <li><code>/v1/search</code>, <code>/v1/context</code> und <code>/v1/dwz/*</code> als Standard nutzen.</li>
              <li><code>items</code>/<code>results</code> und <code>context</code>/<code>sources</code> normalisieren.</li>
              <li><code>internal_first</code>, <code>external_fallback</code>, <code>external_only</code>, <code>internal_only</code> pruefen.</li>
              <li>Browser-Websuche nur als bewussten Fallback nutzen.</li>
            </ul>
          </article>
          <article class="card">
            <span class="tag blue">Router</span>
            <h3>Folgehaertung</h3>
            <ul class="rich-list">
              <li>Streaming-Fehlerbehandlung fuer <code>stream: true</code> klaeren.</li>
              <li>Config-Hot-Reload bewerten und testen.</li>
              <li>Backend-spezifisches Modell-Mapping per Alias absichern.</li>
              <li>Bekannte <code>mypy</code>-Fehler bereinigen.</li>
            </ul>
          </article>
          <article class="card">
            <span class="tag copper">CaiLama-Search</span>
            <h3>Quellenpolitik</h3>
            <ul class="rich-list">
              <li>Crawler-Whitelists, Robots und Rate-Limits testen.</li>
              <li>Quellen-CRUD und Robots-Validierung absichern.</li>
              <li>Search-Goldsets und Observability vorbereiten.</li>
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
            <h3>PTG Phase 2 und Folgehaertung</h3>
            <ul class="rich-list">
              <li>Importierte Partien in Feature-Signale ueberfuehren.</li>
              <li>Schwaechenprofil und Kartenqueue nachvollziehbar ableiten.</li>
              <li>Review-Ergebnisse in Schwierigkeit, Prioritaet und Wiederholung zurueckfuehren.</li>
              <li>Datenschutz fuer Leistungsprofile klaeren.</li>
            </ul>
          </article>
          <article class="card">
            <span class="tag blue">CaiLama + Search</span>
            <h3>DWZ-Identity-Linking</h3>
            <ul class="rich-list">
              <li>Plattformprofile mit DWZ-Treffern verknuepfen.</li>
              <li>Mehrdeutige Treffer manuell bestaetigen lassen.</li>
              <li>Vereins-, Verbands- und Rating-Kontext fuer Training nutzen.</li>
              <li>PII-Minimierung und Export-/Retention-Regeln dokumentieren.</li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Spaeter und Ausbau.</h2>
          <p>Breitere Plattformfeatures kommen, wenn DB-Hybrid, SearchAdapter und PTG stabil sind.</p>
        </div>
        <div class="grid-3">
          <article class="card">
            <h3>RAG-Analysepakete</h3>
            <p>Researcher/Analyst-Rollen mit Search-Kontext fuer Dossiers, Gegnerprofile und evidenzbasierte Berichte.</p>
          </article>
          <article class="card">
            <h3>Unified Job Layer</h3>
            <p>Import, Crawl, Game-Analyse, PTG und Reindex als koordinierte Job-Landschaft.</p>
          </article>
          <article class="card">
            <h3>Observability</h3>
            <p>Privacy-safe KPIs fuer Router, Search und Training, ohne Prompt-/Response- oder Secret-Inhalte.</p>
          </article>
          <article class="card">
            <h3>Semantisches Retrieval</h3>
            <p>Embedding/Reranking nur mit Eval-Datensatz und Fallback ueber bestehendem Meili-Lexikalindex.</p>
          </article>
          <article class="card">
            <h3>Quellenpolitik</h3>
            <p>Whitelists, Trafilatura, Robots, Rate-Limits und rechtliche Grenzen stabil dokumentieren.</p>
          </article>
          <article class="card">
            <h3>Modelle benchmarken</h3>
            <p>Bestes Modell je Aufgabe statt Lieblingsmodell: router, small, large und task bewusst einsetzen.</p>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama-Roadmap</span>
      <a href="operations.php">Betrieb und Qualitaet</a>
    </div>
  </footer>
</body>
</html>
