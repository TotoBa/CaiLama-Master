# Integrationen

Dieses Dokument sammelt die Master-Sicht auf Cross-Repo-Schnittstellen. Es
beschreibt keine Implementierung in den Unter-Repositories.

## Schnittstellen

### CaiLama -> CaiLama-LLM-Router

Ziel: CaiLama nutzt den Router fuer LLM-Zugriff ueber OpenAI-kompatible
Endpunkte.

Relevante Modellrollen:

- `router`
- `small`
- `large`
- `task`
- `coach`
- `analyst`
- `critic`
- `vision`
- `scribe`
- `researcher`

Zu klaerende bzw. laufend zu pruefende Punkte:

- Streaming-Fehlerbehandlung fuer `stream: true`-Flows nachvollziehbar machen.
- Optionales Config-Hot-Reload mit Tests klaeren.
- Backend-spezifisches Modell-Mapping per Alias absichern.
- `/health`, `/v1/models`, `/v1/chat/completions` und `/metrics` als Smoke-
  und Observability-Pfade dokumentieren.
- Keine Provider-Secrets im Master speichern.
- Spaetere spezialisierte Modelle bleiben normale Router-Backends bzw.
  Alias-Ziele. Der Router erhaelt keine Schachproduktlogik und muss gegen die
  gleichen Latenz-, Fallback- und Usage-Benchmarks messbar bleiben.

### CaiLama -> CaiLama-Search

Ziel: CaiLama nutzt CaiLama-Search fuer kontrollierte Suche, RAG-Kontext und
DWZ-Daten.

Relevante Endpunkte:

- `POST /v1/search` als kanonischer Suchvertrag; `GET /v1/search?q=...` bleibt
  fuer einfache Clients kompatibel.
- `POST /v1/context`
- `GET /v1/observability/search` als admin-geschuetzte, privacy-safe
  Suchmetriken- und Quellenfrische-Uebersicht.
- `/v1/dwz/search`
- `/v1/dwz/player/{pkz}`

#### Response-Vertrag

Fuer CaiLama-kompatible Konsumenten liefern die Endpunkte folgende
normalisierte Felder:

- `POST /v1/search` und `GET /v1/search` enthalten
  - `hits` als native Meilisearch-Treffer,
  - `items` oder `results` als normalisierte Trefferliste,
  - `query` als wiederholte Suchanfrage.
- `POST /v1/context` enthaelt
  - `context_blocks` als strukturierte RAG-Bloecke,
  - `context` oder `sources` als kompatible Verbraucherfelder,
  - `query` als wiederholte Kontextanfrage.

Zu klaerende bzw. laufend zu pruefende Punkte:

- SearchAdapter in CaiLama als Standardpfad vor browserbasierter Websuche.
- Normalisierte `items`/`results` aus `/v1/search` und `context`/`sources` aus
  `/v1/context` synchron halten.
- Externer Webpfad bevorzugt ueber lokale SearXNG-JSON-Suche
  (`search_api.searxng_url`, `CAILAMA_SEARXNG_URL` oder `SEARXNG_URL`);
  browserbasierter Webpfad nur als nachrangiger Fallback.
- `seed-knowledge` spielt neben kuratiertem Basiswissen auch den versionierten
  Lichess-ECO-Katalog A-E in `web_chunks`, damit Eröffnungsnamen und
  Zugfolgen in Benchmark und interaktiver Konsole gleich auffindbar sind.
- Quellenprovenienz bei RAG-Antworten sichtbar halten.
- Quellen tragen Rechte-/Zugriffsmarker (`source_license`, `access_mode`,
  `usage_policy`, `ugc_level`, `annotation_level`, `rights_reviewed`), damit
  CaiLama offene Volltextquellen, offizielle Referenzen und nur referenziell
  nutzbare Kandidaten unterscheiden kann.
- DWZ-Identity-Linking mit Ambiguitaetsbehandlung und PII-Minimierung.
- DWZ-Daten kommen produktnah aus dem aktuellen SVW/DSB-v2-Gesamtexport; der
  Importpfad reichert Spieler mit Verein und Verband an. Live verifiziert:
  `baublies`, `baublies, torsten`, `torsten baublies`,
  `Ratinger Schachklub`.
- Search-/RAG-Ergebnisse fuer Benchmarks so exportieren, dass Recall, MRR,
  Zero-Hit-Rate, Latenz und Quellenqualitaet im Master dokumentierbar sind,
  ohne Rohqueries, private Texte oder Credentials zu speichern.
