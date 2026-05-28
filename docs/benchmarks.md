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
- Game-Flow-Artefakt: Wertverlauf, Bewertungspruenge, gegnerische Fehler,
  Ausnutzung/Miss, scharfe/forcierte Stellungen, Forcing-Tiefe,
  kanonische `position_id` und PV-/Material-Evidenz.
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
- Dauerbewertung 1 bis 5, damit Laufzeit nicht nur als Freitextkommentar
  erfasst wird.
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
https://cailama.org/benchmark-feedback-item.php
https://cailama.org/benchmark-feedback-results.php
```

Sie liegt hinter Login, ist `noindex` und speichert Bewertungen in der
Provider-Datenbank. Die Tabellen heissen `cailama_model_benchmark_cases`,
`cailama_model_benchmark_observations` und `cailama_model_feedback`.
Benchmark-Runner duerfen secretfreie Beobachtungen ueber
`POST /api/v1/benchmarks/observations` importieren; bewertet wird danach in
der Website als Blind-Feedback. Die Feedback-Seite zeigt bei importierten
Laeufen nur Kandidaten-Codes, nicht das konkrete Modell. Sichtbar sind
Aufgabenzusammenfassung, die eigentliche Modellfrage, vollstaendiger System- und
User-Prompt innerhalb des technischen Importlimits, erwarteter Ausgabetyp,
Laufzeit-/Tokenmetriken, moeglichst vollstaendige Modellantwort oder Fehler und
bei stellungsbezogenen Faellen ein responsives Schachbrett aus der FEN. Private
Partiearchive, lokale Pfade und Secrets gehoeren nicht in diese Tabellen; dort
liegen vergleichbare Kennzahlen, Aufgaben-/Promptkontext, optionaler
Stellungskontext und menschliches Feedback. Dauer/Tempo und
Uebersetzung/deutsche Ausgabe koennen als eigene Bewertungsdimensionen erfasst
werden. Die Ergebnis-Seite
zeigt Aggregationen pro Lauf, Rolle, Fall und Kandidat weiterhin ohne
Modellnamen.
Die offene Feedbackliste zeigt alle noch unbewerteten Beobachtungen eines
Laufs; bereits bewertete Kandidaten verschwinden aus der Liste. Ein Klick auf
`Bewerten` oeffnet eine fokussierte Einzelfallseite. Der Playmodus laedt nach
dem Speichern automatisch den naechsten offenen Fall, damit lange Laeufe zuegig
und ohne Modell-Bias bewertet werden koennen.

Für agentengestützte Auswertung gibt es zusätzlich geschützte API-Endpunkte:

- `POST /api/v1/benchmarks/feedback/open` mit Scope `benchmark:feedback` oder
  `admin` liefert offene Beobachtungen inklusive Aufgabe, Promptauszügen,
  Modellausgabe, Metriken, Kandidaten-Code und Run-Übersicht. `run_key`,
  `limit` und `include_model_labels` sind optionale Body-Felder;
  Modellnamen werden nur mit `admin`-Scope ausgeliefert.
- `POST /api/v1/benchmarks/feedback` mit Scope `benchmark:feedback` oder
  `admin` speichert ein Feedback-Objekt oder ein `feedback`-Array. Erforderlich
  sind `observation_id`, `quality_score`, `task_solution_score`,
  `duration_score`, `logic_error_level` und `preferred_option`; optionale Felder
  sind `translation_score`, `feedback_text`, `improvement_note` und
  `translation_note`.
- `POST /api/v1/benchmarks/feedback/summary` liefert rollen- und
  modellgruppierte Aggregationen, Fehlerklassen pro Rolle/Kandidat und
  Rollenempfehlungen mit bester Kandidat, Ausschlussgruenden und offenen
  Vertragsfehlern. Modellnamen werden nur mit `admin`-Scope und
  `include_model_labels` ausgeliefert.

Der Master enthält mit `scripts/benchmark_feedback_agent.py` einen
secretfreien CLI-Client für diese Feedback-API. Er liest den Bearer-Key aus
lokaler privater Konfiguration oder Umgebung, bewertet offene Fälle mit
einfachen rollen- und Ausgabetyp-Heuristiken, schreibt optional eine
Summary-JSON und zeigt Top-Kandidaten pro Rolle. Standardmäßig bleiben
Modellnamen verborgen; `--dry-run` prüft offene Fälle ohne Schreibzugriff.
Der Agent ist für automatisch bewertbare Vertrags-, Struktur-, Dauer- und
Basisqualitätsfälle gedacht und ersetzt keine fachliche menschliche Bewertung
von Schachintuition, Analysequalität oder unerwartet sinnvollen Tool-Aufrufen.

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
  Archiv, fuehrt zuerst den rollenbasierten Modellbenchmark aus, schreibt
  lokale Artefakte und kann die secretfreien Beobachtungen in die Website-API
  hochladen. Die Benchmark-Aufgaben und Prompt-Templates liegen versioniert im
  Master unter `benchmarks/model-role/` und werden beim Runtime-Deploy in die
  CaiLama-Runtime kopiert. Aktueller Startpunkt sind 10 editierbare Aufgaben
  je Rolle. Alle Rollenaufgaben haben Schachbezug: konkrete FEN-Stellungen aus
  den drei freigegebenen Baseline-Spielen, oeffentliche Referenzmotive wie
  Opera Game/Kiwipete/Turmendspiele, OCR/FEN-Gates, Coach-/DGT-Flows,
  Schachuebersetzung und schachbezogene RAG-/Recherchefaelle. Jede Aufgabe
  beschreibt erwartete Ausgabe, optionale Tool-Erwartungen samt
  Argumentstruktur und objektive Auto-Checks. Mit
  `--models auto` liest der Runner die Kandidaten aus dem Router-Endpoint
  `/v1/models`; operative Router-Aliase wie `default`, `kimi-cli-default` und
  `chess-*` werden dabei standardmaessig aus der CaiLama-Benchmarkliste
  entfernt. Lokale Modelle sind im automatischen Langlauf ebenfalls
  ausgeschlossen, solange `--include-local-models` nicht explizit gesetzt
  wird. Pro Modell werden nacheinander alle Rollenaufgaben fuer alle
  CaiLama-Rollen erzeugt: `router`, `small`, `large`, `task`, `translator`,
  `coach`, `analyst`, `critic`, `vision`, `scribe` und `researcher`.
  `router`-Aufgaben laufen ueber `ModelRouter.decide()`, `task`-Aufgaben ueber
  einen begrenzten `AgentLoop`; beide verwenden dieselben Parser und
  Output-Vertraege wie die Console. Der Runner nutzt die Rollen-Systemprompts
  aus demselben kopierten
  `system_prompts/`-Verzeichnis; damit weichen Benchmark und interaktive
  Console nicht still auseinander. Das ist wichtig fuer Coach-/Vision-Faelle,
  in denen das Modell keine Bretter oder FENs erfinden darf, sondern nur
  belegte FENs weitergibt und die Console das Unicode-Brett rendert. Die
  Reihenfolge ist modellzentriert: ein Modell durchlaeuft alle Rollenaufgaben,
  danach startet das naechste Modell. Erst nach der automatischen Rollen-
  Zuordnung startet der teure PTG-Flow-/Schluesselstellungsteil, und dann nur
  fuer die Modelle, die mindestens eine Rolle uebernommen haben. Dieser PTG-
  Teil nutzt denselben Game-Flow-, PromptBuilder- und Brettwahrheitspfad wie
  die interaktive Console; es gibt keine separate Benchmark-Analyseprompts.
  Beobachtungen enthalten neben Dauer, Tokens, Artefakt, eigentlicher
  Modellfrage und moeglichst vollstaendiger Ausgabe auch den konstruierten
  System- und User-Prompt der Rollenprobe, erwarteten Ausgabetyp, optionale
  FEN/Side-to-move- und Kandidatenzug-Auszuege sowie `total_tokens`, Verbrauchsklasse,
  Verbrauchsgewicht, gewichtete Token-Einheiten und geschaetzte
  Usage-Einheiten. Diese Kostenfelder sind ein Vergleichsproxy fuer kleine
  und grosse Ollama-Cloud-Modelle, keine Provider-Abrechnung. Backend-Fehler,
  abgelehnte Thinking-Modi und harte Strukturfehler wie falsche Router-
  Toolwahl, ungueltiges JSON, fehlende Quellenmarker oder geratene FENs werden
  als Feedbackfaelle importiert und brechen den Gesamtlauf nicht ab. Router-/
  Provider-Fehler 429/500/503 werden pro LLM-Call bis zu drei Mal mit
  Wartezeit wiederholt; erst danach wird der Fall als Fehler exportiert.
  Leere Modellantworten und konkrete Vertragsfehlerklassen wie `invalid_json`,
  `missing_required_field`, `unexpected_tool`, `boardtruth_conflict`,
  `empty_optional_field_reference` oder `missing_citation` werden automatisch
  geschlossen, damit sie nicht in der manuellen Bewertung landen. Strukturfehler
  koennen serverseitig automatisch als nicht manuell bewertbar geschlossen
  werden. Unerwartete, aber formal strukturierte Tool-Aufrufe bleiben als
  menschlich zu bewertende Feedback-Faelle offen. Der Website-Upload streamt
  Beobachtungen in 50er-Batches, sobald ein Batch voll ist;
  erste Feedbackfaelle koennen daher bereits bewertet werden, waehrend der
  lange Benchmark weiterlaeuft. Rollen-Probes sprechen intern englisch; Deutsch
  ist eine Ausgabe-/Uebersetzungsschicht und kann separat bewertet werden. Die
  Rolle `translator` enthaelt eigene Aufgaben mit und ohne kleines
  Schachwoerterbuch; im Feedback bleibt der englische Ausgangstext sichtbar,
  damit die Uebersetzung nicht still ungetestet bleibt.
  Die Rollenprobe nutzt fuer Nicht-Router-Rollen den echten CaiLama-
  `PromptBuilder` inklusive Brettwahrheit-/Kontextbloecken; RAG-/Researcher-
  Faelle holen ihren Kontext vor dem Prompt ueber das echte `search_rag`-Tool
  und bekommen dadurch denselben Search-Kontext wie der Live-Pfad. Der
  Search-Seed enthaelt neben kuratiertem Basiswissen den Lichess-ECO-Katalog
  A-E, damit Eröffnungsnamen und Zugfolgen in Benchmark und Live-Konsole
  gleich auffindbar sind. Externe Websuche kann ueber eine lokale SearXNG-
  Instanz angebunden werden; ohne Konfiguration bleibt der interne
  CaiLama-Search-Pfad massgeblich. Router-
  Probes nutzen denselben kompakten Router-Prompt mit aktueller Toolliste wie
  die interaktive Konsole. Es gibt dadurch keine parallele Benchmark-
  Promptlogik.
  `--skip-ptg` ist fuer schnelle Feedbacklaeufe erlaubt; `--max-analysis-
  positions` ist ein explizites Laufzeitbudget fuer die tiefen
  PTG-Schluesselstellungen,
  keine allgemeine 21er-Regel. Fuer vollstaendige Laeufe koennen
  `--llm-timeout-seconds 0`, `--role-max-tokens 0` und
  `--max-analysis-positions 0` gesetzt werden; fuer den finalen Upload gilt
  entsprechend `--upload-timeout-seconds 0`. `0` bedeutet in diesem Runner
  bewusst unbegrenzt.
  Der LLM-Router begrenzt parallel laufende Requests zusätzlich pro Ollama-
  Backend: in der aktuellen Dual-Ollama-Runtime maximal drei gleichzeitige
  Cloud-Requests je Docker-Ollama-Account und maximal ein lokaler Host-Ollama-
  Request. Der Runner darf dadurch weiterhin parallel starten; der Router
  reiht zusätzliche Requests pro Backend ein, statt einen einzelnen Account zu
  überlasten.
  PTG-Modelllaeufe verwenden standardmaessig MariaDB/MySQL ueber die lokale
  CaiLama-Runtime-Konfiguration beziehungsweise `CAILAMA_BENCHMARK_DB_URI`.
  SQLite ist nur ein expliziter Kurztestpfad und soll nicht auf NAS-/CIFS-
  Pfaden genutzt werden. Ohne `--ptg-isolated-databases` wird die
  konfigurierte Datenbank verwendet; CaiLama erzeugt pro Modell eindeutige
  Benchmark-Profile und Game-IDs.
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
  --models auto \
  --task-catalog config/model_role_benchmark/tasks.json \
  --tasks-per-role 10 \
  --max-analysis-positions 7 \
  --ptg-db-backend mariadb \
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
  --models auto \
  --task-catalog config/model_role_benchmark/tasks.json \
  --tasks-per-role 10 \
  --llm-timeout-seconds 0 \
  --upload-timeout-seconds 0 \
  --role-max-tokens 0 \
  --max-analysis-positions 0 \
  --cloud-concurrency 4 \
  --llm-retry-attempts 3 \
  --llm-retry-wait-seconds 180 \
  --ptg-db-backend mariadb \
  --upload-url https://cailama.org/api/v1/benchmarks/observations \
  --upload-token-env CAILAMA_DB_API_ADMIN_KEY \
  --require-upload
```

