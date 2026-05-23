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
  Offen bleiben PTG-Live-Verifikation, OCR/FEN-Gates und erweiterte
  Qualitaetsgates.
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
- [x] Runtime-Aktualisierung nach groesseren Unterrepo-Releases pruefen:
  `scripts/update-runtime-projects.sh` fuer Router/Search/CaiLama nutzen und
  dokumentieren, ob Dienste aus Runtime-Ordnern gestartet wurden.
  Stand 2026-05-23: `scripts/update-runtime-projects.sh --install --restart all`
  erfolgreich; Runtime-Ordner enthalten keine `.git`-Verzeichnisse,
  `llm-router.service` und `cailama-search.service` sind aktiv, CaiLama-
  Runtime-Smoke erzeugt PTG-Artefakte.
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
  `docs/benchmark-results/2026-05-23.ptg-offline-baseline.md`. Offen bleibt
  die wiederholbare Orchestrierung ueber alle Repos, inklusive Router-Latenz,
  OCR/FEN-False-Positive-Gates und groesserem Search-/PTG-Eval.
- [ ] Spaeteres spezialisiertes LLM-Training als Roadmap-Hebel vorbereiten:
  erst nach Benchmark-Baseline, Datenfreigabe, sauberer Test-/Eval-/Train-
  Trennung und Datenschutzklaerung planen. Modelle werden nur ueber den
  Router-Vertrag bereitgestellt; Schachproduktlogik bleibt in CaiLama.
- [ ] Roadmap regelmaessig aus den Unterrepo-`TODO.md`-Dateien abgleichen:
  **CaiLama** = Gewichtete Trainingspositionen (`WeightedTrainingPosition`,
  `position_pool.py`, `refresh_pool_weights()`) und Coach-Session on demand
  (`CoachSession` mit State-Machine, `start_coach_session()`, `/coach`
  Slash-Command, Agent-Tool `start_coach_session`) sind umgesetzt. Unicode-
  Brett immer, DGT optional. Naechster Fokus: Planmodus in der interaktiven
  Console, PGN-/PTG-LLM-Optimierung (alle Zuege klassifizieren, priorisierte
  Schluesselstellungen tief analysieren, Retry/Timeout/Checkpointing;
  21 war nur die Anzahl in der aktuellen Drei-Spiele-Benchmark-Baseline,
  kein globaler Default), Nutzer-Review-Gate nach erster Stockfish-Analyse
  fuer die manuelle Schluesselstellungs-Auswahl, Hintergrund-Agenten fuer
  lange Analyse-/Import-/Trainingprofil-Aufgaben, CardScorer-Einbindung
  in weitere Trainingsauswahl, Analyse-Qualitaetsgates ueber PTG hinaus,
  Datenschutz/Export, RAG-Provenienz, PTG-Observability, OCR/FEN aktiv
  ohne geratene FENs.
  **Router** = aktuelle Infrastrukturwelle ist abgearbeitet: Backend-API-Key-
  Weitergabe, Token-/Usage-Metriken, `llm-router usage`, benchmarkbare
  Usage-/Latenzexporte und generische `endpoint_path`-Backends sind umgesetzt;
  lokale Kimi-Arbeit ist wieder auf `kimi-k2.6:cloud` konfiguriert,
  neue Router-Arbeit
  erst bei Live-Smoke-, Benchmark- oder Backend-Auftrag.
  **Search** = filter+hybrid-500er und Multi-Index-Response sind
  behoben (Pass-Rate 9/9). `semantic.enabled=false` bleibt Default.
  Offen bleiben DWZ-Staging-Verifikation, semantische Freigabeentscheidung
  auf groesserem Eval und API-/README-Pflege bei neuen Vertragsaenderungen.

## Kimi-Handoff

Der Master bleibt Koordination, Website und Doku. Keine Unterrepo-Dateien im
Master tracken, keine Submodules, keine produktive Runtime-Logik.

```text
Du arbeitest im CaiLama-Master-Repository. Die lokale Kimi-Konfiguration nutzt
fuer die kommende Arbeit `kimi-k2.6:cloud`; das ist ein Client-Default, kein
produktiver CaiLama-Modellvertrag. Lies zuerst AGENTS.md, README.md und
TODO.md vollstaendig. Lies danach in allen Unterrepos ebenfalls AGENTS.md,
TODO.md und vorhandene HANDOFF-/Plan-Dateien, damit du den Gesamtstand
kennst. Nutze den CaiLama-Ecosystem-Skill als zusaetzliche Orientierung und
lies die letzten Ergebnisse des Analyse-Workflows, insbesondere die aktuellen
Benchmark- und PTG-Artefakte. Lies danach docs/ecosystem-map.md,
docs/orchestration.md, docs/product-positioning.md, docs/benchmarks.md,
status.plan.cailama.md und master-repo-orchestration.plan.md. Wenn die Aufgabe
Website oder LLM-Doku betrifft, lies zusaetzlich docs/website.md,
docs/ecosystem-reference.md, docs/data/ecosystem.json und die betroffenen
Dateien unter web/. Beachte: web/index.php ist die Trainingsfokus-Startseite;
der bisherige Status der Startseite liegt unter web/status.php.

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
Training-/PTG-Punkten beginnen. PTG soll gewichtete Trainingspositionen
persistieren, keine automatisch offen bleibenden Sessions erzeugen. Der Coach
waehlt in der interaktiven Console eine passende Position, zeigt immer ein
Unicode-Brett, fordert bei verbundenem DGT-Brett zum Aufstellen auf und beendet
die Session eindeutig mit Folgeanalyse oder Abbruch. Danach PGN-/LLM-Pipeline
haerten: alle Zuege klassifizieren, nach der ersten Stockfish-Analyse ein
Nutzer-Review-Gate fuer die Schluesselstellungs-Auswahl anbieten, vom Nutzer
ergaenzte oder hochgestufte Stellungen in die Tiefenanalyse uebernehmen, die
pro Lauf priorisierten Schluesselstellungen tief analysieren und keine feste
21er-Grenze als Produktdefault setzen. Retry/Timeout/Checkpointing ergaenzen.
Planmodus vor Ausfuehrung nutzen: Agent erstellt zuerst Plan/TODO, Nutzer kann
pruefen und anpassen, erst danach wird gearbeitet. Hintergrund-Agenten sollen
lange Aufgaben wie Import, Analyse, OCR/FEN-Pruefung und
Trainingsprofil-Aktualisierung verarbeiten; bestehende Trainingspositionen
bleiben nutzbar, waehrend neue Analysen laufen. Beispiel: Partie zu einem
bestehenden Trainingsprofil hinzufuegen, mit vorhandenen Positionen sofort
trainieren, nach Analyseabschluss auf Session-Abschluss oder Abbruch warten und
danach Ergebnis, Einordnung und Integrationsvorschlag in der Console anzeigen.

**Update 2026-05-23:** PGN-/LLM-Pipeline ist gehärtet: `run_llm_call` mit
Retry/Backoff/Timeout, `checkpoint_writer` in `classify_moves` und
`analyze_moves`, inkrementelles JSONL-Appending, `run_pipeline_trifecta` mit
Resume-Semantik (überspringt vorhandene Plies). Neue Doku:
`CaiLama/docs/pipeline-resilienz.md`.
Search danach nur fuer DWZ-Staging und semantische Freigabeentscheidung; Router
nur bei neuem Alias-/Benchmark-Auftrag.

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
