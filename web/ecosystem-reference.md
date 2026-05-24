# CaiLama Ecosystem Reference

Zielgruppe: Menschen, Codex, Kimi und andere LLM-Agenten, die schnell den
aktuellen Systemzuschnitt verstehen müssen.

Stand: 2026-05-24 (CaiLama-/Search-Härtung, Runtime- und Website-Deploy).

## Kurzfassung

CaiLama ist ein Schachanalyse-, Trainings- und Automatisierungs-Ökosystem.
Es besteht aus vier Repositories mit klarer Trennung:

- `TotoBa/CaiLama-Master`: Gesamt-Doku, Webseite, Roadmap, Status,
  Orchestrierung und lokale Checks.
- `TotoBa/CaiLama`: Hauptsystem für Schachanalyse, Training, Profile,
  Agent-CLI, PGN/Stockfish und DGT-nahe Workflows.
- `TotoBa/CaiLama-LLM-Router`: eigenständiger OpenAI-kompatibler Router für
  LLM-Backends, Modell-Aliase, Policies und Fallbacks.
- `TotoBa/CaiLama-Search`: eigenständiger Such-, DWZ- und RAG-Dienst mit
  FastAPI, Meilisearch, Crawlern und Importpfaden.

Der Master ist kein Monorepo und kein Runtime-Repo. Die drei Unter-Repos sind
lokal vorhanden, aber im Master-Git ignoriert.

## Projektidentität

Aktuelle Namen:

- `CaiLama-Master`: Koordination der Projekte und Webseite.
- `CaiLama`: Hauptprojekt, historisch aus DGT-Chesstrainer entstanden.
- `CaiLama-LLM-Router`: generischer lokaler/cloudfähiger LLM-Router.
- `CaiLama-Search`: schachspezifisches Such-/Indexsystem für Webseiten,
  DWZ-/Spielerdaten, RAG-Kontext und optionales semantisches Retrieval.

Alte Namen dürfen nur als historische Referenz verwendet werden.

## Zielbild

CaiLama soll ein praxistaugliches Schachtrainingssystem werden, das folgende
Aufgaben ausführt:

- PGNs importieren.
- Hauptvarianten extrahieren.
- Stockfish-Analysen erzeugen.
- LLM-gestützte menschliche Kommentare ergänzen.
- Schlüsselstellungen erkennen.
- Trainingsaufgaben ableiten.
- DGT-Board-Training steuern.
- relevante externe Informationen suchen.
- Ergebnisse nachvollziehbar speichern.

Produktpositionierung:

- CaiLama ist eine Trainingswerkstatt, keine Consumer-Social-App und kein
  allgemeines Schachportal.
- Der harte Kern ist der Loop:
  PGN importieren -> Analyse und Grounding -> Schlüsselstellungen extrahieren
  -> Trainingsfragen erzeugen -> gültige Artefakte speichern -> Review-
  Ergebnisse in die nächste Priorisierung zurückführen.
- Primäre Zielgruppe sind Vereinsspieler, Trainer und ambitionierte
  Selbstlerner, die eigene Partien systematisch in wiederholbares Training
  überführen wollen.
- DGT-nahe Wiederholung, lokale/self-hosted Datenhaltung, Quellenprovenienz
  und Benchmarks sind bewusste Differenzierungshebel.
- Breite Social-, Feed-, Matchmaking- oder Mobile-First-Funktionen sind kein
  aktueller Schwerpunkt.

## Repository-Referenz

### TotoBa/CaiLama-Master

Rolle:

- Gesamt-Doku des Ökosystems.
- Human-Webseite unter `https://cailama.org/`.
- LLM-freundliche Referenzen unter `llms.txt`,
  `ecosystem-reference.md` und `data/ecosystem.json`.
- Suchmaschinen-Einstieg über `robots.txt` und `sitemap.xml`.
- PHP-Login-/Session-Shell, Webspace-API-Status und kontrollierter
  serverseitiger CaiLama-Dump-Import ohne versionierte Credentials.
- Geschütztes Benchmark-Feedback hinter Login mit Single-DB-Speicherung für
  Modellrollen, Laufzeiten, Tokenwerte, Qualitätsurteile und A/B-Präferenzen.
