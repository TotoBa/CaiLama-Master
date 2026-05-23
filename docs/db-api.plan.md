# CaiLama Webspace DB API Plan

Die Datenbank bleibt intern beim Webhoster, und der PHP-Webspace stellt eine
kleine, streng begrenzte HTTPS-API bereit. Diese API darf kein generischer
DB-Proxy werden, sondern nur eine fachliche API mit klar erlaubten Operationen.

Die CaiLama-Projekthinweise passen dazu: Dienste sollen über klare
HTTP/API-Schnittstellen gekoppelt werden, Konfiguration und Credentials müssen
sauber vom Code getrennt bleiben, und keine Secrets dürfen in Repo, Doku oder
Beispielkonfigurationen landen.

## 1. Zielarchitektur

```text
CaiLama / VM / lokaler Client
        |
        | HTTPS + API-Key / optional Signatur
        v
cailama.org/api/...
        |
        | PHP 8.5 API-Fassade
        |
        v
interne Webhoster-DB
```

Die API läuft also dort, wo die DB erreichbar ist. Alles außerhalb sieht nur HTTPS-Endpunkte, niemals DB-Port, DB-Host, DB-User oder SQL.

PHP 8.5 ist als Basis grundsätzlich gut, aber der Patchstand muss gepflegt werden; PHP veröffentlicht regelmäßig Bugfix- und Security-Releases, und die PHP-Doku beschreibt den offiziellen Support-Zyklus mit aktiver Bug-/Security-Pflege je Release-Zweig. ([PHP][1])

---

## 2. Grundregel: Keine „SQL-over-HTTP“-API

**Nicht bauen:**

```http
POST /api/query
{ "sql": "SELECT * FROM users" }
```

Das wäre gefährlich, schwer auditierbar und bei einem API-Key-Leak praktisch Totalschaden.

**Stattdessen bauen:**

```http
POST /api/v1/status
GET  /api/v1/players/{id}
GET  /api/v1/games/{id}
POST /api/v1/games
POST /api/v1/analysis-results
GET  /api/v1/search/player?name=...
```

Jeder Endpoint macht genau eine fachliche Aufgabe. SQL ist intern fest verdrahtet und nutzt Prepared Statements. PHPs PDO-Prepared-Statements sind genau für diesen Zweck gedacht: User-Input wird über Parameter gebunden und nicht direkt in SQL eingebaut. ([PHP][2])

---

## 3. API-Key-Modell

Ich würde keine einfachen „Passwörter in config.php“ verwenden, sondern echte API-Keys mit Metadaten.

### API-Key-Aufbau

Ein Key sollte so aussehen:

```text
clm_live_8f3a...sehr_langer_random_teil...
```

Empfohlene Struktur:

```text
clm_<env>_<public_prefix>_<secret>
```

Beispiel:

```text
clm_live_K7Q9F2_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Der `public_prefix` dient dazu, den passenden Key-Datensatz schnell zu finden. Der geheime Teil wird **niemals im Klartext gespeichert**.

API-Keys sollten mit kryptographisch sicheren Zufallsbytes erzeugt werden. PHP stellt dafür `random_bytes()` bereit. ([PHP][3])

### DB-Tabelle `api_keys`

```sql
CREATE TABLE api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_prefix VARCHAR(32) NOT NULL UNIQUE,
    key_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    scopes JSON NOT NULL,
    status ENUM('active','revoked') NOT NULL DEFAULT 'active',
    rate_limit_per_minute INT NOT NULL DEFAULT 60,
    created_at DATETIME NOT NULL,
    last_used_at DATETIME NULL,
    expires_at DATETIME NULL
);
```

### Wichtige Eigenschaften

Der Client sendet:

```http
Authorization: Bearer clm_live_...
```

Die API sucht über den Prefix den passenden Datensatz und prüft den geheimen Key gegen den Hash. Für konstante Vergleiche sollte `hash_equals()` verwendet werden, weil normale Stringvergleiche Timing-Informationen leaken können. Die PHP-Doku nennt `hash_equals()` ausdrücklich zur Abschwächung von Timing-Angriffen. ([PHP][4])

---

## 4. Scopes statt „ein Key darf alles“

Jeder Key bekommt Rechte.

Beispiel-Scopes:

```json
[
  "status:read",
  "db_import:write",
  "db_import:reset",
  "players:read",
  "games:read",
  "games:write",
  "analysis:write",
  "admin:keys"
]
```

Aktueller Webspace-API-Schnitt nutzt getrennte Key-Szenarien:

- Status-Key: `status:read`
- Append-Import-Key: `db_import:write`
- Reset-Import-Key: `db_import:reset`
- Admin-Key: `status:read`, `db_import:write`, `db_import:reset`, `admin`

Dann prüft jeder Endpoint:

```text
Hat dieser API-Key genau diesen Scope?
```

Beispiel:

```text
GET /api/v1/games/123
benötigt: games:read

