# Roadmap

Diese Roadmap ueberfuehrt die Punkte aus `status.plan.cailama.md` in eine
laufend pflegbare Master-Sicht. Die Umsetzung erfolgt in den jeweiligen
Unter-Repositories.

## Jetzt

### CaiLama DB-Hybridpfad

Ziel-Repo: `TotoBa/CaiLama`

Koordinationspunkte:

- Konfigurationsmodus fuer `native`, `api` und `hybrid` definieren.
- Native MariaDB/MySQL lokal fuer Aufbau und Backup nutzen.
- Fachliche Webspace-DB-API als Provider-Pfad vorbereiten.
- Keine generische SQL-over-HTTP-API einfuehren.

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

### Router-Status: keine aktive Folgearbeit

Ziel-Repo: `TotoBa/CaiLama-LLM-Router`

Koordinationspunkte:

- Aktueller Stand: keine neue Router-Aufgabe starten, solange kein neuer
  Nutzerauftrag vorliegt.
- Streaming-Fehlerbehandlung fuer `stream: true` ist als finaler SSE-Fehler
  getestet.
- Config-Hot-Reload ist optional ueber `runtime.reload_config_on_request`.
- Backend-spezifisches Modell-Mapping per Alias ist validiert.
- `mypy src` ist bereinigt.

### Search-Quellenpolitik

Ziel-Repo: `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Crawler-Quellenpolitik, Robots-Parser und Rate-Limits testen.
- Quellen-CRUD, Robots-Validierung und Reindex-Tracking sind abgesichert.
- Synthetische Goldsets fuer Suchvertrag, DWZ-Suche und RAG-Kontext sind
  versioniert und per CLI validierbar.
- Naechster Search-Fokus: Goldset-Seeding fuer isolierte Testindizes und
  einheitliche Job-Orchestrierung.
- Aktueller Ausbau-Fokus liegt auf Search, damit CaiLama mit Search/DWZ/RAG
  weiter integriert werden kann.

## Danach

### PTG Phase 2 und Folgehaertung

Ziel-Repo: `TotoBa/CaiLama`

Koordinationspunkte:

- Importierte Partien in Analyse-/Feature-Signale ueberfuehren.
- Schwaechenprofil und Kartenqueue nachvollziehbar ableiten.
- Umgesetzt: optionale Queue-Einspeisung ueber `ptg-games --queue-dir`.
- Review-Ergebnisse in Schwierigkeit, Prioritaet und Wiederholung
  zurueckfuehren.
- Datenschutz fuer personenbezogene Leistungsprofile klaeren.

### DWZ-Identity-Linking

Ziel-Repos: `TotoBa/CaiLama`, `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Vorhandenen Matching-Pfad in Store und CLI integrieren.
- Plattformprofile mit DWZ-Treffern verknuepfen.
- Mehrdeutige Treffer manuell bestaetigen lassen.
- PII-Minimierung und Export-/Retention-Regeln dokumentieren.

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

## Ausbau

### Observability

Ziel-Repos: `TotoBa/CaiLama`, `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Router-KPIs fuer Fallbacks, Cooldowns, Alias-Nutzung und Latenzen sind fuer
  den aktuellen Stand ausreichend; keine neue Router-Arbeit ohne Auftrag.
- Search-KPIs fuer Suchqualitaet, Indexfrische und Fehlerquoten sind als
  privacy-safe Grundlage angebunden; synthetische Goldsets sind vorbereitet.
- PTG-KPIs fuer Kartenqualitaet, Review-Erfolg und Wiederholungswirkung.
- Keine Prompt-, Response- oder Secret-Inhalte loggen.

### Optionale semantische Retrieval-Schicht

Ziel-Repo: `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Embedding-/Reranking-Layer nur mit Eval-Datensatz einfuehren.
- Bestehenden Meilisearch-Lexikalindex als stabile Basis behalten.
- Fallback-Strategie und Qualitaetsmetriken vor Umsetzung definieren.
