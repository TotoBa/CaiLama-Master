# CaiLama Ecosystem Reference

Zielgruppe: Menschen, Codex, Kimi und andere LLM-Agenten, die schnell den
aktuellen Systemzuschnitt verstehen muessen.

Stand: 2026-05-19.

## Kurzfassung

CaiLama ist ein Schachanalyse-, Trainings- und Automatisierungs-Oekosystem.
Es besteht aus vier Repositories mit klarer Trennung:

- `TotoBa/CaiLama-Master`: Gesamt-Doku, Webseite, Roadmap, Status,
  Orchestrierung und lokale Checks.
- `TotoBa/CaiLama`: Hauptsystem fuer Schachanalyse, Training, Profile,
  Agent-CLI, PGN/Stockfish und DGT-nahe Workflows.
- `TotoBa/CaiLama-LLM-Router`: eigenstaendiger OpenAI-kompatibler Router fuer
  LLM-Backends, Modell-Aliase, Policies und Fallbacks.
- `TotoBa/CaiLama-Search`: eigenstaendiger Such-, DWZ- und RAG-Dienst mit
  FastAPI, Meilisearch, Crawlern und Importpfaden.

Der Master ist kein Monorepo und kein Runtime-Repo. Die drei Unter-Repos sind
lokal vorhanden, aber im Master-Git ignoriert.

## Projektidentitaet

Aktuelle Namen:

- `CaiLama-Master`: Koordination der Projekte und Webseite.
- `CaiLama`: Hauptprojekt, historisch aus DGT-Chesstrainer entstanden.
- `CaiLama-LLM-Router`: generischer lokaler/cloudfaehiger LLM-Router.
- `CaiLama-Search`: schachspezifisches Such-/Indexsystem fuer Webseiten,
  DWZ-/Spielerdaten und spaeter RAG-Kontext.

Alte Namen duerfen nur als historische Referenz verwendet werden.

## Zielbild

CaiLama soll ein praxistaugliches Schachtrainingssystem werden, das folgende
Aufgaben ausfuehrt:

- PGNs importieren.
- Hauptvarianten extrahieren.
- Stockfish-Analysen erzeugen.
- LLM-gestuetzte menschliche Kommentare ergaenzen.
- Schluesselstellungen erkennen.
- Trainingsaufgaben ableiten.
- DGT-Board-Training steuern.
- relevante externe Informationen suchen.
- Ergebnisse nachvollziehbar speichern.

## Repository-Referenz

### TotoBa/CaiLama-Master

Rolle:

- Gesamt-Doku des Oekosystems.
- Human-Webseite unter `https://cailama.org/`.
- LLM-freundliche Referenzen unter `llms.txt`,
  `ecosystem-reference.md` und `data/ecosystem.json`.
- Roadmap und Cross-Repo-Koordination.
- Lokale Checks ohne Schreibzugriffe in Unter-Repos.

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

- Hauptsystem fuer Analyse, Training und Nutzerfluss.
- Agentische CLI, Slash-Commands, Tools und Rollen.
- PGN-I/O, Stockfish-Pipeline, statische Brettwahrheit, Datenbank,
  Spielerprofile, Plattformimporte, Knowledge/OCR, Queue, Training und
  DGT-nahe Adapter.

Wichtige Modulgruppen:

- `analysis`: Stockfish-Pipeline, Zugqualitaet, Sharpness, PGN-Annotation.
- `chess_eval`: Brettfakten ohne Engine.
- `database`: SQLObject-Store, Migrationen, MariaDB/SQLite-Testpfade.
- `player_profile`: Profile, Plattformaccounts, importierte Partien,
  Rating-Aggregation.
- `training`: Karten, Sessions, Reviews, Live-Coach, Board-aware Tool-Chain.
- `agent`: Console, Controller, ToolRegistry, ConversationContext.
- `knowledge`: lokale Wissenskarten, OCR, Quellenmodelle.
- `queue`: dateibasierte Import-/Verarbeitungspfade.

Aktueller Fokus:

- PTG-Folgehaertung: Review-Resultate in Schwierigkeit, Prioritaet und
  Wiederholung personalisierter Karten zurueckfuehren.
- Fehler-/Mustertaxonomie fuer personalisiertes Training.
- Interner SearchAdapter fuer CaiLama-Search.
- DWZ-Identity-Linking mit Ambiguitaetsbehandlung.
- RAG-gestuetzte Analysepakete fuer Researcher/Analyst-Rollen.

Grenzen:

- Keine Live-Web-, Engine-, Router-, DB- oder Hardware-Zugriffe ohne
  ausdruecklichen Auftrag.