POST /api/v1/analysis-results
benötigt: analysis:write
```

Das ist wichtig, weil OWASP Broken Object Level Authorization und Broken Authentication als zentrale API-Risiken aufführt. Besonders kritisch sind Endpoints, die Objekt-IDs entgegennehmen; dort muss immer geprüft werden, ob der aufrufende Key genau auf dieses Objekt zugreifen darf. ([OWASP Foundation][5])

---

## 5. Optional, aber sinnvoll: Request-Signatur für Schreibzugriffe

Für reine interne Nutzung reicht oft:

```http
Authorization: Bearer <api-key>
```

Aber weil die API öffentlich erreichbar ist, würde ich für kritische Schreib-Endpunkte zusätzlich eine HMAC-Signatur einplanen.

Client sendet:

```http
Authorization: Bearer <api-key>
X-CLM-Timestamp: 2026-05-20T12:34:56Z
X-CLM-Nonce: zufallswert
X-CLM-Signature: hmac_sha256(method + path + timestamp + nonce + body_hash)
```

Vorteile:

* ein mitgeschnittener Request kann nicht einfach wiederholt werden
* Body-Manipulation fällt auf
* Timestamp begrenzt Replay-Fenster
* Nonce kann einmalig gespeichert werden

Für Phase 1 kannst du ohne HMAC starten, aber die API-Struktur sollte so gebaut werden, dass HMAC später leicht ergänzt werden kann.

---

## 6. Rate Limiting

Mindestens pro API-Key:

```sql
CREATE TABLE api_rate_limits (
    api_key_id BIGINT UNSIGNED NOT NULL,
    window_start DATETIME NOT NULL,
    request_count INT NOT NULL,
    PRIMARY KEY (api_key_id, window_start)
);
```

Einfaches Modell:

```text
max. 60 Requests pro Minute pro Key
max. 5000 Requests pro Tag pro Key
```

Zusätzlich sinnvoll:

```text
max. 10 fehlgeschlagene Auth-Versuche pro IP / 10 Minuten
```

Auf Shared Hosting kann man das über DB machen. Nicht ideal schnell, aber für CaiLama völlig ausreichend.

OWASP nennt „Unrestricted Resource Consumption“ als API-Risiko; Rate Limits und Größenlimits sind deshalb keine Kosmetik, sondern Pflicht. ([OWASP Foundation][5])

---

## 7. Input-Validierung und Output-Begrenzung

Jeder Endpoint bekommt harte Regeln.

Beispiele:

```text
id: positive Ganzzahl oder UUID
name: max. 100 Zeichen
pgn: max. z.B. 1 MB
limit: max. 100
offset: max. 10000
date_from/date_to: ISO-Format
```

Bei Listen-Endpunkten niemals unbegrenzt liefern:

```http
GET /api/v1/games?limit=50&offset=0
```

Maximalwert serverseitig erzwingen:

```text
limit <= 100
```

Fehlerantworten immer neutral:

```json
{
  "error": {
    "code": "invalid_request",
    "message": "Invalid request."
  }
}
```

Keine SQL-Fehler, keine Pfade, keine Stacktraces öffentlich ausgeben.

---

## 8. Empfohlene Dateistruktur

Für Shared Hosting ohne schweres Framework:

```text
webspace/
├── api/
│   └── public/
│       ├── index.php
│       └── .htaccess
│
├── api_app/
│   ├── bootstrap.php
│   ├── config.php
│   ├── config.local.sample.php
│   ├── Router.php
│   ├── Response.php
│   ├── Auth/
│   │   ├── AuthService.php
│   │   ├── SessionManager.php
│   │   ├── ApiKeyAuthenticator.php
│   │   ├── ScopeGuard.php
│   │   └── RateLimiter.php
│   ├── Db/
│   │   └── ConnectionFactory.php
│   ├── Http/
│   │   └── Request.php
│   ├── Controllers/
│   │   ├── StatusController.php
│   │   ├── PlayersController.php
│   │   ├── GamesController.php
│   │   └── AnalysisController.php
│   └── Repositories/
│       ├── PlayersRepository.php
│       ├── GamesRepository.php
│       └── AnalysisRepository.php
│
└── secrets/
    └── api.env
