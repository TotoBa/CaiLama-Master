# Benchmarks

Benchmarks fuer das CaiLama-Oekosystem werden im Master geplant und
dokumentiert. Die Umsetzung einzelner Messpunkte findet in den jeweiligen
Repos statt, aber Ergebnisformat, Vergleichbarkeit und Freigaberegeln gehoeren
in diese Koordination.

## Grundregeln

- Keine Secrets, Tokens, privaten Pfade oder produktiven Zugangsdaten.
- Keine ungekuerzten privaten Partiearchive oder privaten Kommentare in
  Benchmark-Artefakten.
- Bevorzugt synthetische, oeffentliche, freigegebene oder anonymisierte Daten.
- Testdaten, Evaldaten und spaetere Trainingsdaten bleiben getrennt.
- Jeder Benchmark beschreibt Kommando, Umgebung, Dataset, Metriken und
  Akzeptanzkriterium.
- Ergebnisse werden im Master dokumentiert, damit Router-, Search- und
  CaiLama-Aenderungen vergleichbar bleiben.

## Benchmark-Familien

### Search und RAG

- Recall@5 und Recall@10.
- MRR.
- Zero-Hit-Rate.
- Quellen-Diversitaet.
- Antwortlatenz.
- Speicherbedarf.
- Vergleich `lexical` gegen optionales `hybrid`.

### Router

- Request-Latenz nach Alias und Backend.
- Fehler- und Fallback-Rate.
- Cooldown-Verhalten.
- Streaming-Fehlervertrag.
- Token-/Usage-Werte aus den privacy-safe Router-Metriken.
- Master-kompatibler Export per Router-CLI.

### Analyse und PTG

- PGN-Validitaet nach Annotation.
- Anteil legal validierter Zuege und Stellungen.
- Zahl und Qualitaet extrahierter Schluesselstellungen.
- Anteil gueltiger PTG-Sessions aus `quality_gates.json`.
- Grounding-Zaehler fuer Board, Engine, Klassifikation und Analyse.
- Redundanz von Trainingskarten.
- Fehler-/Mustertaxonomie-Abdeckung.
- Review-Erfolg und Wiederholungswirkung.

### OCR und FEN

- Text-OCR-Qualitaet fuer private Buchimporte.
- Diagramm-Erkennungsrate.
- FEN-Ausgabe nur bei hoher Sicherheit.
- Falsch-positive FENs als harte Fehlerklasse.

## Ergebnisformat

Neue Benchmark-Ergebnisse sollen als klare Master-Artefakte abgelegt werden,
zum Beispiel:

```text
docs/benchmark-results/YYYY-MM-DD.<thema>.md
```

Ein Ergebnis enthaelt mindestens:

- Ziel-Repo.
- Git-Ref oder Versionsstand ohne lokale Pfade.
- Dataset-Name und Datenfreigabe.
- Kommando oder reproduzierbarer Ablauf.
- Metriken.
- Ergebnis.
- Bewertung.
- offene Folgepunkte.

## Vorliegende Ergebnisse

- `docs/benchmark-results/2026-05-23.search-lexical-hybrid.md`:
  CaiLama-Search-Goldsets, lexical gegen hybrid. Ergebnis: Recall@5 und
  Recall@10 sind in beiden Modi 1.0; Hybrid verbessert MRR von 0.9167 auf
  1.0000, senkt aber die Pass-Rate von 88.89% auf 77.78% durch einen
  filter+hybrid-Fehler. `semantic.enabled=false` bleibt empfohlen.

## Spezialisiertes LLM-Training

Spezialisiertes LLM-Training wird erst nach belastbaren Benchmarks sinnvoll.
Vor einer Modellanpassung muessen Trainingsdaten, Evaldaten, Datenschutz,
Lizenzlage und Zielmetriken geklaert sein. Ein spezialisiertes Modell darf nur
ueber den Router eingebunden werden und muss gegen die gleiche Benchmark-
Familie antreten wie generische Modelle.