- Source-Registry-Metadaten muessen verhindern, dass offene Quellen,
  offizielle Referenzen und rechtekritische Kandidaten in Benchmarks oder
  spaeteren Trainingsdaten vermischt werden.

### CaiLama -> Training/Benchmark-Artefakte

Ziel: Der produktnahe Trainingsloop liefert reproduzierbare Artefakte und
messbare Qualitaet, nicht nur freie Modellantworten.

Zu klaerende bzw. laufend zu pruefende Punkte:

- Importierte PGNs muessen nach Annotation validierbar bleiben.
- Schluesselstellungen, Trainingsfragen und Karten werden als strukturierte
  Trainings-JSON-Artefakte gespeichert.
- PTG schreibt `quality_gates.json` mit PGN-Roundtrip, annotiertem
  PGN-Roundtrip, legalen Zuegen, illegalen Plies und Grounding-Zaehlern.
- PTG-Ausgaben enthalten genug Metadaten fuer Kartenqualitaet, Redundanz,
  Fehlerklassifikation und Review-Erfolg.
- OCR/FEN bleibt aktiver Messbereich: FENs nur ausgeben, wenn die Erkennung
  belastbar ist; falsch-positive FENs sind harte Benchmark-Fehler.
- Ergebniszusammenfassungen fliessen in den Master-Benchmark-Rahmen zurueck,
  aber keine privaten Partiearchive, rohen LLM-Prompts oder vollstaendigen
  Kommentare.

### CaiLama -> Webspace-DB-API

Ziel: CaiLama kann zwischen nativem MariaDB/MySQL-Zugriff, fachlicher
Webspace-API und Hybridbetrieb waehlen.

Zu klaerende bzw. laufend zu pruefende Punkte:

- Konfiguration in CaiLama fuer `database.access_mode = native|api|hybrid`
  ist definiert.
- Ein begrenzter DB-API-Statusclient prueft per `POST /api/v1/status` nur mit
  Bearer-Key fachliche Statusdaten und gibt keine Credentials aus.
- Provider-seitige Import-Endpunkte sind bereitgestellt:
  `POST /api/v1/imports/cailama/append` und
  `POST /api/v1/imports/cailama/reset`.
- Provider-seitige Schema-Setup-Endpunkte sind als kurze Admin-Aktionen
  bereitgestellt: `POST /api/v1/admin/schema/cailama` und
  `POST /api/v1/admin/schema/all`.
- Append und Reset haben getrennte Scopes: `db_import:write` fuer Append,
  `db_import:reset` fuer Reset, `admin` fuer beide.
- Import- und Schema-Endpunkte erhalten keine Query-Parameter und keinen
  Request-Body; nur der Bearer-Key wird gesendet. Die zu verarbeitende `.sql`-
  oder `.sql.gz`-Datei liegt serverseitig in einem nicht oeffentlich
  erreichbaren Webspace-Ordner.
- Wenn keine konfigurierte Importdatei vorhanden ist, wird der Import
  abgelehnt. Nach erfolgreichem Import wird die Datei geloescht.
- Lokale DB als Aufbau- und Backup-Pfad erhalten.
- Provider-Datenbank nur ueber fachliche PHP-Fassade anbinden; direkte lokale
  Provider-DB-Setup-Laeufe sind nicht der Betriebsweg.
- Website-Login und CaiLama-Fachdaten nutzen dieselbe Datenbank
  (single-database mode). IONOS shared hosting kann offenbar nur einen
  DB-Host pro PHP-Prozess aufloesen, daher liegt alles in einer DB.
- Website-Login trennt die Tabellen logisch (z.B. `web_users`) von den
  Fachdaten; eine strikte Trennung auf Datenbank-Ebene ist technisch
  nicht moeglich.
- Keine SQL-over-HTTP-API und keine DB-Secrets im Master.
- Private Webspace-Konfiguration wird ausserhalb des Public-Webroots unter
  `cailama-private/api/config.local.php` abgelegt; alte Public-Konfigdateien
  werden beim Private-Deploy entfernt.

#### Smoke-Test-Matrix fuer die Webspace-DB-API

#### Live-Status (2026-05-22)

```text
POST /api/v1/status (Admin-Key)
  HTTP 200
  databases.cailama: ok

POST /api/v1/admin/schema/cailama
  HTTP 200, Schema erfolgreich angewendet
```