Fuer diesen Lauf sollte der Router nicht ueber ein langsames Pi-Backend
round-robinnen. Der Router enthaelt dafuer
`configs/router.vm-dual-ollama.example.yaml` und
`docker/docker-compose.dual-ollama.example.yml`: zwei lokale Docker-Ollamas
auf der VM, beide mit eigenen persistenten Ollama-Cloud-Anmeldungen in ihren
Docker-Volumes.
Diese Container sind nur die zwei Cloud-Ausgaenge. Cloud-Modelle werden im
aktuellen Langlauf modellzentriert nacheinander getestet; die Router-Backends
duerfen dennoch pro Account bis zu drei gleichzeitig laufende Requests halten,
falls ein spaeterer Lauf wieder Parallelitaet nutzt. Lokale Modelle bleiben
aktuell komplett aus `--models auto` heraus, damit der Benchmark nicht durch
lokale Ladezeiten blockiert. Wer lokale Modelle bewusst mitpruefen will, muss
`--include-local-models` explizit setzen; Host-Ollama bleibt dabei die einzige
lokale Modellspur.
Der nackte Basis-Alias wird bei Modellen mit expliziten `:think-*`-Varianten
nicht gebenchmarkt, weil der modellseitige Thinking-Default sonst unklar
beziehungsweise doppelt zu einem expliziten Modus waere.
`hemanth/chessplayer:latest` bleibt aktuell ausgenommen, weil ein wiederholter
Pull einen fehlerhaften GGUF-Blob lieferte und Host-Ollama in einen Crash-Loop
brachte; die Quarantaene ist lokale Runtime-Wartung und gehoert nicht ins Repo.

