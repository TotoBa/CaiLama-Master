{extends file="layouts/base.tpl"}

{block name="content"}
  {include file="partials/hero.tpl" hero=$page.hero hero_class="hero"}

  <section id="kernloop">
    <div class="section-inner">
      <div class="section-head">
        <h2>Aus einem Projekt wird ein nutzbarer Trainingsloop.</h2>
        <p>
          CaiLama hat inzwischen eine offline prüfbare Produktloop-Scheibe:
          Partien werden importiert, analysiert, als gültige PGN weiterverarbeitet
          und in Trainingsartefakte überführt.
        </p>
      </div>
      {include file="partials/timeline.tpl" items=$page.timeline}
    </div>
  </section>

  <section class="band">
    <div class="section-inner">
      <div class="section-head">
        <h2>Was CaiLama gerade schon kann.</h2>
      </div>
      {include file="partials/card-grid.tpl" cards=$page.capabilities grid_class="grid-3"}
    </div>
  </section>

  <section>
    <div class="section-inner split">
      <div class="section-head">
        <h2>Nicht „KI erklärt eine Partie“, sondern Trainingsinfrastruktur.</h2>
        <p>
          Viele Werkzeuge liefern eine Analyse oder einen Kommentar. CaiLama zielt
          auf einen geschlossenen Trainingskreislauf: Partie rein, kritische
          Momente erkennen, Aufgaben erzeugen, am Brett oder in der CLI wiederholen,
          Ergebnis speichern, nächste Aufgabe besser auswählen.
        </p>
      </div>
      <pre><code>PGN → Analyse → Schlüsselstellung → Karte → Review → bessere Priorisierung</code></pre>
    </div>
  </section>

  <section class="band">
    <div class="section-inner">
      <div class="section-head">
        <h2>Aktuell: starkes Fundament, noch kein fertiges Produkt.</h2>
        <p>
          Der Kern ist weit genug, um den Produktloop realistisch zu sehen.
          Gleichzeitig bleiben wichtige Punkte bewusst offen: Live-Verifikation
          mit Router, Datenschutz für Leistungsprofile, einheitliche
          Job-Orchestrierung, durchgängige Provenienz in RAG-Antworten,
          OCR/FEN-Gates und Master-Benchmarks mit echten Artefakten.
        </p>
      </div>
    </div>
  </section>
{/block}