- Roadmap und Cross-Repo-Koordination.
- Produktpositionierung und Master-geführte Benchmarks.
- Lokale Checks ohne Schreibzugriffe in Unter-Repos.
- Öffentliche PHP-Controller unter `web/` und privater Smarty-App-Bereich
  unter `web-smarty/`; live als `public/` und privater Sibling `smarty/`.
  Benötigt wird `smarty/smarty ^5.0`; die Library wird nicht versioniert.

Erlaubt:

- `README.md`, `TODO.md`, `AGENTS.md`, `hinweise.md`.
- `docs/`, `scripts/`, `web/`.
- Plan-, Status- und Orchestrierungsdateien.

Verboten:

- Unter-Repo-Dateien tracken.
- Submodules anlegen.
- Secrets, Tokens, `.env` oder produktive Credentials speichern.
- Runtime-Logik ins Master-Repo verschieben.

### TotoBa/CaiLama

Rolle:

- Hauptsystem für Analyse, Training und Nutzerfluss.
- Agentische CLI, Slash-Commands, Tools und Rollen.
- PGN-I/O, Stockfish-Pipeline, statische Brettwahrheit, Datenbank,
  Spielerprofile, Plattformimporte, Knowledge/OCR, Queue, Training und
  DGT-nahe Adapter.

Wichtige Modulgruppen:

- `analysis`: Stockfish-Pipeline, Zugqualität, Sharpness, PGN-Annotation.
- `chess_eval`: Brettfakten ohne Engine, inklusive Legal-Move-Tags.
- `database`: SQLObject-Store, Migrationen, MariaDB/SQLite-Testpfade.
- `player_profile`: Profile, Plattformaccounts, importierte Partien,
  Rating-Aggregation.
- `training`: Karten, Sessions, Reviews, Live-Coach, Board-aware Tool-Chain.
- `agent`: Console, Controller, ToolRegistry, ConversationContext.
- `knowledge`: lokale Wissenskarten, OCR, Quellenmodelle.
- `queue`: dateibasierte Import-/Verarbeitungspfade.

Aktueller Fokus:

**Stand 2026-05-23:**
- Review-Gate nach erster Stockfish-Analyse: Nutzer kann Kandidaten-
  Stellungen vor LLM-Tiefenanalyse prüfen, hinzufügen, entfernen,
  priorisieren oder zurückstellen. `training/review_gate.py`
  mit persistentem JSON-Workflow; Slash-Command `/review`.
  `review_decision.json` kann aus Session-Verzeichnissen oder direkt per
  Dateipfad geladen werden.
- Planmodus: heuristisch oder LLM-gestützte schrittweise Aufgabenplanung.
  `training/plan_mode.py` mit `Plan`, `PlanStep`, persistentem JSON.
  Slash-Command `/plan`; Skills `generate_plan`, `plan_next_step`.
- Hintergrund-Agenten: `/task start|list|status|stop|result|max-steps`,
  persistente JSON-Jobs und synchron wartbarer Abbruch sind vorhanden.
- Modellrollen-Benchmark-Events: secretfreie Dauer- und Token-Metriken können
  aus Agent-Läufen für das geschützte Website-Feedback exportiert werden.
- Legal-Move-/Brettwahrheit: `BoardTruth` klassifiziert alle legalen Züge
  enginefrei mit Mehrfach-Tags (`quiet`, `check`, `promotion`,
  `development`, `protects_piece`, `double_attack`, `triple_attack`,
  `discovery_attack`, `moves_out_of_attack`). Das Agent-Tool
  `evaluate_legal_moves` gibt jetzt eine strukturierte Stockfish-Auswertung
  mit UCI/SAN, Score aus Sicht des Ziehers, Engine-Rang, Tags und
  Qualitätsband zurück.
- Slash-to-Skill-Vertrag: 25 built-in Tools als manifestbasierte Skills
  verfügbar; jedes Kern-Feature als Tool, UI-Steuerung als Slash-Command.
  Dokumentation in `docs/slash-tool-skill-contract.md`.
- DB-Hybridpfad ist im Grundschnitt umgesetzt: `database.access_mode` wählt
  `native`, `api` oder `hybrid`; API-Metadaten bleiben secretfrei und der
  DB-API-Statusclient fragt per geschütztem `POST /api/v1/status` nur
  fachliche Statusdaten ab.
- Provider-seitiger Dump-Import ist in der Webspace-API vorbereitet:
  `append` und `reset` verarbeiten nur eine serverseitig abgelegte `.sql`- oder
  `.sql.gz`-Datei; der Request selbst enthält außer Bearer-Key keine Daten.