- Keine Secrets aus lokalen Configs anzeigen.
- Neue Fachlogik zuerst als importierbares Modul, CLI/Tools nur als Adapter.

### TotoBa/CaiLama-LLM-Router

Rolle:

- OpenAI-kompatible API fuer Modellzugriff.
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

- Backend-Zustandsmodell pruefen.
- Fallback-Verhalten fuer Rate-Limits, Connection-Errors und 5xx testen.
- Exhausted-Backend-Verhalten klar dokumentieren.
- Privacy-safe Observability ohne Prompt-/Response-Logging.

Grenzen:

- Keine Schachproduktlogik im Router.
- Keine Provider-Secrets im Repo.
- Retrieval-Kontext bleibt Aufgabe von CaiLama/CaiLama-Search.

### TotoBa/CaiLama-Search

Rolle:

- Such-, DWZ- und RAG-Dienst.
- FastAPI-Zugriffsschicht.
- Meilisearch-Indizes fuer Webseiten, Chunks und DWZ-Spielerdaten.
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

- `MeiliKeyManager` in Runtime-Pfade verdrahten.
- Environment-Namen konsistent machen.
- Master-Key nur fuer Bootstrap/Admin nutzen.
- Admin-Endpunkte vor produktiver Exponierung absichern.
- Quellenprovenienz fuer RAG-Antworten.
- Download-basierter DWZ-Import statt deaktivierter tokenisierter
  Schnittstellen.
- Optionale semantische Retrieval-Schicht nur mit Eval-Datensatz.

Grenzen:

- Keine echten Meilisearch-Keys, Admin-Keys, Master-Keys oder `.env`-Dateien
  committen.
- Live-Importe, Crawler-Laeufe und Netzwerkzugriffe nur auf ausdruecklichen
  Auftrag.

## Schnittstellen

### CaiLama -> CaiLama-LLM-Router

Zweck:

- LLM-Zugriff ueber OpenAI-kompatible Endpunkte.
- Rollenbasierte Modellwahl.
- Fallbacks ohne Produktlogik in CaiLama zu duplizieren.

Vertrag:

- CaiLama kennt logische Rollen.
- Router loest Rollen/Aliase auf Provider-Modelle und Backends auf.
- Router loggt keine Prompt-/Response-Inhalte standardmaessig.

### CaiLama -> CaiLama-Search

Zweck:

- kontrollierte Suche statt fragiler Browser-Websuche.
- RAG-Kontext fuer Researcher/Analyst.
- DWZ-Daten fuer Profile und Training.

Vertrag:

- Search liefert strukturierte Such- und Kontextantworten.
- CaiLama normalisiert Resultate ueber einen SearchAdapter.
- Quellenprovenienz bleibt sichtbar.
- Browserbasierte Suche ist nur Fallback.

### Master -> Unter-Repos

Zweck:

- Dokumentieren.
- Koordinieren.
- Pruefen.

Vertrag:

- Master nimmt keine Unter-Repo-Dateien auf.
- Master schreibt nicht in Unter-Repos.
- Master enthaelt keine Secrets und keine Runtime-Logik.

## Roadmap

Jetzt:

- Search-Auth-Hardening in CaiLama-Search.
- Interner SearchAdapter in CaiLama.

Danach:

- PTG-MVP/Folgehaertung in CaiLama.
- DWZ-Identity-Linking zwischen CaiLama und CaiLama-Search.

Spaeter:

- RAG-gestuetzte Analysepakete.
- Einheitliche Job-Orchestrierung fuer Import, Crawl, Game-Analyse, PTG und
  Reindex.

Ausbau:

- Observability/KPIs fuer Router, Search und PTG.
- Optionale semantische Retrieval-Schicht in CaiLama-Search.

## Qualitaetsregeln

- Erst Stand pruefen, dann aendern.
- Keine Aussagen ueber Repo-Zustaende erfinden.
- Keine stillen Fehler oder Schein-Erfolge.
- Tests fuer Kernmodule und Smoke-Tests fuer CLI/API.
- PGN-Ausgabe validieren.
- Router-Fallbacks testen.
- Datenbankzugriffe testen.
- Doku aktuell halten.
- Keine Secrets in Doku, Code oder Beispielen.

## Maschinenlesbare Quellen

- `docs/data/ecosystem.json`: versionierte Maschinenreferenz im Master.
- `web/data/ecosystem.json`: ausgelieferte Maschinenreferenz auf der Webseite.
- `web/llms.txt`: LLM-Einstiegspunkt fuer `https://cailama.org/`.
- `web/ecosystem-reference.md`: ausgelieferte LLM-freundliche Markdown-Version.
