# Benchmark-Ergebnisse

Dieser Ordner ist fuer spaetere, Master-geführte Benchmark-Artefakte
reserviert. Die Messlogik bleibt in den jeweiligen Ziel-Repositories; der
Master sammelt Ergebniszusammenfassungen, Vergleichbarkeit und offene
Folgepunkte.

Dateinamen:

```text
YYYY-MM-DD.<thema>.md
```

Jedes Ergebnis muss ohne Secrets, lokale Pfade, private Rohdaten, volle
Prompt-/Response-Logs oder ungekuerzte private Partiearchive auskommen.
Zulaessig sind synthetische, oeffentliche, explizit freigegebene oder
anonymisierte Daten.

Aktueller Bestand:

- `model-role-matrix.current.md` - aktuelle Arbeitshypothese fuer
  Coding-Agenten und Schachrollen, mit Pflichtmetriken fuer Laufzeit,
  Tokenwerte, Qualitaet, Aufgabenloesung, Logikfehler und A/B-Feedback.
- `2026-05-23.search-lexical-hybrid.md` - synthetischer CaiLama-Search-
  Goldset-Vergleich lexical gegen hybrid.
- `2026-05-23.ptg-offline-baseline.md` - CaiLama-PTG-Offline-Baseline mit
  freigegebenen PGN-Auszügen, Quality-Gates und offenen LLM-Resilienzpunkten.

Menschliches Feedback zu Modellrollen wird nicht als Rohprompt-Archiv
gespeichert, sondern ueber die geschuetzte Website-Seite
`/benchmark-feedback.php` in der Provider-Datenbank gesammelt. Relevante
Tabellen: `cailama_model_benchmark_cases` und `cailama_model_feedback`.