```

Aktueller Master-Stand:

- `web/api_app/config.php` enthaelt nur Defaults.
- `web/api_app/config.local.sample.php` ist nur eine secretfreie Vorlage.
- Die echte Webspace-Konfiguration liegt ausserhalb des Public-Webroots unter
  `cailama-private/api/config.local.php`.
- `databases.cailama` verbindet die Provider-Datenbank.
- Die login- und fachlichen Tabellen leben in derselben Datenbank (single-database
  mode), da IONOS shared hosting offenbar nur einen DB-Host pro PHP-Prozess
  auflösen kann (Error 2005 bei Versuch einer zweiten Verbindung).
- Login/Authentifizierung erfolgt über dieselbe `cailama`-Datenbank.
- `web/login.php`, `web/account.php` und `web/logout.php` bilden die
  Session-Shell.
- `POST /api/v1/status` liefert geschuetzten neutralen Status. Ohne gueltigen
  Bearer-Key liefert die API keine API- oder DB-Details.
- `POST /api/v1/imports/cailama/append` und
  `POST /api/v1/imports/cailama/reset` verarbeiten nur die lokal konfigurierte
  serverseitige Dump-Datei. Der Request enthaelt keine Query-Parameter und
  keinen Body; gesendet wird nur ein Bearer-Key mit passendem Scope.
- Reset benoetigt `db_import:reset` oder `admin`; der normale Append-Key reicht
  dafuer nicht.
- `POST /api/v1/admin/schema/cailama` und
  `POST /api/v1/admin/schema/all` sind kurze, admin-geschuetzte PHP-Aktionen
  zur Schemaanlage auf dem Provider. Im Single-Database-Mode wenden beide das
  gleiche Schema inklusive `web_users` an. Auch sie akzeptieren keine Query-
  Parameter und keinen Body.
- Grosse Uebertragungen laufen per SFTP in einen nicht oeffentlich
  erreichbaren Webspace-Ordner. Wenn keine Datei vorhanden ist, wird der Import
  abgelehnt; nach erfolgreichem Import wird die Datei geloescht.
- `web/api_app/schema/cailama-data.sql` enthaelt die neutrale Schema-Vorlage
  (application tables + web_users fuer das Login-System).
- Provider-Schemas werden ueber die PHP-API im Webspace gesetzt, weil die
  Provider-Datenbanken nur von dort bearbeitet werden sollen. Lokale MySQL-
  Setup-Laeufe gelten nur fuer lokale DBs.

Ideal ist: `api_app/` und `secrets/` liegen **außerhalb des öffentlich erreichbaren Document Root**.

Falls dein Hoster das nicht erlaubt, dann mindestens per `.htaccess` blocken:

```apache
<FilesMatch "\.(env|ini|log|sql|bak)$">
    Require all denied
</FilesMatch>
```

Und keine Secrets in `public/`.

---

## 9. Webserver-Regeln

`public/.htaccess`:

```apache
Options -Indexes

RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]

<Files ".env">
    Require all denied
