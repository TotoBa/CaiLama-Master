# Roadmap

Diese Roadmap ueberfuehrt die Punkte aus `status.plan.cailama.md` in eine
laufend pflegbare Master-Sicht. Die Umsetzung erfolgt in den jeweiligen
Unter-Repositories.

## Jetzt

### Search-Auth-Hardening

Ziel-Repo: `TotoBa/CaiLama-Search`

Koordinationspunkte:

- `MeiliKeyManager` in Runtime-Pfade bringen.
- Environment-Namen zwischen API, CLI, Scheduler, Config und `.env.example`
  vereinheitlichen.
- Bootstrap per Master-Key von Runtime-Keys trennen.
- Admin-Endpunkte schuetzen.
- Tests fuer Key-Bootstrap, Rotation, Config und Admin-Auth ergaenzen.

### Interner SearchAdapter

Ziel-Repo: `TotoBa/CaiLama`

Koordinationspunkte:

- `SearchApiClient` bzw. Adapter fuer `/v1/search`, `/v1/context` und
  `/v1/dwz/*` schneiden.
- Rueckgabeformate normalisieren.
- Modi `internal_first`, `external_fallback` und `external_only` pruefen.
- Browserbasierte Websuche nur als bewussten Fallback nutzen.

## Danach

### PTG-MVP und Folgehaertung

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
