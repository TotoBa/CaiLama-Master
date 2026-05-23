<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Produktpositionierung und Differenzierung des CaiLama-Ökosystems.">
  <title>CaiLama - Trainingswerkstatt für Schach</title>
  <link rel="canonical" href="https://cailama.org/">
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
          <p class="eyebrow">Deine Analyse. Deine Daten. Dein Training.</p>
          <h1>Vom PGN zur Trainingsaufgabe.</h1>
          <p class="lead">
            CaiLama ist als Trainingswerkstatt für ambitionierte Spieler,
            Trainer und ernsthafte Selbstlerner positioniert. Der Wert liegt
            nicht in Social-Features, sondern in nachvollziehbarer Analyse,
            wiederholbarem Training und langfristigem Feedback.
          </p>
          <div class="hero-actions">
            <a class="button primary" href="#kernloop">Kernloop ansehen</a>
            <a class="button" href="projects.php">Projektstand</a>
            <a class="button" href="https://github.com/TotoBa/CaiLama">GitHub</a>
          </div>
        </div>
      </div>
    </section>

    <section id="kernloop">
      <div class="section-inner">
        <div class="section-head">
          <h2>Kernloop.</h2>
          <p>Der erste produktive Loop muss klein, testbar und wiederholbar sein.</p>
        </div>
        <div class="timeline">
          <article class="step">
            <strong>1. Import</strong>
            <p>PGN oder Plattformpartie aufnehmen und gültige Ausgangsdaten sichern.</p>
          </article>
          <article class="step">
            <strong>2. Analyse</strong>
            <p>Stockfish, Brettwahrheit und Heuristiksignale statt ungeprüfter Modellbehauptungen.</p>
          </article>
          <article class="step">
            <strong>3. Aufgaben</strong>
            <p>Schlüsselstellungen, Trainingsfragen, Karten, kommentierte PGN und Trainings-JSON erzeugen.</p>
          </article>
          <article class="step">
            <strong>4. Wiederholung</strong>
            <p>Training per CLI, Agent oder DGT-nahem Brettmodus abrufen und Reviews zurückführen.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="band">
      <div class="section-inner">
        <div class="section-head">
          <h2>Differenzierung.</h2>
        </div>
        <div class="grid-3">
          <article class="card">
            <h3>Trainingssystem</h3>
            <p>CaiLama organisiert Analyse, Aufgaben, Wiederholung und Feedback statt nur eine Partie zu erklären.</p>
          </article>
          <article class="card">
            <h3>Lokal und modular</h3>
            <p>Hauptsystem, Router, Search, DB-API und Runtime-Kopien bleiben getrennt und kontrollierbar.</p>
          </article>
          <article class="card">
            <h3>DGT-nah</h3>
            <p>Trainingsstellungen sollen nicht nur gelesen, sondern auch am Brett wiederholt werden können.</p>
          </article>
          <article class="card">
            <h3>Quellenklarheit</h3>
            <p>RAG-Kontext braucht sichtbare Provenienz und darf die Kernanalyse nicht ersetzen.</p>
          </article>
          <article class="card">
            <h3>Validierung</h3>
            <p>Legale Züge, PGN-Roundtrip, annotierte PGN und Grounding-Status werden als Qualitätsgates sichtbar.</p>
          </article>
          <article class="card">
            <h3>Benchmarks</h3>
            <p>Qualität wird über Master-geführte Benchmarks sichtbar: Search, Router, PTG, OCR und später Modelle.</p>
          </article>
        </div>
      </div>
    </section>

    <section>
      <div class="section-inner split">
        <div class="section-head">
          <h2>Späterer Modellhebel.</h2>
          <p>
            Spezialisiertes LLM-Training ist ein späterer Ausbaupfad. Vorher
            müssen Benchmark-Daten, Datenschutz, Lizenzlage und Eval-Metriken
            stehen. Modelle werden über den Router eingebunden und müssen
            gegen dieselben Benchmarks antreten wie generische Backends.
          </p>
        </div>
        <pre><code>PGN -> Analyse -> Schlüsselstellung -> Karte -> Review -> bessere Priorisierung</code></pre>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama</span>
      <a href="roadmap.php">Roadmap</a>
      <a href="mailto:info@cailama.org">Kontakt</a>
    </div>
  </footer>
</body>
</html>