</Files>

Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "no-referrer"
Header always set X-Frame-Options "DENY"
```

Falls `Header` nicht erlaubt ist, nicht schlimm, aber versuchen.

Wichtig außerdem:

```text
display_errors = Off
log_errors = On
expose_php = Off, falls möglich
HTTPS erzwingen
keine Directory Listings
keine Backups im Webroot
```

---

## 10. API-Versionierung

Von Anfang an:

```text
/api/v1/status
/api/v1/players
/api/v1/games
```

Nicht direkt:

```text
/api/status
```

So kannst du später breaking changes sauber als `/api/v2/...` einführen.

---

## 11. Minimaler Endpoint-Schnitt

### `POST /api/v1/status`

Zweck: API erreichbar, DB erreichbar.

Der Endpoint akzeptiert weder Query-Parameter noch Request-Body. Der API-Key
wird ausschliesslich als Bearer-Key im Header gesendet.

Antwort:

```json
{
  "status": "ok",
  "api": "cailama-db-api",
  "version": "1.0.0",
  "db": "ok"
}
```

Scope:

```text
status:read
```

### `POST /api/v1/imports/cailama/append`

Zweck: Einen bereits serverseitig abgelegten `.sql`- oder `.sql.gz`-Dump in die
bestehende CaiLama-Datenbank einspielen. Der Endpoint akzeptiert weder
Query-Parameter noch Request-Body; Dateiname und Drop-Verzeichnis kommen aus
`config.local.php`.

Scope:

```text
db_import:write
```

Fehler:

```text
no_import_file
body_not_allowed
```

Nach erfolgreichem Import wird die Dump-Datei geloescht.

### `POST /api/v1/imports/cailama/reset`

Zweck: Die CaiLama-Datenbank zuruecksetzen und danach den konfigurierten Dump
einspielen. Dieser Endpoint ist nur verfuegbar, wenn `allow_reset` lokal
bewusst aktiviert wurde. Auch hier werden keine Query-Parameter und kein
Request-Body akzeptiert.

Scope:

```text
db_import:reset
```

Nach erfolgreichem Import wird die Dump-Datei geloescht.

### `POST /api/v1/admin/schema/cailama`

Zweck: Das CaiLama-Anwendungsschema (inkl.
`web_users`-Login-Tabelle) in der Provider-Datenbank idempotent anlegen. Dieser Endpoint nutzt das fest versionierte
Schema `web/api_app/schema/cailama-data.sql`.

Scope:

```text
admin
```

Der Endpoint akzeptiert weder Query-Parameter noch Request-Body.

### `POST /api/v1/admin/schema/all`

Zweck: Das CaiLama-Schema ueber die PHP-API anwenden. Aequivalent zu
`schema/cailama`. Die Route ist fuer Skripte gedacht, die den Namen `all`
wiederholend verwenden.

Scope:

```text
admin
```

### `GET /api/v1/players/{id}`

Zweck: Spieler laden.

Scope:

```text
players:read
```

### `GET /api/v1/games/{id}`

Zweck: Partie laden.

Scope:

```text
games:read
```

### `POST /api/v1/games`

Zweck: Partie speichern.

Scope:

```text
games:write
```

### `POST /api/v1/analysis-results`

Zweck: Analyseergebnis speichern.

Scope:

```text
analysis:write
```

Für CaiLama würde ich bewusst mit wenigen Endpoints starten. Erst Status, dann Read, dann Write.

---

## 12. Logging ohne Geheimnisse

Tabelle:

```sql
CREATE TABLE api_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_key_id BIGINT UNSIGNED NULL,
    request_id CHAR(36) NOT NULL,
    method VARCHAR(10) NOT NULL,
    path VARCHAR(255) NOT NULL,
    status_code INT NOT NULL,
    ip_hash CHAR(64) NULL,
    user_agent_hash CHAR(64) NULL,
    created_at DATETIME NOT NULL
);
```

Nicht loggen:

```text
Authorization Header
API-Key
Request Body mit PGN/privaten Daten, außer bewusst gekürzt
DB-Passwörter
Stacktraces
```

Loggen:

```text
request_id
endpoint
status
api_key_id
Zeit
Fehlerklasse
```

---

## 13. Admin-Key-Verwaltung

Keine API-Keys händisch in SQL eintragen.

Stattdessen kleines CLI-/Admin-Script auf dem Webspace:

```text
php bin/create-api-key.php --name "cailama-vm" --scopes "status:read,games:read,games:write,analysis:write"
```

Ausgabe einmalig:

```text
API key created:

clm_live_K7Q9F2_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

Store this key now. It will not be shown again.
```

Der Klartext-Key wird danach nie wieder angezeigt.

Wenn CLI beim Hoster nicht geht, dann temporäres Admin-PHP-Script, aber nur geschützt, kurz verwenden, danach löschen.

---

## 14. DB-Rechte minimal halten

Nicht denselben DB-User für alles verwenden, wenn dein Hoster mehrere DB-User erlaubt.

Ideal:

```text
api_read_user      SELECT
api_write_user     SELECT, INSERT, UPDATE für erlaubte Tabellen
api_admin_user     Migrationen, manuell/selten
```

Falls nur ein DB-User möglich ist: Schaden durch API-Schicht begrenzen, keine generischen SQL-Endpunkte, Prepared Statements, Scopes, Rate Limit, Logging.

---

## 15. Deployment-Konzept

Repository enthält:

```text
api/public/index.php
api_app/...
.env.example
README.md
schema.sql
```

Repository enthält nicht:

```text
api.env
DB-Passwort
API-Key
Hoster-Zugang
FTP/SFTP-Zugang
echte Domains mit Secrets
```

Deployment:

```text
1. Code per SFTP hochladen
2. Private Webspace-Konfig ausserhalb von /public deployen
3. Rechte setzen, soweit Hoster erlaubt
4. DB-Schema ueber geschuetzte PHP-Admin-Endpunkte setzen
5. API-Keys lokal generieren und nur Hashes auf den Server schreiben
6. POST /api/v1/status testen
```

---

## 16. Sicherheits-Baseline für `api.env`

Beispiel ohne echte Werte:

```ini
APP_ENV=prod
APP_DEBUG=false

