# Roadmap

Diese Roadmap ueberfuehrt die Punkte aus `status.plan.cailama.md` in eine
laufend pflegbare Master-Sicht. Die Umsetzung erfolgt in den jeweiligen
Unter-Repositories.

## Jetzt

### Produktpositionierung

Ziel-Repo: `TotoBa/CaiLama-Master`

Koordinationspunkte:

- CaiLama als Trainingswerkstatt fuer ambitionierte Spieler, Trainer und
  ernsthafte Selbstlerner beschreiben.
- Kernloop priorisieren: PGN importieren, Analyse erden,
  Schluesselstellungen extrahieren, gewichtete Trainingspositionen und
  Trainingsfragen erzeugen, konkrete Coach-Sessions on demand starten,
  gueltige Artefakte speichern und Review-Ergebnisse zurueckfuehren.
- Keine Social-, Feed-, Matchmaking- oder Mobile-First-Funktionen als
  Roadmap-Schwerpunkt aufnehmen.
- DGT-nahe Wiederholung, lokale Datenhaltung, Quellenprovenienz und
  Benchmarks als Differenzierungshebel sichtbar machen.

### CaiLama DB-Hybridpfad

Ziel-Repo: `TotoBa/CaiLama`

Koordinationspunkte:

- Umgesetzt: Konfigurationsmodus `database.access_mode` fuer `native`, `api`
  und `hybrid` ist definiert.
- Native MariaDB/MySQL lokal fuer Aufbau und Backup nutzen.
- Fachliche Webspace-DB-API als Provider-Pfad ist mit einem begrenzten,
  geschuetzten POST-Statusclient vorbereitet.
- Umgesetzt: Webspace-API stellt no-query/no-body-Import-Endpunkte fuer
  serverseitig hochgeladene CaiLama-Dumps bereit:
  `POST /api/v1/imports/cailama/append` und
  `POST /api/v1/imports/cailama/reset`.
- Fehlende Importdateien werden abgelehnt; erfolgreich importierte Dateien
  werden geloescht.
- Umgesetzt: private Webspace-Konfig liegt ausserhalb des Public-Webroots;
  Status, Append, Reset und Admin nutzen getrennte Keys/Scopes.
- Umgesetzt: Provider-DB-Schemaanlage laeuft ueber admin-geschuetzte
  PHP-Endpunkte auf dem Webspace, nicht ueber direkten lokalen Provider-DB-
  Zugriff.
- Umgesetzt: Single-Database-Mode fuer Webspace-Betrieb. Website-Login
  (`web_users`) und CaiLama-Fachdaten leben in derselben Provider-Datenbank
  unter `databases.cailama`; der alte separate Auth-DB-Pfad ist entfallen.
- Umgesetzt: geschuetztes Benchmark-Feedback nutzt dieselbe Provider-
  Datenbank und speichert Modellrollen-Faelle sowie menschliche Bewertungen in
  `cailama_model_benchmark_cases` und `cailama_model_feedback`. Es gibt keine
  oeffentliche Registrierung; Nutzer werden direkt in `web_users` angelegt.
- Umgesetzt: Live-Status meldet `databases.cailama: ok`; Provider-Schema-
  Setup laeuft ueber `POST /api/v1/admin/schema/cailama` beziehungsweise
  `POST /api/v1/admin/schema/all`.
- Keine generische SQL-over-HTTP-API einfuehren.
- Offen: fachliche Read-/Write-Endpunkte und kontrollierte Hybrid-
  Synchronisation bleiben Folgearbeit.

### CaiLama Search/DWZ/RAG-Integration

Ziel-Repo: `TotoBa/CaiLama`

Koordinationspunkte:

- `SearchAdapter` als Standardpfad fuer `/v1/search`, `/v1/context` und
  `/v1/dwz/*` nutzen.
- Rueckgabeformate `items`/`results`, `context`/`sources` und DWZ normalisieren.
- Modi `internal_first`, `external_fallback`, `external_only` und
  `internal_only` pruefen.
- Browserbasierte Websuche nur als bewussten Fallback nutzen.
- Umgesetzt: `web_search` und `search_dwz` nutzen `SearchAdapter`; Recherche-
  und Quellenfragen schlagen `search_rag` vor.

### Router-Status: Infrastrukturwelle abgeschlossen

Ziel-Repo: `TotoBa/CaiLama-LLM-Router`

Koordinationspunkte:

- Streaming-Fehlerbehandlung fuer `stream: true` ist als finaler SSE-Fehler
  getestet.
- Config-Hot-Reload ist optional ueber `runtime.reload_config_on_request`.
- Backend-spezifisches Modell-Mapping per Alias ist validiert.
- `mypy src` ist bereinigt.
- Umgesetzt: Backend-API-Keys aus `api_key_env` werden an OpenAI-kompatible
  Backends weitergereicht, ohne Secrets zu loggen.
