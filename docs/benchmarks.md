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
- Source-Quality: Provenienz-Abdeckung, Quellen pro Fall,
  Domain-Diversitaet als Count, Freshness-Signal-Rate und Herkunftstypen,
  ohne URLs, Domains oder Snippets zu exportieren.
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
- Legal-Move-Tags und Stockfish-Qualitaetsbaender pro Stellung:
  `quiet`, `check`, `promotion`, `development`, `protects_piece`,
  `double_attack`, `triple_attack`, `discovery_attack`,
  `moves_out_of_attack` plus `loosing-blunder`, `blunder`, `mistake`,
  `ungenau`, `okay`, `good`, `stark`, `brilliant`.
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
- FEN-Validitaet per regelbasierter Brettpruefung.
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

Modelle werden nicht nur global verglichen, sondern pro Rolle und Teilaufgabe.
Ein Modell kann fuer `chess-small`/Klassifikation gut sein, aber fuer
`chess-analyst`, `chess-coach`, `chess-vision`, `chess-researcher` oder
Coding-Agenten schlechter passen. Benchmarkfaelle speichern deshalb Rolle,
Aufgabe und Modell getrennt; die Auswertung soll spaeter je Rolle einen
belastbaren Favoriten zeigen.

Die Website stellt dafuer eine geschuetzte Feedback-Seite bereit:

```text
https://cailama.org/benchmark-feedback.php
```

Sie liegt hinter Login, ist `noindex` und speichert Bewertungen in der
Provider-Datenbank. Die Tabellen heissen `cailama_model_benchmark_cases`,
`cailama_model_benchmark_observations` und `cailama_model_feedback`.
Benchmark-Runner duerfen secretfreie Beobachtungen ueber
`POST /api/v1/benchmarks/observations` importieren; bewertet wird danach in
der Website als Blind-Feedback. Die Feedback-Seite zeigt bei importierten
Laeufen nur Kandidaten-Codes, nicht das konkrete Modell. Rohprompts, volle
Modellantworten, private Partien, lokale Pfade und Secrets gehoeren nicht in
diese Tabellen; dort liegen nur vergleichbare Kennzahlen, kurze
Aufgabenbeschreibungen, knappe Auszuege und menschliches Feedback.

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
  `source_quality`-Kennzahlen fuer RAG-/Researcher-Faelle sind seit dem
  Folgestand im Goldset-/Benchmark-Vertrag enthalten. `semantic.enabled=false`
  bleibt empfohlen, bis ein groesseres Eval-Set produktiven Nutzen belegt.
- `docs/benchmark-results/2026-05-23.ptg-offline-baseline.md`:
  CaiLama-PTG-Offline-Baseline mit 3 freigegebenen Spielen, 21
  Schluesselstellungen, 13 Trainingskarten und 3/3 gueltigen PTG-Sessions.
  Die 21 Schluesselstellungen sind ein Ergebnis dieser Drei-Spiele-Baseline,
  keine allgemeine Obergrenze.
  Seit dem Folgestand sind Retry/Timeout/Checkpointing, gewichtete
  Trainingspositionen, Coach-Session on demand, Review-Gate, Planmodus,
  Hintergrund-Agenten, Benchmark-Events und strukturierte Legal-Move-
  Klassifikation vorhanden. PTG-Live-Verifikation gegen Router,
  Legal-Move-/Brettwahrheit-Artefakte, RAG-Provenienz, OCR/FEN-Gates,
  Analyse-Gates und TrainingSession-Gates sind umgesetzt. Offen bleibt die
  Auswertung der neuen Modelllauf-Beobachtungen im Website-Feedback.
- PTG-Drei-Spiele-Modellbenchmark:
  CaiLama stellt `scripts/run_ptg_model_benchmark.py` bereit. Der Runner
  extrahiert die drei freigegebenen Baseline-Spiele aus dem lokalen PGN-
  Archiv, laesst die PTG-Pipeline pro Router-Modell laufen, schreibt lokale
  Artefakte und kann die secretfreien Beobachtungen in die Website-API
  hochladen. Pro Modell werden Rollen-Probes fuer alle CaiLama-Rollen
  erzeugt: `router`, `small`, `large`, `task`, `translator`, `coach`,
  `analyst`, `critic`, `vision`, `scribe` und `researcher`. Der teure PTG-
  Classify-/Analyze-Teil erzeugt zusaetzlich aggregierte PTG-Faelle.
  `--skip-ptg` ist fuer schnelle Feedbacklaeufe erlaubt; `--max-analysis-
  positions` ist ein explizites Laufzeitbudget fuer den vollen PTG-Lauf,
  keine allgemeine 21er-Regel. Fuer vollstaendige Laeufe koennen
  `--llm-timeout-seconds 0`, `--role-max-tokens 0` und
  `--max-analysis-positions 0` gesetzt werden; fuer den finalen Upload gilt
  entsprechend `--upload-timeout-seconds 0`. `0` bedeutet in diesem Runner
  bewusst unbegrenzt.