Ollamas CLI bietet Thinking-Mode-Werte `false`, `true`, `low`, `medium` und
`high` fuer unterstuetzte Modelle. Der Router bildet diese fuer alle
nachweislich thinking-faehigen lokalen und Cloud-Modelle als eigene Aliase ab;
GPT-OSS-Modelle nutzen nur die dokumentierten Stufen `low`, `medium` und
`high`. Wenn ein Backend einen Modus nicht unterstuetzt, erfasst der CaiLama-
Benchmark-Runner den Fehler als Feedbackfall und faehrt mit den uebrigen
Modellen fort.

Die Container sind mit `restart: unless-stopped` vorbereitet; Docker, Router
und Search muessen als Boot-Dienste enabled sein, damit die Benchmark-
Infrastruktur nach einem Neustart wieder verfuegbar ist. Praktischer
Live-Befund: Fuer Ollama-Cloud ist die signierte Ollama-Anmeldung im privaten
Docker-Volume entscheidend. API-Keys sind fuer diese Docker-Ollamas nicht
erforderlich, wenn die Anmeldung vorhanden ist; dann sollte der Router keinen
Authorization-Header an die Docker-Ollamas senden, damit ein falscher Key den
eingeloggten Account nicht uebersteuert. Docker persistiert diese Anmeldung in
named volumes ueber Neustarts hinweg. Fehlt diese Anmeldung, liefert Ollama bei
Cloud-Modellen HTTP 500 mit `internal service error` beziehungsweise meldet im
CLI, dass eine Anmeldung erforderlich ist. Signierte Ollama-Dateien und Keys
bleiben lokale Operator-Secrets und werden nicht dokumentiert oder versioniert.

