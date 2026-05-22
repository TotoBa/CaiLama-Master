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
- Browserbasierter Webpfad nur als expliziter Fallback.
- Quellenprovenienz bei RAG-Antworten sichtbar halten.
- DWZ-Identity-Linking mit Ambiguitaetsbehandlung und PII-Minimierung.

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
  bereitgestellt: `POST /api/v1/admin/schema/auth`,
  `POST /api/v1/admin/schema/cailama` und
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
- Website-Login nutzt eine getrennte Auth-Datenbank; CaiLama-Fachdaten bleiben
  in einer separaten Datenbank und werden ueber eigene DSN-Konfiguration
  angebunden.
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
  databases.auth: error (Unknown server host)

POST /api/v1/admin/schema/cailama
  HTTP 200, Schema erfolgreich angewendet

POST /api/v1/admin/schema/auth
  HTTP 500, blockiert weil Auth-DB-Host vom Webspace nicht aufloesbar
```

**CaiLama-Datenbank** ist erreicht und Schema ist idempotent gesetzt.
**Auth-Datenbank** meldet DNS-Fehler `Unknown server host` fuer den
konfigurierten Hostnamen. Ursache: vermutlich falscher IONOS-Auth-DB-Hostname
oder DNS-Resolver-Problem auf dem Webspace. Die Konfiguration selbst (DSN,
User, Passwort) ist korrekt; die PDO-Verbindung scheitert bereits an der
Hostname-Aufloesung.

```text
POST /api/v1/status (Admin-Key)
  HTTP 200
  databases.cailama: ok
  databases.auth: error (auth_failed)

POST /api/v1/admin/schema/cailama
  HTTP 200, Schema erfolgreich angewendet

POST /api/v1/admin/schema/auth
  HTTP 500, blockiert weil auth DB auth_failed meldet
```

**CaiLama-Datenbank** ist erreicht und Schema ist gesetzt.
**Auth-Datenbank** meldet MySQL 1045 (Access denied) trotz
privater Konfig ausserhalb des Public-Webroots. Ursache wird
geprueft (Provider-seitige Passwort-Ueberpruefung).

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
| `POST /api/v1/admin/schema/auth` | Ohne `admin`-Scope | HTTP 401, `unauthorized` |
| `POST /api/v1/admin/schema/cailama` | Ohne `admin`-Scope | HTTP 401, `unauthorized` |

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
- Pruefstatus.

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