- Umgesetzt: privacy-safe Token-/Usage-Metriken, `llm-router usage`,
  Master-kompatibler Benchmark-Export und generischer `endpoint_path` fuer
  spaetere spezialisierte Backend-Endpoints.
- Offen: keine direkte Router-Folgearbeit, bis Live-Smokes, Benchmark-
  Anforderungen oder neue Backend-Profile beauftragt werden.

### Search-Quellenpolitik

Ziel-Repo: `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Crawler-Quellenpolitik, Robots-Parser und Rate-Limits testen.
- Quellen-CRUD, Robots-Validierung und Reindex-Tracking sind abgesichert.
- Synthetische Goldsets fuer Suchvertrag, DWZ-Suche und RAG-Kontext sind
  versioniert und per CLI validierbar.
- Goldset-Seeding fuer isolierte Testindizes ist ueber einen localhost-
  geschuetzten CLI-Pfad vorbereitet.
- Umgesetzt: `goldsets smoke` automatisiert lokale Test-Meilisearch-Instanz,
  synthetisches Seeding, API-Start mit deaktiviertem Scheduler und Goldset-Run.
- Umgesetzt: optionale semantische Retrieval-Schicht ist implementiert,
  bleibt default-off und faellt bei Fehlern auf lexikalische Suche zurueck.
- Umgesetzt: Recall-/MRR-/Zero-Hit-/Latenz-Metriken, Master-kompatibler
  Benchmark-Export, RAG-Provenienz und Datenvertrag fuer spaetere
  Modellanpassungen.
- Umgesetzt: Docker-faehiger Vergleich `lexical` gegen `hybrid` und
  Ergebnisbericht fuer den Master; filter+hybrid-500er und Multi-Index-
  Normalisierung sind behoben, beide Modi erreichen Pass-Rate 1.0.
- Umgesetzt: aktueller SVW/DSB-DWZ-v2-Liveimport mit
  `spieler.csv`/`vereine.csv`/`verbaende.csv`, Verein-/Verbandsanreicherung
  und verifizierten Namens-/Vereinssuchen (`baublies`, `torsten baublies`,
  `Ratinger Schachklub`).
- Umgesetzt: Webquellen-Bericht in `sources.yaml` uebernommen. Offene
  Kernquellen wie Wikibooks, Stockfish und Lc0 sind aktiv; rechte- oder
  UGC-sensible Kandidaten bleiben deaktiviert und tragen Review-Metadaten.
- Naechster Search-Fokus: Entscheidung, ob Hybrid auf groesserem Eval
  produktiv freigegeben wird, plus Source-Policy-Gates fuer ungeklaerte
  Volltext-/Trainingsnutzung.

## Danach

### PTG Phase 2 und Folgehaertung

Ziel-Repo: `TotoBa/CaiLama`

Koordinationspunkte:

- Importierte Partien in Analyse-/Feature-Signale ueberfuehren.
- Schwaechenprofil und Kartenqueue nachvollziehbar ableiten.
- Umgesetzt: optionale Queue-Einspeisung ueber `ptg-games --queue-dir`.
- Umgesetzt: offline/deterministische PTG-Artefakt-Scheibe mit
  `source.pgn`, `annotated.pgn`, drei bis sieben Schluesselstellungen,
  Trainingsfragen, `training.json`, `quality_gates.json` und CLI-Ausgabe.
- Umgesetzt: Agent-/DGT-naher Kartenabruf, `ReplayPosition.card_id` und
  Review-Stats in der Trainingspriorisierung.
- Umgesetzt: deterministisches Scoring, Fehler-/Mustertaxonomie, Kartentypen
  und PTG-Basis-Qualitaetsgates.
- Umgesetzt: gewichtete Trainingspositionen und Coach-Session on demand mit
  Unicode-Brett und optionaler DGT-Aufstellaufforderung.
- Umgesetzt: PGN-/LLM-Pipeline mit Retry, Timeout, Checkpointing und
  Resume-Semantik. Die 21 Positionen aus der Drei-Spiele-Benchmark bleiben
  Beobachtung, kein Produktdefault.
- Umgesetzt: Review-Gate-Grundlage nach erster Stockfish-Analyse, Planmodus,
  Hintergrund-Agenten fuer lange Aufgaben und secretfreie
  Modellrollen-Benchmark-Events.
- Umgesetzt: Review-Gate-Console-Flow mit Unicode-Brett pro Kandidat und
  finalem Submit vor LLM-Tiefenanalyse.
- Umgesetzt: Plan-Kaskade in `AgentLoop`, sodass bestaetigte Plaene den
  naechsten Schritt deterministisch ansteuern.
- Umgesetzt: BoardTruth- und Stockfish-Legal-Move-Ausgabe mit Mehrfach-Tags
  und Produkt-Qualitaetsbaendern.
- Umgesetzt: PTG-Live-Verifikation gegen Router, Legal-Move-Details in
  Review-/Coach-/Benchmark-Artefakten, RAG-Provenienz, OCR/FEN-Gates,
  Analyse-/Training-Qualitaetsgates ueber PTG hinaus sowie Profil-Export und
  bestaetigte Profil-Loeschung.
- Offen: Retention/Profilbindung fuer dateibasierte Trainingskarten und
  Review-Historien sowie automatische Metrik-Uebernahme in das geschuetzte
  Website-Feedback.

### DWZ-Identity-Linking

Ziel-Repos: `TotoBa/CaiLama`, `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Vorhandenen Matching-Pfad in Store und CLI integrieren.
- Plattformprofile mit DWZ-Treffern verknuepfen.
- Mehrdeutige Treffer manuell bestaetigen lassen.
- PII-Minimierung und Export-/Retention-Regeln dokumentieren.
- Profil-Export und bestaetigte Profil-Loeschung sind umgesetzt; Retention
  fuer dateibasierte Card-/Review-Stores bleibt Folgearbeit.

