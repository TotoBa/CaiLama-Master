# TODO - CaiLama-Master

Dieses TODO koordiniert Ecosystem-weite Aufgaben. Es ersetzt nicht die TODOs der
einzelnen Repositories und verlangt keine direkten Code-Aenderungen in den
Unter-Repos.

Erledigte TODOs werden nur auf ausdrueckliche Nutzeranweisung entfernt; TODO
ist nicht gleich Handoff. Diese Bereinigung wurde am 2026-05-20 auf
ausdrueckliche Nutzeranweisung durchgefuehrt.

## Arbeitskontext

Vor Arbeitsbeginn lesen:

- `AGENTS.md`, `README.md`, diese `TODO.md`.
- `docs/ecosystem-map.md`, `docs/orchestration.md`,
  `docs/product-positioning.md`, `docs/benchmarks.md`,
  `status.plan.cailama.md`, `master-repo-orchestration.plan.md`.
- Bei Website-Aenderungen zusaetzlich `docs/website.md`,
  `docs/ecosystem-reference.md`, `docs/data/ecosystem.json` und `web/`.

## Naechster Arbeitsschritt

- [x] Googlebot-Grundlage fuer die Website bereitstellen: `robots.txt`,
  `sitemap.xml`, kanonische URLs, `noindex` fuer Login-/Konto-Seiten,
  Deployment- und Check-Dokumentation. Indexierungsanstoß erfolgt ueber
  Sitemap-Verweis in `robots.txt`; Search-Console-Einreichung bleibt ein
  manueller Schritt mit verifizierter Property.
- [x] CaiLama-DB-Hybrid koordinieren: native MariaDB/MySQL, fachliche Webspace-
  API und Hybridbetrieb in Master-Doku/Website nachziehen, sobald die
  Umsetzung in `TotoBa/CaiLama` beginnt. Master-Seite: Login-/Session-Shell,
  Single-Database-Providerbetrieb mit `web_users` und CaiLama-Fachtabellen in
  derselben `databases.cailama`-PDO-Verbindung sowie Schema-Vorlage sind
  vorbereitet; echte Provider-Credentials bleiben in ignorierter
  privater Webspace-Konfiguration ausserhalb von `/public`. `TotoBa/CaiLama`
  konfiguriert jetzt
  `database.access_mode = native|api|hybrid`, API-Metadaten ohne Secrets und
  einen begrenzten DB-API-Statusclient per geschuetztem `POST /api/v1/status`.
  Die Webspace-API stellt jetzt no-query/no-body-Import-Endpunkte fuer
  serverseitig hochgeladene `.sql`-/`.sql.gz`-Dumps bereit; fehlende
  Importdateien werden abgelehnt und erfolgreich verarbeitete Dateien
  geloescht. Provider-Schemas werden ueber admin-geschuetzte PHP-Endpunkte im
  Webspace gesetzt, weil die Provider-DB nur von dort bearbeitet werden
  sollen. Private Webspace-Konfig und API-Keys liegen ausserhalb des
  Public-Webroots; lokale DB-Schemas sind angelegt.
- [x] Webspace-DB-API live-testfaehig fertigstellen:
  **Live-Stand 2026-05-22:** Es gibt nur noch eine Provider-Datenbank.
  Website-Login (`web_users`) und CaiLama-Fachdaten leben gemeinsam in
  `databases.cailama` (Single-Database-Mode), weil der Webspace keine stabile
  zweite Provider-DB-Verbindung bereitstellt. `POST /api/v1/status` liefert mit
  gueltigem Bearer-Key HTTP 200 und `databases.cailama: ok`.
  `POST /api/v1/admin/schema/cailama` und
  `POST /api/v1/admin/schema/all` wenden dasselbe Single-DB-Schema erfolgreich
  an. Der alte separate Auth-Schema-Pfad ist entfallen.
  no-key/body/file-Import-Smokes sind in `docs/integrations.md` dokumentiert.
  Folgearbeit: minimale fachliche CaiLama-Read-/Write-Endpunkte.
