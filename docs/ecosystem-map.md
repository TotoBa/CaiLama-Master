# Ecosystem Map

Diese Karte beschreibt die Rollen der vier Repositories im CaiLama-Oekosystem.
Sie erfindet keine Implementierungsdetails und beschreibt nur die
Koordinationssicht.

## Uebersicht

```text
                 +----------------------------+
                 | TotoBa/CaiLama-Master     |
                 | Koordination, Plaene,      |
                 | Status, Doku, Benchmarks   |
                 +-------------+--------------+
                               |
                               | dokumentiert und prueft lokal
                               v
+------------------+     nutzt HTTP/API     +----------------------------+
| TotoBa/CaiLama   +-----------------------> | TotoBa/CaiLama-LLM-Router |
| Hauptsystem      |                         | LLM-Zugriff, Aliase,      |
| Training, Profile|                         | Backends, Fallbacks       |
+---------+--------+                         +----------------------------+
          |
          | nutzt HTTP/API
          v
+--------------------------+
| TotoBa/CaiLama-Search    |
| Suche, DWZ, RAG-Kontext, |
| Meilisearch-Indizes      |
+--------------------------+
```

## Verantwortlichkeiten

### `TotoBa/CaiLama-Master`

- Haelt Ecosystem-weite Plaene, Statusdokumente und TODO-Koordination.
- Definiert Regeln fuer Agentenarbeit im Master-Repo.
- Prueft lokal, ob die drei Unter-Repos vorhanden, eigene Git-Repos und vom
  Master-Repo ignoriert sind.
- Pflegt die statische Webseite fuer `https://cailama.org/` unter `web/`.
- Liefert die Human-Doku als HTML und die LLM-/Maschinenreferenz als
  `llms.txt`, `ecosystem-reference.md` und `data/ecosystem.json` aus.
- Haelt Produktpositionierung und Master-geführte Benchmark-Regeln aktuell.
- Stellt eine kleine PHP-Webspace-Fassade fuer Status, Login-Shell und
  kontrollierte serverseitige CaiLama-Dump-Importe bereit.
- Enthaelt keine produktive Laufzeitlogik.
- Enthaelt keine Secrets.

### `TotoBa/CaiLama`

- Ist das Hauptsystem des Oekosystems.
- Verantwortet Nutzer- und Trainingsfluss, Spielerprofile, importierte Partien,
  Analyse- und Trainingsartefakte sowie Agent-/CLI-Workflows.
- Priorisiert den PGN-zu-Trainingsaufgabe-Loop: gueltige Partieartefakte,
  Schluesselstellungen, Trainingskarten, Wiederholung und Review-Rueckfluss.
- Nutzt den LLM-Router fuer Modellzugriff.
- Soll CaiLama-Search fuer kontrollierte Suche, RAG-Kontext und DWZ-Daten
  nutzen.

### `TotoBa/CaiLama-LLM-Router`

- Kapselt LLM-Provider und lokale bzw. entfernte Backends hinter einer
  OpenAI-kompatiblen API.
- Verantwortet Modell-Aliase, Routing-Policies, Fallbacks und Betriebschecks.
- Stellt spaetere spezialisierte Modelle nur als generische Backend-/Alias-
  Variante bereit; Schachproduktlogik bleibt ausserhalb des Routers.
- Enthaelt keine CaiLama-Produktlogik.

### `TotoBa/CaiLama-Search`

- Kapselt Suche, RAG-Kontext, DWZ-Daten, Crawler- und Importpfade.
- Stellt Such- und Kontext-Endpunkte fuer CaiLama bereit.
- Verwaltet Meilisearch-Indizes und zugehoerige Runtime-Konfiguration im eigenen
  Repo bzw. in der lokalen Betriebsumgebung.
- Liefert Search-/RAG-Benchmarkdaten und Quellenprovenienz als messbare
  Grundlage fuer Master-Auswertungen.

## Schnittstellenrichtung

- `CaiLama -> CaiLama-LLM-Router`: CaiLama ruft den Router fuer LLM-Zugriff auf.
- `CaiLama -> CaiLama-Search`: CaiLama ruft Search fuer Suche, Kontext und DWZ
  auf.
- `CaiLama -> Webspace-DB-API`: CaiLama kann Status pruefen, grosse
  Provider-DB-Importe ueber serverseitig hochgeladene Dump-Dateien anstossen
  und Provider-Schemas ueber geschuetzte PHP-Admin-Aktionen setzen.
- `CaiLama-Master -> alle Repos`: nur Dokumentation, Koordination und lokale
  Statuspruefung.

Router und Search bleiben unabhaengig deploybar. Das Master-Repo koppelt keine
Runtime-Komponenten direkt.
