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

### Router-Folgehaertung

Ziel-Repo: `TotoBa/CaiLama-LLM-Router`

Koordinationspunkte:

- Streaming-Fehlerbehandlung fuer `stream: true` klaeren.
- Config-Hot-Reload bewerten und bei Entscheidung testen.
- Backend-spezifisches Modell-Mapping per Alias absichern.
- Bekannte `mypy`-Fehler bereinigen.

### Search-Quellenpolitik

Ziel-Repo: `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Crawler-Quellenpolitik, Robots-Parser und Rate-Limits testen.
- Quellen-CRUD und Robots-Validierung absichern.
- Goldsets fuer Suchqualitaet vorbereiten.

## Danach

### PTG Phase 2 und Folgehaertung

Ziel-Repo: `TotoBa/CaiLama`

Koordinationspunkte:

- Importierte Partien in Analyse-/Feature-Signale ueberfuehren.
- Schwaechenprofil und Kartenqueue nachvollziehbar ableiten.
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

Ziel-Repos: alle Unter-Repos

Koordinationspunkte:

- Router-KPIs fuer Fallbacks, Cooldowns, Alias-Nutzung und Latenzen.
- Search-KPIs fuer Suchqualitaet, Indexfrische, Fehlerquoten und Goldsets.
- PTG-KPIs fuer Kartenqualitaet, Review-Erfolg und Wiederholungswirkung.
- Keine Prompt-, Response- oder Secret-Inhalte loggen.

### Optionale semantische Retrieval-Schicht

Ziel-Repo: `TotoBa/CaiLama-Search`

Koordinationspunkte:

- Embedding-/Reranking-Layer nur mit Eval-Datensatz einfuehren.
- Bestehenden Meilisearch-Lexikalindex als stabile Basis behalten.
- Fallback-Strategie und Qualitaetsmetriken vor Umsetzung definieren.