- [x] Produktpositionierung als Master-Pruefpunkt dauerhaft pflegen:
  CaiLama bleibt Trainingswerkstatt fuer ernsthafte Verbesserung. Der
  dokumentierte Kernloop ist PGN-Import, Stockfish-/Heuristik-Grounding,
  drei bis sieben Schluesselstellungen, Trainingsfragen/-karten, gueltige
  PGN- und Trainings-JSON-Artefakte sowie Review-Rueckfluss. Social-, Feed-,
  Matchmaking- und Mobile-First-Funktionen bleiben Nicht-Ziele, solange dieser
  Produktloop nicht stabil und benchmarkbar ist.
  Stand 2026-05-23 (Update): PTG-Scoring, Fehler-/Mustertaxonomie und
  Kartentypen sind umgesetzt und in Agent-Tools, Board-Chain und Drift-Cards
  integriert. `privacy-training-data.md` ist als Datenschutz-Konzept angelegt.
  Update 2026-05-24: PTG-Live-Verifikation gegen Router, Legal-Move-Details
  in Review-/Coach-/Benchmark-Artefakten, RAG-Provenienz, OCR/FEN-Gates,
  Analyse-/Training-Qualitaetsgates sowie Profil-Export und bestaetigte
  Profil-Loeschung sind in CaiLama umgesetzt.
- [x] Website-Struktur auf Trainingsfokus als Startseite umstellen:
  `web/index.php` ist die Trainingsfokus-/Trainingswerkstatt-Seite,
  die bisherige Status-Startseite liegt als `web/status.php`. Navigation,
  Sitemap, `llms.txt`, `docs/website.md`, `docs/data/ecosystem.json` und
  `scripts/check-ecosystem.sh` sind auf `status.php` statt eine separate
  Trainingsfokus-URL ausgerichtet.
- [x] Website-Wartbarkeit auf privaten Smarty-App-Bereich umstellen:
  Öffentliche Seiten unter `web/` sind dünne Controller; Header, Navigation,
  Footer und Content-Blöcke liegen unter `web-smarty/`. `web/` wird nach
  `<webspace-root>/public/` deployt, `web-smarty/` nach
  `<webspace-root>/smarty/`. Die Smarty-Library gehört nicht ins Git; benötigt
  wird `smarty/smarty ^5.0`, lokal/deployt unter ignoriertem
  `web-smarty/vendor/`.
- [x] CaiLama-Search-Vertrag weiter pruefen: `POST /v1/search`,
  kompatibles `GET /v1/search`, `POST /v1/context`, `items`/`results`,
  `context`/`sources` und DWZ-Endpunkte **in `docs/integrations.md` als
  Response-Vertrag ergaenzt**; Website (`ecosystem-reference.md` und
  `ecosystem.json`) sind synchron.
- [x] Kimi-CLI-Ecosystem-Skill nach erstem realen Kimi-Lauf geprueft und
  geschaerft: `skills/kimi-cli-cailama-ecosystem/SKILL.md` enthaelt jetzt
  explizite Initialisierungspruefungen (`pwd`, `git rev-parse`,
  `git status --short`, `git check-ignore`) und Abschlusspruefungen
  (`git diff --check`, `bash scripts/check-ecosystem.sh`) im Working Loop.
  Versionierte Quelle und lokale Kimi-Skill-Datei sind synchron deployt.
  Keine Secrets oder Runtime-Pfade.
  Update 2026-05-23: Der Skill dokumentiert jetzt auch secretfreie Runtime-
  und Website-Deploys, typische Kimi-Fallstricke aus der Background-Agent-/
  Benchmark-/Review-Gate-Session und die Regel, lokale Operator-Konfigurationen
  nur ueber Skripte zu nutzen, aber nie offenzulegen.
