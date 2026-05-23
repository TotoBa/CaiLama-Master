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
- Coding-Agenten separat von Schachrollen bewerten: Regelbefolgung, Patch-
  Qualitaet, Testdisziplin, Doku-Sync, Korrekturrate und Arbeitsbaum-Hygiene.

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

### Modellrollen und Human Feedback

Die Arbeitshypothese aus der Modellbewertung wird als wiederholbarer
Benchmark behandelt, nicht als Bauchgefuehl. Gemma4 ist nur fuer Kimi-/Coding-
Agentenarbeit verworfen; fuer Schachrollen wie `chess-small`, `chess-coach`
oder `chess-vision` bleibt es ein messbarer Kandidat. Coding-Arbeit nutzt
lokal weiter `kimi-k2.6:cloud` als Kimi-Default.

Erfasst werden mindestens:

- Laufzeit pro Antwort oder Teilaufgabe.
- Input-, Thinking- und Output-Tokens, soweit vom Router oder Client geliefert.
- Modellrolle und konkretes Modell.
- Qualitaetsbewertung 1 bis 5.
- Bewertung, ob die konkrete Teilaufgabe geloest wurde, 1 bis 5.
- Logikfehler-Klasse: keine, klein, schwer oder unklar.
- A/B-Praeferenz: Option A, Option B, gleich gut oder nicht anwendbar.
- Freitext fuer Fehler, Nutzen und daraus folgende Prompt-/Regel-
  Verbesserung.

Die Website stellt dafuer eine geschuetzte Feedback-Seite bereit:

```text
https://cailama.org/benchmark-feedback.php
```

Sie liegt hinter Login, ist `noindex` und speichert Bewertungen in der
Provider-Datenbank. Die Tabellen heissen `cailama_model_benchmark_cases` und
`cailama_model_feedback`. Rohprompts, volle Modellantworten, private Partien
und Secrets gehoeren nicht in diese Tabellen; dort liegen nur vergleichbare
Kennzahlen, kurze Aufgabenbeschreibungen und menschliches Feedback.

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

- `docs/benchmark-results/model-role-matrix.current.md`:
  aktuelle Arbeitshypothese fuer Coding-Agenten und Schachrollen, inklusive
  Messdimensionen fuer Laufzeit, Thinking-/Output-Tokens, Qualitaet,
  Aufgabenloesung, Logikfehler und A/B-Feedback.
- `docs/benchmark-results/2026-05-23.search-lexical-hybrid.md`:
  CaiLama-Search-Goldsets, lexical gegen hybrid. Ergebnis: Recall@5 und
  Recall@10 sind in beiden Modi 1.0; MRR ist in beiden Modi 0.9167; die
  Pass-Rate ist nach Filter- und Multi-Index-Fixes in beiden Modi 100%.
  `semantic.enabled=false` bleibt empfohlen, bis ein groesseres Eval-Set
  produktiven Nutzen belegt.
- `docs/benchmark-results/2026-05-23.ptg-offline-baseline.md`:
  CaiLama-PTG-Offline-Baseline mit 3 freigegebenen Spielen, 21
  Schluesselstellungen, 13 Trainingskarten und 3/3 gueltigen PTG-Sessions.
  Die 21 Schluesselstellungen sind ein Ergebnis dieser Drei-Spiele-Baseline,
  keine allgemeine Obergrenze.
  Der Live-LLM-Pfad ist noch nicht produktionsreif, weil der bisherige
  pro-Zug-Loop 464 Router-Calls erzeugt und keine Retry-/Timeout-Resilienz
  besitzt. Folgearbeit: alle Zuege klassifizieren, die pro Lauf priorisierten
  Schluesselstellungen tief analysieren, gewichtete Trainingspositionen
  erzeugen und konkrete Coach-Sessions erst on demand bilden.

## Spezialisiertes LLM-Training

Spezialisiertes LLM-Training wird erst nach belastbaren Benchmarks sinnvoll.
Vor einer Modellanpassung muessen Trainingsdaten, Evaldaten, Datenschutz,
Lizenzlage und Zielmetriken geklaert sein. Ein spezialisiertes Modell darf nur
ueber den Router eingebunden werden und muss gegen die gleiche Benchmark-
Familie antreten wie generische Modelle.