- Provider-seitiges Schema-Setup läuft über admin-geschützte PHP-Endpunkte
  auf dem Webspace; lokale MySQL-Zugriffe auf Provider-DBs sind nicht der
  Betriebsweg.
- PTG Phase 2: Queue-Einspeisung ist im PTG-Kommando angebunden;
  `--run-llm-stages` verdrahtet die bestehende classify/analyze-Trifecta
  explizit vor der Kartengenerierung.
- PTG-Produktloop ist offline/deterministisch als Artefakt-Scheibe umgesetzt:
  pro Session entstehen `source.pgn`, `annotated.pgn`, `training.json` und
  `quality_gates.json`; die CLI meldet Schlüsselstellungen und gültige
  Sessions. Agent-/DGT-naher Kartenabruf, `ReplayPosition.card_id` und
  Review-Stats in der Trainingspriorisierung sind umgesetzt.
- Deterministisches Karten-Scoring, Fehler-/Mustertaxonomie und Kartentypen
  für personalisiertes Training sind umgesetzt.
- Interne Search-Anbindung ist Standardpfad für `web_search` und
  `search_dwz`; Recherchefragen schlagen `search_rag` vor.
- DWZ-Identity-Linking ist in Store und CLI integriert.
- RAG-Provenienz ist im Agent-/Researcher-Pfad normalisiert; Quellen,
  Freshness, Verwendungszweck und Unsicherheit werden promptfähig
  weitergereicht.
- PTG-Live-Verifikation gegen den Router, Legal-Move-/Brettwahrheit-Details in
  Review-/Coach-/Benchmark-Artefakten, Analyse-/Training-Qualitätsgates,
  OCR/FEN-Gates sowie Profil-Export und bestätigte Profil-Löschung sind
  umgesetzt.
- Modellrollen-Benchmarks sollen Dauer, Input-/Thinking-/Output-Tokens,
  Qualitätsurteile, Aufgabenlösung, Logikfehler und A/B-Präferenzen für PTG,
  Coach, Analyst, Researcher und Vision/OCR erfassen. Modelle werden
  rollenweise verglichen; Ziel ist ein belastbarer Favorit pro Aufgabe und
  Rolle, nicht nur ein globaler Sieger.
- Der Drei-Spiele-Modellbenchmark erzeugt Rollen-Probes fuer alle CaiLama-
  Rollen (`router`, `small`, `large`, `task`, `translator`, `coach`,
  `analyst`, `critic`, `vision`, `scribe`, `researcher`) und kann den teuren
  PTG-Teil fuer schnelle Blind-Feedbacklaeufe mit `--skip-ptg` auslassen.
- Offen bleiben Retention/Profilbindung für dateibasierte Trainingskarten und
  Review-Historien sowie die automatische Übernahme von Benchmarkmetriken in
  das geschützte Website-Feedback.
- OCR/FEN ist aktiv; FENs werden erst nach belastbarer Vision-/Template- und
  Validitätsprüfung ausgegeben.

Grenzen:

- Keine Live-Web-, Engine-, Router-, DB- oder Hardware-Zugriffe ohne
  ausdrücklichen Auftrag.
- Keine Secrets aus lokalen Configs anzeigen.
- Neue Fachlogik zuerst als importierbares Modul, CLI/Tools nur als Adapter.
- Keine ungeprüften LLM-Behauptungen als Brettwahrheit ausgeben.

### TotoBa/CaiLama-LLM-Router

Rolle:

- OpenAI-kompatible API für Modellzugriff.
- Modell-Aliase und Routing-Policies.
- Backend-Fallback, Round-Robin, Cooldowns und Fehlerweitergabe.
- Kimi-CLI- und CaiLama-kompatible Modellschicht.

Wichtige Endpunkte:

- `/health`
- `/v1/models`
- `/v1/chat/completions`

Relevante CaiLama-Rollen/Aliase:

- `chess-router`
- `chess-small`
- `chess-large`
- `chess-task`
- `chess-coach`
- `chess-analyst`
- `chess-critic`
- `chess-vision`
- `chess-scribe`
- `chess-researcher`

Aktueller Fokus:

- Streaming-Fehlerbehandlung für `stream: true`-Flows ist als finaler
  SSE-Fehlerchunk dokumentiert und getestet.