- [x] Runtime-Aktualisierung nach groesseren Unterrepo-Releases pruefen:
  `scripts/update-runtime-projects.sh` fuer Router/Search/CaiLama nutzen und
  dokumentieren, ob Dienste aus Runtime-Ordnern gestartet wurden.
  Stand 2026-05-23: `scripts/update-runtime-projects.sh --install --restart all`
  erfolgreich; Runtime-Ordner enthalten keine `.git`-Verzeichnisse,
  `llm-router.service` und `cailama-search.service` sind aktiv, CaiLama-
  Runtime-Smoke erzeugt PTG-Artefakte.
  Update 2026-05-23: `scripts/update-runtime-projects.sh` ermittelt den Master-
  Root aus dem eigenen Skriptpfad und funktioniert dadurch auch aus
  Unterrepos heraus. `--install` installiert jetzt Test-/Dev-Extras
  (`CaiLama .[test]`, Router `.[dev]`, Search `.[api,dev]`), damit Runtime-
  Smokes ohne manuelles `pytest`-Nachinstallieren laufen.
  Update 2026-05-24: Runtime wurde mit
  `scripts/update-runtime-projects.sh --install --restart all` aus den
  lokalen Source-Repos aktualisiert; Router `/health` und Search `/healthz`
  antworteten lokal und beide User-Services waren aktiv.