## Spaeter

### RAG-Analysepakete

Ziel-Repos: `TotoBa/CaiLama`, `TotoBa/CaiLama-Search`,
`TotoBa/CaiLama-LLM-Router`

Koordinationspunkte:

- `researcher`- und `analyst`-Rollen mit `/v1/context` versorgen.
- Eroeffnungsdossiers, Gegnerprofile und evidenzbasierte Berichte als
  Produktpfade schneiden.
- Quellenprovenienz und Prompt-Disziplin absichern.

### Einheitliche Job-Orchestrierung

Ziel-Repos: `TotoBa/CaiLama`, `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Import, Crawl, Game-Analyse, PTG und Reindex als zusammenhaengende
  Job-Landschaft beschreiben.
- Bestehende CaiLama-Queue/Training-Logik und Search-Scheduler abstimmen.
- Keine Scheduler-Logik zwischen Repos kopieren.

### Spezialisiertes LLM-Training

Ziel-Repos: `TotoBa/CaiLama`, `TotoBa/CaiLama-LLM-Router`,
`TotoBa/CaiLama-Master`

Koordinationspunkte:

- Erst nach Benchmark-Baseline und Datenfreigabe planen.
- Trainings-, Eval- und Testdaten strikt trennen.
- Keine privaten Partien, Kommentare oder Profile ohne explizite Freigabe.
- Spezialisiertes Modell bleibt hinter dem Router-Vertrag; keine
  Schachproduktlogik in den Router verschieben.
- Erfolg an denselben Benchmarks messen wie generische Modelle.

### Benchmark-Rahmen im Master

Ziel-Repo: `TotoBa/CaiLama-Master`

Koordinationspunkte:

- Benchmarks werden zentral im Master geplant und dokumentiert, damit
  CaiLama-, Router- und Search-Ergebnisse vergleichbar bleiben.
- Die Modellrollen-Hypothese ist als Matrix dokumentiert und wird ueber
  geschuetztes Website-Feedback messbar gemacht.
- Automatisch bewertbare Struktur-, Tool-, Fehler-, Dauer- und
  Basisqualitaets-Faelle koennen per `scripts/benchmark_feedback_agent.py`
  ueber die geschuetzte Website-API geschlossen werden; menschliches Feedback
  bleibt fuer Schachintuition und echte Analysequalitaet massgeblich.
- Pflichtmetriken fuer Modellrollen: Dauer, Input-/Thinking-/Output-Tokens,
  Qualitaetsurteil, Aufgabenloesung, Logikfehler-Klasse und A/B-Praeferenz.
- Ergebnisdateien duerfen nur synthetische, anonymisierte oder bewusst
  freigegebene Testdaten enthalten.
- Benchmark-Kommandos duerfen keine produktiven Keys, lokalen Pfade oder
  privaten Runtime-Details voraussetzen.

## Ausbau

### Observability

Ziel-Repos: `TotoBa/CaiLama`, `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Router-KPIs fuer Fallbacks, Cooldowns, Alias-Nutzung, Latenzen und
  Token-/Usage-Werte in den Benchmark-Rahmen aufnehmen.
- Search-KPIs fuer Suchqualitaet, Indexfrische und Fehlerquoten sind als
  privacy-safe Grundlage angebunden; synthetische Goldsets und Testindex-
  Seeding sind vorbereitet.
- PTG-KPIs fuer Kartenqualitaet, Review-Erfolg und Wiederholungswirkung.
- Keine Prompt-, Response- oder Secret-Inhalte loggen.

### Semantische Retrieval-Evaluation

Ziel-Repo: `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Embedding-/Reranking-Layer ist optional implementiert und default-off.
- Produktivnutzung nur nach messbarem Nutzen gegen Eval-Datensatz freigeben.
- Bestehenden Meilisearch-Lexikalindex als stabile Basis behalten.
- Fallback-Strategie, Recall, MRR, Zero-Hit-Rate, Latenz und Speicherbedarf
  gegen die vorhandene Goldset-Baseline messen.