- Optionales Config-Hot-Reload ist über `runtime.reload_config_on_request`
  verfügbar.
- Backend-spezifisches Modell-Mapping per Alias ist validiert.
- `/metrics` kann JSON oder Prometheus-Text liefern.
- `mypy src` ist bereinigt.
- Backend-API-Key-Weitergabe, Usage-Metriken, `llm-router usage`,
  Master-kompatibler Benchmark-Export und generischer `endpoint_path` sind
  umgesetzt.
- Später können spezialisierte Modelle über denselben Router-Vertrag
  angebunden werden, aber ohne Schachproduktlogik im Router.
- Coding-Agentenmetriken und Schachrollenmetriken bleiben getrennt. Kimi nutzt
  lokal `kimi-k2.6:cloud`; Gemma4 bleibt nur für Schachrollen ein messbarer
  Kandidat, nicht für Kimi-/Coding-Arbeit.

Grenzen:

- Keine Schachproduktlogik im Router.
- Keine Provider-Secrets im Repo.
- Retrieval-Kontext bleibt Aufgabe von CaiLama/CaiLama-Search.

### TotoBa/CaiLama-Search

Rolle:

- Such-, DWZ- und RAG-Dienst.
- FastAPI-Zugriffsschicht.
- Meilisearch-Indizes für Webseiten, Chunks und DWZ-Spielerdaten.
- Crawler, Quellenlisten und DWZ-Importpfade.

Wichtige Endpunkte:

- `/healthz`
- `/readyz`
- `/v1/search`
- `/v1/context`
- `/v1/dwz/search`
- `/v1/dwz/player/{pkz}`
- `/v1/doc/{id}`
- `/v1/admin/reindex/dwz`
- `/v1/admin/reindex/source/{id}`

Aktueller Fokus:

- CaiLama-kompatibler API-Vertrag: `/v1/search` liefert native `hits` und
  normalisierte `items`/`results`; `/v1/context` liefert `context_blocks` sowie
  kompatible `context`/`sources`.
- Docker-Defaults: expliziter `MEILI_MASTER_KEY`, expliziter `MEILI_API_KEY`,
  lokale Port-Bindings und optionaler `ADMIN_SERVICE_TOKEN`.
- Crawler-Quellenpolitik, Robots-Gruppen, Source-Validierung und
  Reindex-Tracking sind offline getestet.
- Privacy-safe Search-Observability über `/v1/observability/search`.
- Synthetische Search-Goldsets für Suchvertrag, DWZ-Suche und RAG-Kontext
  sind versioniert und per CLI validierbar.
- Goldset-Testindex-Seeding ist über einen localhost-geschützten CLI-Pfad
  für isolierte Test-Meilisearch-Instanzen vorbereitet.
- Goldset-End-to-End-Smoke ist automatisiert: `goldsets smoke` startet eine
  temporäre lokale Meilisearch-Testinstanz, seedet synthetische Fixtures,
  startet die API mit deaktiviertem Scheduler und führt die Goldsets aus.
- Einheitliche Job-Orchestrierung mit CaiLama-Queue/Training.
- Optionale semantische Retrieval-Schicht ist implementiert, default-off und
  nur nach messbarem Eval-Nutzen produktiv freizugeben.
- Recall-/MRR-/Zero-Hit-/Latenz-Metriken, Master-kompatibler Benchmark-Export,
  RAG-Provenienz und Datenvertrag sind umgesetzt.
- Docker-fähiger Vergleich `lexical` gegen `hybrid` ist dokumentiert:
  Recall@5/10 bleibt in beiden Modi 1.0; MRR ist in beiden Modi 0.9167;
  filter+hybrid-500er und Multi-Index-Response sind behoben, beide Modi
  erreichen Pass-Rate 1.0. `semantic.enabled=false` bleibt Default, bis ein
  größeres Eval einen produktiven Nutzen belegt.
- DWZ-Staging-Test: `dwz_staging.py` validiert lokale DSB-CSV-ZIP-Artefakte
  offline ohne Import, Netzwerk oder Meilisearch-Mutation. Live-Download und
  echter Import bleiben bewusste manuelle Schritte.

Grenzen:

- Keine echten Meilisearch-Keys, Admin-Keys, Master-Keys oder `.env`-Dateien
  committen.
- Live-Importe, Crawler-Läufe und Netzwerkzugriffe nur auf ausdrücklichen
  Auftrag.