- [ ] Benchmark-Rahmen im Master vorbereiten: gemeinsame Benchmark-
  Orchestrierung fuer CaiLama, Router und Search definieren, Ergebnisablage im
  Master unter `docs/benchmark-results/` oder einer klar benannten
  Benchmark-Datei planen, reproduzierbare Kommandos ohne Secrets/lokale Pfade
  festlegen und Datenschutzregeln fuer synthetische bzw. anonymisierte
  Testdaten aufnehmen.
  Messfamilien: Search/RAG, Router, Analyse/PTG, OCR/FEN. OCR ist aktiver
  Messbereich, nicht zurueckgestellt. PTG liefert jetzt pro Session
  `quality_gates.json` und einen PTG-Benchmark-Summary-Export als erste
  Master-kompatible Messquelle; Router und Search liefern ebenfalls
  Benchmark-Export-Pfade. Erste Master-Berichte liegen vor:
  `docs/benchmark-results/2026-05-23.search-lexical-hybrid.md` und
  `docs/benchmark-results/2026-05-23.ptg-offline-baseline.md`.
  **Update 2026-05-23:** Schritt 1 des Rahmens erledigt: `scripts/run_benchmark_smoke.py`
  fuehrt offline, secretfreie Import-Smokes in allen drei Repos durch:
  PTG-Benchmark-Summary (CaiLama), Benchmark-Events-Summary (CaiLama),
  Goldset-Validierung (Search) und RequestMetrics-Roundtrip (Router).
  Das Skript wird von `scripts/check-ecosystem.sh` automatisch ausgefuehrt.
  Offen bleiben: wiederholbare Orchestrierung ueber alle Repos,
  Router-Latenz-Livesmoke und groesseres Search-/PTG-Eval.
  OCR/FEN-False-Positive-Gate ist in CaiLama umgesetzt
  (check_fen_false_positive, 6 Gates, Commit 8cd3ccf).
  OCR-Live-Benchmark erzeugt: 6 PDFs, 23 Diagramme, 0% FEN-FP-Rate,
  alle Gates passed. Artefakt: 2026-05-23.ocr-live-baseline.md.
  **Update 2026-05-23:** Die Modellrollen-Hypothese ist als Benchmark-
  Matrix dokumentiert (`docs/benchmark-results/model-role-matrix.current.md`).
  Die Website besitzt die geschuetzte Seite
  `/benchmark-feedback.php`; Feedback wird hinter Login in
  `cailama_model_benchmark_cases` und `cailama_model_feedback` gespeichert.
  Erfasst werden Laufzeit, Input-/Thinking-/Output-Tokens, Qualitaet,
  Aufgabenloesung, Logikfehler, A/B-Praeferenz und Freitext. Offen bleibt die
  automatische Uebernahme von Router-/CaiLama-Metriken in diese Tabelle.
  **Update 2026-05-24:** CaiLama liefert jetzt PTG-Live-Verifikation,
  Legal-Move-/Brettwahrheit-Artefakte, OCR/FEN-Validitaetsgates sowie
  Analyse-/Training-Qualitaetsgates. CaiLama-Search liefert privacy-safe
  RAG-/Researcher-`source_quality`-Kennzahlen fuer Benchmarkberichte. Offen
  bleibt die wiederholbare Cross-Repo-Orchestrierung und die automatische
  Uebernahme dieser Metriken in das Website-Feedback.
  **Update 2026-05-24:** Die Website-API besitzt jetzt
  `POST /api/v1/benchmarks/observations` fuer secretfreie Modelllaufdaten.
  `benchmark-feedback.php` zeigt importierte Beobachtungen an und fuellt das
  Feedbackformular pro Lauf vor. CaiLama kann den Drei-Spiele-PTG-
  Modellbenchmark per `scripts/run_ptg_model_benchmark.py` ausfuehren und die
  Beobachtungen hochladen. Der Lauf erzeugt pro Modell einen Gesamtfall und
  rollenbezogene Faelle fuer alle verfuegbaren CaiLama-Rollen (`router`,
  `small`, `large`, `task`, `translator`, `coach`, `analyst`, `critic`,
  `vision`, `scribe`, `researcher`), damit das beste Modell pro Aufgabe statt
  nur ein globaler Sieger bestimmt werden kann. Fuer Feedback-Laeufe wird der
  Upload mit `--require-upload` verbindlich gemacht. Das Feedback ist blind:
  importierte Laeufe zeigen nur Kandidaten-Codes, keine Modellnamen; die
  Zuordnung bleibt serverseitig. `--skip-ptg` erlaubt schnelle Rollen-
  Feedbacklaeufe ohne den teuren PTG-Teil. `--role-max-tokens` begrenzt nur
  die kurzen Rollen-Probes, damit Benchmarklaufzeiten vergleichbarer bleiben
  und langsame Provider nicht den gesamten Upload blockieren. Lokale CaiLama-
  Artefakte erfassen zusaetzlich Router-Backend, Provider-Modell und Fallback,
  falls der Router diese Header liefert; die Website-Bewertung bleibt blind.
  **Update 2026-05-24:** Der volle Drei-Spiele-Modellbenchmark kann nun
  bewusst ohne clientseitige Benchmark-Kappung laufen:
  `--llm-timeout-seconds 0`, `--upload-timeout-seconds 0`,
  `--role-max-tokens 0` und `--max-analysis-positions 0`. Die Modellliste
  fuer den naechsten vollstaendigen Feedbacklauf wird per `--models auto` aus
  dem Router gelesen; operative Aliase wie `default`, `kimi-cli-default` und
  `chess-*` bleiben Router-Aliase und werden in CaiLama nicht als
  Benchmarkkandidaten genutzt. `hemanth/chessplayer:latest` ist nach erneutem
  fehlerhaftem Pull vorerst ausgenommen, weil der Blob Host-Ollama crashen
  liess. Der Router besitzt eine Dual-Ollama-VM-Beispielkonfiguration,
  damit die Laufzeitmessung nicht mehr vom Pi-Backend ausgebremst wird.
  Cloud-Modelle gehen ueber die zwei Docker-Ollamas mit separaten Keys;
  lokale Modelle gehen ueber den Host-Ollama auf `127.0.0.1:11434`, damit sie
  nur einmal geladen werden muessen.
  **Update 2026-05-24:** Die Feedback-Website nimmt optionale Aufgaben-,
  Stellungs- und Fehlerfelder an. Bei FEN rendert sie ein responsives Brett;
  `/benchmark-feedback-results.php` zeigt geschuetzte, weiterhin blinde
  Aggregationen pro Lauf/Rolle/Fall/Kandidat. Thinking-Aliase werden im Router
  fuer alle nachweislich thinking-faehigen Modelle vorbereitet; abgelehnte
  Modi bleiben Feedbackfaelle statt Laufabbruch.
  Das Website-Deploy-Skript laedt im Standardmodus nur Code hoch; Remote-
  Ordneranlage und `web-smarty/vendor/`-Upload erfolgen nur mit explizitem
  Flag (`--create-dirs`, `--with-vendor`, `--full`).
  Offen bleibt die fachliche
  Bewertung der heutigen Laeufe und die Ableitung belastbarer Modell-/Prompt-
  Regeln.