Schneller Rollenlauf fuer heutiges Blind-Feedback:

```bash
env CAILAMA_LLM_PROVIDER=openai_compatible \
  CAILAMA_LLM_BASE_URL=http://127.0.0.1:18080/v1 \
  .venv/bin/python scripts/run_ptg_model_benchmark.py \
  --pgn /pfad/zum/freigegebenen/import.pgn \
  --output-dir ~/.local/share/cailama/benchmarks/ptg-models \
  --models auto \
  --task-catalog config/model_role_benchmark/tasks.json \
  --tasks-per-role 10 \
  --role-max-tokens 700 \
  --skip-ptg \
  --upload-url https://cailama.org/api/v1/benchmarks/observations \
  --upload-token-env CAILAMA_DB_API_ADMIN_KEY \
  --require-upload
```

Nach dem Upload erscheint der Lauf unter
`https://cailama.org/benchmark-feedback.php`; aggregierte Auswertungen liegen
unter `https://cailama.org/benchmark-feedback-results.php`. Dort wird pro
Aufgabe und Kandidat Feedback zu Qualitaet, Aufgabenloesung, Dauer,
Uebersetzung, Logikfehlern und A/B-Praeferenz erfasst. Der Playmodus waehlt
offene Faelle zufaellig, bewertete Faelle verschwinden aus der offenen Liste.
Die sichtbare Bewertung bleibt blind: angezeigt wird nur ein Kandidaten-Code,
nicht der Modellname und nicht die Verbrauchsklasse. Der einzelne Feedbackfall
zeigt den vollstaendigen System- und User-Prompt der Rollenprobe, damit die
Antwort gegen den tatsaechlichen Konsolenkontext bewertet werden kann. Diese
Prompts duerfen Rolle, Aufgabe, Toolliste, Brettwahrheit, FEN und
Kandidatenzuege enthalten, aber keine Modellidentitaet, keine
Verbrauchsklasse, keine lokalen Pfade und keine Secrets.
`--role-max-tokens` begrenzt nur die kurzen Rollen-Probes auf
OpenAI-kompatiblen Backends. Damit werden Laufzeit und Antwortlaenge
vergleichbarer; der volle PTG-Flow-/Schluesselstellungslauf bleibt davon unberuehrt.
Lokale Artefakte erfassen Router-Header fuer Backend, Provider-Modell und
Fallback, sofern der Router sie liefert. Das ist wichtig, weil `vm`/`pi`-
Routing die Laufzeitmessung beeinflusst. Die geschuetzte Website zeigt diese
Zuordnung nicht im Bewertungsformular.