DB_HOST=interner-db-host
DB_PORT=3306
DB_NAME=cailama
DB_USER=api_user
DB_PASSWORD=change-me

API_PEPPER=change-me-long-random-secret
CORS_ALLOWED_ORIGINS=
MAX_BODY_BYTES=1048576
```

`API_PEPPER` ist ein zusätzlicher serverseitiger Geheimwert, der beim Hashing/Prüfen helfen kann. Wichtig: Wenn der verloren geht, sind alte Key-Hashes ggf. nicht mehr prüfbar. Also sicher sichern.

---

## 17. CORS

Wenn die API nur von Servern/CLI/VM genutzt wird:

```text
kein CORS nötig
```

Nicht setzen:

```http
Access-Control-Allow-Origin: *
```

Falls später Browser-Frontend direkt zugreifen soll:

```text
Access-Control-Allow-Origin: https://cailama.org
```

Aber: API-Keys im Browser sind grundsätzlich kritisch. Für Browser lieber Session/Login oder kurzlebige Tokens, nicht den internen CaiLama-API-Key.

---

## 18. Akzeptanztests

Vor „live“ sollte Folgendes funktionieren:

```text
1. Request ohne API-Key -> 401
2. Request mit falschem API-Key -> 401
3. Request mit revoked Key -> 401
4. Request mit gültigem Key, aber falschem Scope -> 403
5. Request mit gültigem Scope -> 200/201
6. SQL-Injection-Test in Parametern -> keine Wirkung
7. Limit > 100 -> wird auf 100 begrenzt oder 400
8. zu großer Body -> 413
9. Rate Limit überschritten -> 429
10. DB-Fehler -> 500 ohne interne Details
11. Logs enthalten keine Secrets
12. `.env` per Browser nicht abrufbar
```

---

## 19. Empfohlene Umsetzungsphasen

### Phase 1: Minimal sichere API

Ziel: API erreichbar, Key-Prüfung, DB-Verbindung, Status-Endpoint.

Umfang:

```text
POST /api/v1/status
PDO-Verbindung
.env-Konfiguration
API-Key-Tabelle
API-Key-Erzeugung
401/403/500 JSON-Fehler
```

### Phase 2: Erste fachliche Read-Endpoints

Ziel: CaiLama kann Daten lesen.

Umfang:

```text
GET /api/v1/players/{id}
GET /api/v1/games/{id}
GET /api/v1/games?limit=&offset=
Scopes
Prepared Statements
Pagination
```

### Phase 3: Schreibzugriffe

Ziel: CaiLama kann Ergebnisse speichern.

Umfang:

```text
POST /api/v1/games
POST /api/v1/analysis-results
Body-Limit
Schema-Validierung
Audit-Log
```

### Phase 4: Härtung

Ziel: Internet-tauglicher Betrieb.

Umfang:

```text
Rate Limiting
Nonce/HMAC für Schreibzugriffe
Key-Rotation
Admin-Script für Keys
Monitoring-Endpoint
saubere Fehler-/Auditlogs
```

### Phase 5: CaiLama-Integration

Ziel: CaiLama nutzt die API statt direkter DB-Verbindung.

Umfang:

```text
CaiLama-DB-Client
Konfiguration lokal
Retry-Logik
Timeouts
Smoke-Test
Dokumentation
```

---

## 20. Konkrete Empfehlung

Ich würde es so bauen:

```text
Name: CaiLama-Webspace-DB-API
Typ: kleine PHP-8.5-API ohne schweres Framework
Auth: Bearer API-Key mit Prefix + Hash + Scopes
DB: PDO + Prepared Statements
Endpoints: fachlich, nicht generisch
Security: HTTPS, Rate Limit, Body Limit, Scope Guard, Audit Log
Optional später: HMAC-Signatur für Write-Endpoints
```

Der wichtigste Architekturentscheid ist: **Die API darf nur die Operationen anbieten, die CaiLama wirklich braucht.** Nicht „mach mir die DB erreichbar“, sondern „stelle kontrollierte CaiLama-Funktionen bereit“. Das hält den Schaden bei einem Fehler oder Key-Leak begrenzt.

[1]: https://www.php.net/supported-versions.php "PHP: Supported Versions"
[2]: https://www.php.net/manual/en/pdo.prepare.php "PDO::prepare - Manual"
[3]: https://www.php.net/manual/en/function.random-bytes.php "random_bytes - Manual"
[4]: https://www.php.net/manual/en/function.hash-equals.php "hash_equals - Manual"
[5]: https://owasp.org/API-Security/editions/2023/en/0x11-t10/ "OWASP Top 10 API Security Risks 2023"