- [ ] Spaeteres spezialisiertes LLM-Training als Roadmap-Hebel vorbereiten:
  erst nach Benchmark-Baseline, Datenfreigabe, sauberer Test-/Eval-/Train-
  Trennung und Datenschutzklaerung planen. Modelle werden nur ueber den
  Router-Vertrag bereitgestellt; Schachproduktlogik bleibt in CaiLama.
- [ ] Roadmap regelmaessig aus den Unterrepo-`TODO.md`-Dateien abgleichen:
  **CaiLama** = Gewichtete Trainingspositionen (`WeightedTrainingPosition`,
  `position_pool.py`, `refresh_pool_weights()`) und Coach-Session on demand
  (`CoachSession` mit State-Machine, `start_coach_session()`, `/coach`
  Slash-Command, Agent-Tool `start_coach_session`) sind umgesetzt. Unicode-
  Brett immer, DGT optional. PGN-/PTG-LLM-Resilienz mit Retry, Timeout und
  Checkpointing ist umgesetzt; die 21 Positionen bleiben nur Benchmark-
  Beobachtung aus drei Beispielspielen, kein globaler Default. Nutzer-
  Review-Gate, Planmodus, Hintergrund-Agenten und secretfreie
  Modellrollen-Benchmark-Events sind als erste Version vorhanden. Legal-Move-
  und Brettwahrheit ist erweitert: `BoardTruth` liefert Tags fuer alle
  legalen Zuege; `evaluate_legal_moves` gibt strukturierte Stockfish-Details
  mit Score, Engine-Rang, Tags und Qualitaetsband zurueck.
  CaiLama-Update 2026-05-24: PTG-Live-Verifikation, Legal-Move-/Brettwahrheit-
  Durchreichung in Review-/Coach-/Benchmark-Artefakte, RAG-Provenienz,
  OCR/FEN-Gates, Analyse-/Training-Qualitaetsgates und Profil-Export/
  bestaetigte Profil-Loeschung sind umgesetzt. Offen bleiben Retention/
  Profilbindung fuer dateibasierte Trainingskarten und Review-Historien sowie
  die fachliche Bewertung der importierten Website-Benchmarklaeufe.
  **Router** = aktuelle Infrastrukturwelle ist abgearbeitet: Backend-API-Key-
  Weitergabe, Token-/Usage-Metriken, `llm-router usage`, benchmarkbare
  Usage-/Latenzexporte und generische `endpoint_path`-Backends sind umgesetzt;
  lokale Kimi-Arbeit ist wieder auf `kimi-k2.6:cloud` konfiguriert,
  neue Router-Arbeit
  erst bei Live-Smoke-, Benchmark- oder Backend-Auftrag.
  **Search** = filter+hybrid-500er und Multi-Index-Response sind
  behoben (Pass-Rate 9/9). `semantic.enabled=false` bleibt Default.
  DWZ-Staging-Verifikation ist in `CaiLama-Search` umgesetzt (dwz_staging.py
  + tests/test_dwz_staging.py, 4 Tests passing). `uv.lock` ist wie im Router
  ignoriert, damit `uv run` keinen dreckigen Arbeitsbaum hinterlaesst.
  Search-Update 2026-05-24: RAG-/Researcher-`source_quality`-Kennzahlen fuer
  das Website-Feedback sind im Goldset-/Benchmark-Vertrag umgesetzt
  (Provenienz-Abdeckung, Quellen pro Fall, Domain-Diversitaet als Count,
  Freshness-Signal-Rate und Herkunftstypen; keine URLs/Domains im Export).
  Offen bleibt die semantische Freigabeentscheidung auf groesserem Eval und
  API-/README-Pflege bei neuen Vertragsaenderungen.