- `docs/benchmark-results/2026-05-23.ocr-live-baseline.md`:
  OCR-Live-Benchmark mit 6 PDFs, 23 Diagrammen, 1686 Textzeichen, 6/6 Gates
  passed und 0% FEN-False-Positive-Rate. FENs werden weiterhin nicht geraten.

## Spezialisiertes LLM-Training

Spezialisiertes LLM-Training wird erst nach belastbaren Benchmarks sinnvoll.
Vor einer Modellanpassung muessen Trainingsdaten, Evaldaten, Datenschutz,
Lizenzlage und Zielmetriken geklaert sein. Ein spezialisiertes Modell darf nur
ueber den Router eingebunden werden und muss gegen die gleiche Benchmark-
Familie antreten wie generische Modelle.

## Offline-Benchmark-Smoke

`scripts/run_benchmark_smoke.py` (vom Master aus aufrufbar, kein Live-
Dienst, keine Secrets) prueft in einem Lauf, dass die Benchmark-Module aller
drei Repos importierbar und mit synthetischen Daten funktionsfaehig sind:

- **CaiLama PTG**: `scan_ptg_sessions` → `build_ptg_benchmark_summary` →
  `export_benchmark_json`.
- **CaiLama Events**: `BenchmarkStore.record` → `summary` → `export_json`.
- **Search**: `load_goldsets` + `validate_goldset` + `summarize_goldsets`.
- **Router**: `RequestMetrics.record_request` → `snapshot` JSON.

Aufruf:

```bash
python3 scripts/run_benchmark_smoke.py
```

`scripts/check-ecosystem.sh` fuehrt den Smoke automatisch als letzten Schritt
aus.  Er liefert nur Pass/Fail; keine lokalen Pfade, keine Live-Queries.

## Live-Modellbenchmark

Der echte Drei-Spiele-Lauf ist bewusst kein Standard-Check. Er benoetigt
Router, Stockfish und freigegebene lokale PGN-Daten. Der Lauf kann im
Hintergrund gestartet werden; fuer Feedback-Laeufe ist der Website-Upload
verbindlich. Der API-Token kommt nur aus einer lokalen Env-Variable und wird
nicht ausgegeben. Fuer schnelle Proben bleiben positive Limits sinnvoll:

```bash
env CAILAMA_LLM_PROVIDER=openai_compatible \
  CAILAMA_LLM_BASE_URL=http://127.0.0.1:18080/v1 \
  .venv/bin/python scripts/run_ptg_model_benchmark.py \
  --pgn /pfad/zum/freigegebenen/import.pgn \
  --output-dir ~/.local/share/cailama/benchmarks/ptg-models \
  --models kimi-k2.6:cloud,gemma4:31b-cloud,qwen3.5:397b-cloud,deepseek-v4-flash:cloud \
  --max-analysis-positions 7 \
  --upload-url https://cailama.org/api/v1/benchmarks/observations \
  --upload-token-env CAILAMA_DB_API_ADMIN_KEY \
  --require-upload
```

Vollstaendiger Lauf ohne clientseitige Benchmark-Kappung:

```bash
env CAILAMA_LLM_PROVIDER=openai_compatible \
  CAILAMA_LLM_BASE_URL=http://127.0.0.1:18080/v1 \
  .venv/bin/python scripts/run_ptg_model_benchmark.py \
  --pgn /pfad/zum/freigegebenen/import.pgn \
  --output-dir ~/.local/share/cailama/benchmarks/ptg-models \
  --models kimi-k2.6:cloud,deepseek-v4-flash:cloud,deepseek-v4-pro:cloud,gemma4:31b-cloud,qwen3.5:397b-cloud,glm-5.1:cloud,minimax-m2.7:cloud,nemotron-3-super:cloud,gpt-oss:20b-cloud,hemanth/chessplayer:latest,starling-lm:7b,gemma4:e2b,gemma4:e4b,qwen3.6:27b,qwen3.6:27b:think-off,qwen3.6:27b:think-on,qwen3.6:27b:think-low,qwen3.6:27b:think-medium,qwen3.6:27b:think-high \
  --llm-timeout-seconds 0 \
  --upload-timeout-seconds 0 \
  --role-max-tokens 0 \
  --max-analysis-positions 0 \
  --upload-url https://cailama.org/api/v1/benchmarks/observations \
  --upload-token-env CAILAMA_DB_API_ADMIN_KEY \
  --require-upload
```