## Schnittstellen

### CaiLama -> CaiLama-LLM-Router

Zweck:

- LLM-Zugriff über OpenAI-kompatible Endpunkte.
- Rollenbasierte Modellwahl.
- Fallbacks ohne Produktlogik in CaiLama zu duplizieren.

Vertrag:

- CaiLama kennt logische Rollen.
- Router löst Rollen/Aliase auf Provider-Modelle und Backends auf.
- Router loggt keine Prompt-/Response-Inhalte standardmäßig.

### CaiLama -> CaiLama-Search

Zweck:

- kontrollierte Suche statt fragiler Browser-Websuche.
- RAG-Kontext für Researcher/Analyst.
- DWZ-Daten für Profile und Training.

Vertrag:

- Search liefert strukturierte Such- und Kontextantworten.
- CaiLama normalisiert Resultate über einen SearchAdapter.
- Quellenprovenienz bleibt sichtbar.
- `/v1/search` ist kanonisch `POST`, bleibt für einfache Clients aber auch
  als `GET` kompatibel. JSON-Felder `query`/`limit` werden akzeptiert.
- `/v1/context` akzeptiert Query-Parameter oder JSON und liefert kompatible
  Felder für RAG-Verbraucher.
- Browserbasierte Suche ist nur Fallback.

### CaiLama -> Webspace-DB-API

Zweck:

- Fachlicher DB-Zugriff über HTTPS statt direkter Provider-DB-Exposition.
- Wahl zwischen lokaler DB, Provider-API und Hybridbetrieb vorbereiten.
- Login/Session der Website über `web_users` in derselben Provider-Datenbank
  wie die CaiLama-Fachdaten abbilden.
- Geschütztes Human-Feedback für Modellrollen-Benchmarks wiederverwendbar
  sammeln.

Vertrag:

- Keine SQL-over-HTTP-Endpunkte.
- Echte DB-Zugangsdaten stehen nur in der privaten Webspace-Konfiguration
  außerhalb von `/public`.
- `databases.cailama` ist die einzige PDO-Verbindung (Single-DB-Mode).
- `POST /api/v1/status` ist der geschützte Statuspfad; ohne gültigen
  Bearer-Key liefert die API keine API- oder DB-Details.
- `POST /api/v1/imports/cailama/append` fügt Daten aus der konfigurierten
  serverseitigen Dump-Datei hinzu.
- `POST /api/v1/imports/cailama/reset` setzt die CaiLama-Datenbank nur dann
  zurück, wenn `allow_reset` lokal bewusst aktiviert wurde.
- `POST /api/v1/admin/schema/cailama` und
  `POST /api/v1/admin/schema/all` wenden dasselbe Single-DB-Schema über die
  API an.
- `/benchmark-feedback.php` ist eine Login-geschützte Website-Seite und nutzt
  `cailama_model_benchmark_cases`,
  `cailama_model_benchmark_observations` sowie
  `cailama_model_feedback` in `databases.cailama`.
- `POST /api/v1/benchmarks/observations` importiert secretfreie
  Benchmark-Laufdaten mit `benchmark:write` oder `admin`; Rohprompts,
  vollständige Antworten, lokale Pfade und Secrets sind ausgeschlossen.
- Importierte Benchmark-Läufe werden im Website-Feedback blind angezeigt:
  Nutzer sehen Kandidaten-Codes statt Modellnamen; die Zuordnung bleibt
  serverseitig in der Datenbank.
- Es gibt keine öffentliche Registrierung; Nutzer werden direkt in
  `web_users` angelegt.
- Import- und Schema-Endpunkte akzeptieren keine Query-Parameter und keinen
  Request-Body.
  Wenn keine konfigurierte Importdatei vorhanden ist, wird der Import
  abgelehnt; nach erfolgreichem Import wird die Datei gelöscht.
- Status, Append-Import, Reset-Import und Admin nutzen getrennte Keys/Scopes;
  Reset benötigt `db_import:reset` oder `admin`.
- Echte Webspace-Konfiguration liegt außerhalb des Public-Webroots unter
  `cailama-private/api/config.local.php`.
- `web/api_app/config.local.sample.php` ist nur Vorlage.

### Master -> Unter-Repos

Zweck:

- Dokumentieren.
- Koordinieren.
- Prüfen.

Vertrag:

- Master nimmt keine Unter-Repo-Dateien auf.
- Master schreibt nicht in Unter-Repos.
- Master enthält keine Secrets und keine Runtime-Logik.

## Roadmap

Jetzt:

- CaiLama-DB-Hybridpfad weiter härten: native/API/hybrid ist konfigurierbar,
  der fachliche API-Statuspfad ist angebunden und Provider-Dump-Importe laufen
  serverseitig über `append`/`reset`; fachliche Read-/Write-Endpunkte bleiben
  Folgearbeit.
- Trainingsfokus schärfen: CaiLama liefert Trainingsarbeit und reproduzierbare
  Artefakte, nicht Social- oder Plattformfunktionen.
- RAG-Provenienz ist im Agent-/Researcher-Pfad normalisiert;
  DWZ-Identity-Linking in Store/CLI ist umgesetzt.
- Router-Infrastrukturwelle ist abgeschlossen: Backend-API-Key-Weitergabe,
  privacy-safe Token-/Usage-Metriken, `llm-router usage`, Benchmark-Export
  und generische Endpoint-Pfade sind umgesetzt.
- Search als aktuellen Ausbau-Fokus weiter messen: lexical-vs-hybrid ist
  benchmarkbar dokumentiert, Filter-/Multi-Index-Bugs, DWZ-Staging und
  privacy-safe RAG-/Researcher-`source_quality`-Kennzahlen sind erledigt;
  offen bleibt die Freigabeentscheidung für Hybrid auf größerem Eval.
- Modellrollen-Hypothese als Benchmark validieren: geschütztes Website-
  Feedback erfasst importierte Laufdaten, Tokenwerte, Qualität,
  Aufgabenlösung, Logikfehler und A/B-Präferenz.

Danach:

- Retention/Profilbindung für dateibasierte Trainingskarten und Review-
  Historien abschließen; importierte Website-Benchmarkläufe fachlich
  bewerten und daraus Modell-/Prompt-Regeln ableiten.
- Einheitliche Job-Orchestrierung vorbereiten.
- Benchmark-Rahmen im Master weiter ausbauen, Website-Feedback mit Router- und
  CaiLama-Metriken verbinden und Ergebnisse repo-übergreifend dokumentieren.

Später:

- RAG-Analysepakete produktnah in Researcher-/Analyst-Flows nutzen.
- Einheitliche Job-Orchestrierung für Import, Crawl, Game-Analyse, PTG und
  Reindex.

Ausbau:

- Observability/KPIs für Router, Search und PTG.
- Semantische Retrieval-Evaluation in CaiLama-Search gegen Goldset-Baseline.
- Später spezialisiertes LLM-Training vorbereiten, aber erst nach
  Datenfreigabe, Benchmark-Baseline, Eval-Trennung und Router-kompatibler
  Bereitstellung.

## Qualitätsregeln

- Erst Stand prüfen, dann ändern.
- Keine Aussagen über Repo-Zustände erfinden.
- Keine stillen Fehler oder Schein-Erfolge.
- Tests für Kernmodule und Smoke-Tests für CLI/API.
- PGN-Ausgabe validieren.
- Router-Fallbacks nur bei Router-Auftrag erneut testen.
- Datenbankzugriffe testen.
- Doku aktuell halten.
- Keine Secrets in Doku, Code oder Beispielen.

## Maschinenlesbare Quellen

- `docs/data/ecosystem.json`: versionierte Maschinenreferenz im Master.
- `docs/product-positioning.md`: Trainingsfokus, Zielgruppe, Kernloop und
  Qualitätsgrenzen.
- `docs/benchmarks.md`: Master-Rahmen für repo-übergreifende Benchmarks.
- `docs/benchmark-results/README.md`: Formatregeln für spätere
  Master-Benchmark-Ergebnisse.
- `docs/benchmark-results/model-role-matrix.current.md`: aktuelle
  Modellrollen-Hypothese und Feedback-Metriken.
- `web/data/ecosystem.json`: ausgelieferte Maschinenreferenz auf der Webseite.
- `web/llms.txt`: LLM-Einstiegspunkt für `https://cailama.org/`.
- `web/ecosystem-reference.md`: ausgelieferte LLM-freundliche Markdown-Version.
- `web/robots.txt`: Crawler-Regeln mit Sitemap-Verweis.
- `web/sitemap.xml`: kanonische XML-Sitemap für öffentliche URLs.