**Single-database mode:** Der alte separate Auth-DB-Pfad ist entfallen.
`auth-login.sql` wurde in `cailama-data.sql` ueberfuehrt; `web_users`,
`cailama_schema_meta` und zukuenftige Fachtabellen leben gemeinsam in der
Provider-Datenbank `databases.cailama`. Provider-Host, Nutzername und
Passwort stehen nur in der privaten Webspace-Konfiguration.

Die folgenden Pruefschritte gelten als Vertragsgrenze; sie erfordern
keine destruktiven Aktionen und werden mit gueltigen Bearer-Keys
durchgefuehrt:

| Endpunkt | Bedingung | Erwartetes Ergebnis |
|---|---|---|
| `POST /api/v1/status` | Ohne Bearer-Key | HTTP 401, `unauthorized` |
| `POST /api/v1/status` | Mit falscher Key | HTTP 401, `unauthorized` |
| `POST /api/v1/status` | Mit gueltigem Key, Scope `status:read` | HTTP 200, Status-JSON |
| `POST /api/v1/status` | Mit Key, aber Query-Parameter | HTTP 400, `query_not_allowed` |
| `POST /api/v1/status` | Mit Key, aber Request-Body | HTTP 400, `body_not_allowed` |
| `POST /api/v1/imports/cailama/append` | Ohne Key | HTTP 401, `unauthorized` |
| `POST /api/v1/imports/cailama/append` | Mit Key, aber Body | HTTP 400, `body_not_allowed` |
| `POST /api/v1/imports/cailama/append` | Mit Key, ohne Datei | HTTP 409, `no_import_file` |
| `POST /api/v1/imports/cailama/reset` | Ohne `db_import:reset`-Scope | HTTP 401, `unauthorized` |
| `POST /api/v1/admin/schema/cailama` | Ohne `admin`-Scope | HTTP 401, `unauthorized` |
| `POST /api/v1/admin/schema/all` | Ohne `admin`-Scope | HTTP 401, `unauthorized` |

Die Import-Endpunkte akzeptieren weder Query-Parameter noch Request-Body;
der zu verarbeitende Dump liegt serverseitig vor. Nach erfolgreichem
Import wird die Datei geloescht.

### Search-Goldsets -> isolierte Testinstanz

Ziel: CaiLama-Search kann Suchvertrag, DWZ-Suche und RAG-Kontext gegen
synthetische Daten end-to-end pruefen, ohne Produktivdaten oder Live-Crawls zu
beruehren.

Zu klaerende bzw. laufend zu pruefende Punkte:

- `goldsets smoke` startet bei bewusster Ausfuehrung eine lokale
  Docker-Meilisearch-Testinstanz auf `127.0.0.1`.
- Der Smoke seedet nur synthetische Goldset-Fixtures.
- Die Search-API startet mit deaktiviertem Scheduler auf einem lokalen Testport.
- Der ephemere Test-Key wird nicht ausgegeben und nicht versioniert.

### CaiLama-Master -> Unter-Repos

Der Master koppelt keine Runtime-Komponenten. Er dokumentiert:

- Ziel-Repo,
- betroffene Schnittstelle,
- erwartetes Ergebnis,
- Akzeptanzkriterien,
- Pruefstatus,
- Benchmark-Familie und Datenschutzgrenze, falls die Arbeit produktnahe
  Analyse-, Training-, Search- oder Router-Qualitaet beruehrt.

## Runtime-Konfiguration ohne Secrets

Der Master darf nur Variablennamen, Rollen, Endpunkte und Betriebsannahmen
dokumentieren. Echte Werte bleiben lokal.

Beispiele fuer erlaubte Angaben:

- Name einer Env-Variable.
- lokaler Standard-Port ohne Zugangsdaten.
- erwarteter Endpunktpfad.
- Beschreibung eines Service-Keys ohne Wert.

Nicht erlaubt:

- echte API-Keys,
- Tokens,
- Passwoerter,
- Zertifikate,
- produktive Host-spezifische Zugangsdaten.

## Smoke-Tests

Master-dokumentierte Smoke-Tests duerfen nur pruefen, was ohne Secrets und ohne
destruktive Aktionen moeglich ist:

- Existenz und Git-Status der Unter-Repos.
- Ignore-Status im Master.
- statische Webseite und Webspace-Datei.
- dokumentierte API-Pfade als Vertrag, nicht als Live-Aufruf mit Credentials.