## Kimi-Handoff

Der Master bleibt Koordination, Website und Doku. Keine Unterrepo-Dateien im
Master tracken, keine Submodules, keine produktive Runtime-Logik.

Update 2026-05-24: Fuer den langen Modellrollen-Benchmark sind Router-Aliase
fuer alle lokal/cloudseitig nachgewiesenen Modelle und Thinking-Varianten
vorbereitet. Der Router nutzt dafuer `request_overrides` in Modellrouten;
CaiLama liest die Kandidaten per `--models auto` aus `/v1/models`, filtert
Router-Default-/Rollen-Aliase wie `default`, `kimi-cli-default` und `chess-*`
heraus und exportiert abgelehnte Modell-/Thinking-Varianten als Feedback-
Fehlerfall. Vor dem finalen Lauf muessen alle lokalen Modelle in Host-Ollama
vorhanden sein; `hemanth/chessplayer:latest` ist wegen wiederholtem defektem
Pull/Host-Ollama-Crash vorerst nicht in der Liste. Der Startbefehl steht in
`docs/benchmarks.md`.

```text
Du arbeitest im CaiLama-Master-Repository. Die lokale Kimi-Konfiguration nutzt
fuer die kommende Arbeit `kimi-k2.6:cloud`; das ist ein Client-Default, kein
produktiver CaiLama-Modellvertrag. Lies zuerst AGENTS.md, README.md und
TODO.md vollstaendig. Lies danach in allen Unterrepos ebenfalls AGENTS.md,
TODO.md und vorhandene Plan-Dateien sowie die TODO-Handoff-Bloecke, damit du den Gesamtstand
kennst. Nutze den CaiLama-Ecosystem-Skill als zusaetzliche Orientierung und
lies die letzten Ergebnisse des Analyse-Workflows, insbesondere die aktuellen
Benchmark- und PTG-Artefakte. Lies danach docs/ecosystem-map.md,
docs/orchestration.md, docs/product-positioning.md, docs/benchmarks.md,
status.plan.cailama.md und master-repo-orchestration.plan.md. Wenn die Aufgabe
Website oder LLM-Doku betrifft, lies zusaetzlich docs/website.md,
docs/ecosystem-reference.md, docs/data/ecosystem.json und die betroffenen
Dateien unter web/. Beachte: web/index.php ist die Trainingsfokus-Startseite;
der bisherige Status der Startseite liegt unter web/status.php. Fuer
  Modellrollen-Benchmarks gibt es die geschuetzten Seiten
  web/benchmark-feedback.php und web/benchmark-feedback-results.php; keine
  Kontoanlage oder Registrierung einbauen, Nutzer werden direkt in web_users
  angelegt.

Arbeite danach die offenen Punkte in TODO.md von oben nach unten ab. Pro
Schritt nur eine kleine, nachvollziehbare Aufgabe bearbeiten: Kontext lesen,
umsetzen, gezielt pruefen, TODO/Doku aktualisieren, dann committen und pushen.
Keine Secrets, lokalen Pfade oder produktiven Zugangsdaten aufnehmen. Keine
Unterrepo-Dateien im Master committen. Erledigte TODO-Punkte nicht loeschen,
ausser der Nutzer fordert diese Bereinigung ausdruecklich an. TODO ist nicht
gleich Handoff. Dauerhafte Pruefpunkte wie Produktpositionierung kurz gegen
Doku/Web abgleichen und dann zum naechsten konkret umsetzbaren offenen Punkt
weitergehen.

Cross-Repo-Prioritaet fuer den naechsten Lauf: In CaiLama mit den offenen
Training-/PTG-Punkten beginnen. Bereits erledigt sind gewichtete
Trainingspositionen, on-demand Coach-Session, PGN-/LLM-Resilienz,
Review-Gate-Grundlage und Console-Flow, Planmodus und Plan-Kaskade,
Hintergrund-Agent, Benchmark-Event-Recorder sowie strukturierte Legal-Move-/
Brettwahrheit-Ausgabe, PTG-Live-Verifikation gegen bewusst gestarteten Router,
Legal-Move-/Brettwahrheit-Details in Review-/Coach-/Benchmark-Artefakten,
OCR/FEN-Gates ohne geratene FENs, RAG-Provenienz, Analyse-/Training-
Qualitaetsgates sowie Profil-Export und bestaetigte Profil-Loeschung. Offen
ist die Abrundung: Retention/Profilbindung fuer dateibasierte Trainingskarten
  und Review-Historien. Neu vorbereitet ist der Drei-Spiele-PTG-
  Modellbenchmark mit automatischer Router-Modellliste, Thinking-Varianten,
  Fehler-Beobachtungen, Website-Beobachtungsimport, FEN-/Brettanzeige und
  Ergebnis-Aggregation. Router-Default-/Rollen-Aliase wie `default`,
  `kimi-cli-default` und `chess-*` gehoeren nicht in die CaiLama-
  Benchmarkkandidaten, ausser sie werden explizit mit `--include-role-aliases`
  verlangt. Nach echten Laeufen sind die Feedbackfaelle auf der Website
  fachlich zu bewerten. Die 21 Positionen aus dem Benchmark sind nur
  Beobachtung aus drei Beispielpartien. Danach Search nur fuer semantische
Freigabeentscheidung auf groesserem Eval; RAG-/Researcher-Kennzahlen sind als
`source_quality` im Benchmark-Vertrag umgesetzt. Router nur bei neuem Alias-/
Benchmark-/Live-Smoke-Auftrag.
Runtime- und Website-Deploys nur ausfuehren, wenn sie beauftragt sind; dabei
den Ecosystem-Skill nutzen und keine lokalen Operator-Secrets anzeigen oder
dokumentieren.

**Update 2026-05-23:** PGN-/LLM-Pipeline ist gehärtet: `run_llm_call` mit
Retry/Backoff/Timeout, `checkpoint_writer` in `classify_moves` und
`analyze_moves`, inkrementelles JSONL-Appending, `run_pipeline_trifecta` mit
Resume-Semantik (überspringt vorhandene Plies). Neue Doku:
`CaiLama/docs/pipeline-resilienz.md`.
Search danach nur fuer DWZ-Staging und semantische Freigabeentscheidung; Router
nur bei neuem Alias-/Benchmark-Auftrag. Die Modellrollen-Hypothese aus
docs/benchmark-results/model-role-matrix.current.md soll durch Messdaten
validiert werden: Dauer, Input-/Thinking-/Output-Tokens, Qualitaet,
Aufgabenloesung, Logikfehler und A/B-Feedback, ohne Rohprompts, volle private
Partien oder Secrets zu speichern.

Nach jeder Aenderung:
1. Betroffene Master-Doku oder Website knapp aktualisieren.
2. git diff --check ausfuehren.
3. git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search ausfuehren.
4. bash scripts/check-ecosystem.sh ausfuehren.
5. git status --short ausfuehren.
6. Commit und Push im Master-Repository ausfuehren, bevor der naechste TODO-
   Schritt begonnen wird.
```

## Master-Arbeitsregeln

- [ ] Vor Arbeitsbeginn die Dateien aus "Arbeitskontext" lesen.
- [ ] Unter-Repos bleiben eigenstaendige Git-Repositories und im Master
  ignoriert.
- [ ] Keine Prompt- oder Handoff-Dateien ausserhalb von `TODO.md` anlegen;
  groessere Konzepte duerfen als klar benannte `*.plan.md` abgelegt werden.
- [ ] Keine Secrets, Tokens, `.env`, lokalen Service-Dateien oder produktiven
  Credentials in Doku, Website, Skripte oder Beispiele schreiben.
- [ ] Abschlusspruefung ausfuehren:
  `git status --short`,
  `git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search`,
  `bash scripts/check-ecosystem.sh`,
  `git diff --check`.