Fuer diesen Lauf sollte der Router nicht ueber ein langsames Pi-Backend
round-robinnen. Der Router enthaelt dafuer
`configs/router.vm-dual-ollama.example.yaml` und
`docker/docker-compose.dual-ollama.example.yml`: zwei lokale Docker-Ollamas
auf der VM, beide mit lokalen Secret-Keys aus der Operator-Konfiguration.
Diese Container sind nur die zwei Cloud-Ausgaenge. Lokal auszufuehrende
Modelle laufen ueber den vorhandenen Host-Ollama auf `127.0.0.1:11434`, damit
sie nur einmal geladen werden muessen und die VM nicht zwei lokale Modelle
parallel startet:

```bash
ollama pull hemanth/chessplayer:latest
ollama pull starling-lm:7b
ollama pull gemma4:e2b
ollama pull gemma4:e4b
ollama pull qwen3.6:27b
```

Ollamas CLI bietet Thinking-Mode-Werte `false`, `true`, `low`, `medium` und
`high` fuer unterstuetzte Modelle. Der Router bildet diese fuer
`qwen3.6:27b` als eigene Aliase ab, damit der Benchmark die Modi blind wie
separate Modellkandidaten bewerten kann. Wenn ein Backend einen Modus nicht
unterstuetzt, soll der CaiLama-Benchmark-Runner den Fehler als Feedbackfall
erfassen und mit den uebrigen Modellen fortfahren.

Nach dem Eintragen oder Aendern der lokalen Ollama-Cloud-Keys in der
unversionierten Router-`.env` muessen die Container neu erstellt und der
Router neu gestartet werden. Die Container sind mit `restart: unless-stopped`
vorbereitet; Docker, Router und Search muessen als Boot-Dienste enabled sein,
damit die Benchmark-Infrastruktur nach einem Neustart wieder verfuegbar ist.
Praktischer Live-Befund: Fuer Ollama-Cloud reicht die Env-Key-Weitergabe
allein nicht immer; die Docker-Ollamas muessen im privaten Docker-Volume auch
eine signierte Ollama-Anmeldung besitzen oder im Container per `ollama signin`
eingerichtet werden. Fehlt diese Anmeldung, liefert Ollama bei Cloud-Modellen
HTTP 500 mit `internal service error` beziehungsweise meldet im CLI, dass eine
Anmeldung erforderlich ist. Signierte Ollama-Dateien und Keys bleiben lokale
Operator-Secrets und werden nicht dokumentiert oder versioniert.

Schneller Rollenlauf fuer heutiges Blind-Feedback:

```bash
env CAILAMA_LLM_PROVIDER=openai_compatible \
  CAILAMA_LLM_BASE_URL=http://127.0.0.1:18080/v1 \
  .venv/bin/python scripts/run_ptg_model_benchmark.py \
  --pgn /pfad/zum/freigegebenen/import.pgn \
  --output-dir ~/.local/share/cailama/benchmarks/ptg-models \
  --models kimi-k2.6:cloud,gemma4:31b-cloud,qwen3.5:397b-cloud,deepseek-v4-flash:cloud \
  --role-max-tokens 700 \
  --skip-ptg \
  --upload-url https://cailama.org/api/v1/benchmarks/observations \
  --upload-token-env CAILAMA_DB_API_ADMIN_KEY \
  --require-upload
```

Nach dem Upload erscheint der Lauf unter
`https://cailama.org/benchmark-feedback.php`. Dort wird pro Modell Feedback zu
Qualitaet, Aufgabenloesung, Logikfehlern und A/B-Praeferenz erfasst.
Jede Rolle erscheint als eigener Feedback-Fall. Die sichtbare Bewertung bleibt
blind: angezeigt wird nur ein Kandidaten-Code, nicht der Modellname.
`--role-max-tokens` begrenzt nur die kurzen Rollen-Probes auf
OpenAI-kompatiblen Backends. Damit werden Laufzeit und Antwortlaenge
vergleichbarer; der volle PTG-Classify-/Analyze-Lauf bleibt davon unberuehrt.
Lokale Artefakte erfassen Router-Header fuer Backend, Provider-Modell und
Fallback, sofern der Router sie liefert. Das ist wichtig, weil `vm`/`pi`-
Routing die Laufzeitmessung beeinflusst. Die geschuetzte Website zeigt diese
Zuordnung nicht im Bewertungsformular.
