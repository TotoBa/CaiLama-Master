# Webseite

Die öffentliche CaiLama-Webseite ist für folgende URL vorgesehen:

```text
https://cailama.org/
```

## Ziel

Die Webseite ist die Human-Version der Master-Gesamtdokumentation. Sie wird als
PHP-Webspace vorbereitet, auch wenn die sichtbaren Seiten aktuell noch ohne
dynamische Produktlogik auskommen. Zusätzlich werden LLM-freundliche und
maschinenlesbare Dateien ausgeliefert.

## Versionierte Quellen

Die öffentlichen Dateien der Website liegen im Master-Repo unter:

```text
web/
```

Die CaiLama-eigene private Website-Schicht liegt unter:

```text
web-smarty/
```

`web/` wird nach `<webspace-root>/public/` deployt und bleibt der öffentliche
Document Root. `web-smarty/` wird nach `<webspace-root>/smarty/` deployt und
liegt als privater Sibling neben `public/`. Smarty-Templates, Content-Daten,
Cache und Dependency-Dateien dürfen nicht unter `web/` liegen.

Wichtige Dateien:

```text
web/index.php                 # Startseite mit Trainingsfokus
web/status.php                # Status- und Repo-Übersicht
web/projects.php              # Projekt- und Repo-Details
web/architecture.php          # Architektur und Schnittstellen
web/roadmap.php               # Roadmap aus status.plan.cailama.md
web/operations.php            # Betrieb, Checks, Deployment
web/reference.php             # Human-/LLM-Referenzseite
web/login.php                 # Login-Formular mit Session-Schutz
web/account.php               # geschützter Konto-Stub
web/benchmark-feedback.php    # geschützte Benchmark-Feedback-Erfassung
web/logout.php                # CSRF-geschützter Logout
web/robots.txt                # Crawler-Regeln mit Sitemap-Verweis
web/sitemap.xml               # Canonical XML-Sitemap für Suchmaschinen
web/api/public/index.php      # vorbereiteter API-Frontcontroller
web/api_app/                  # interne API-Skelettstruktur ohne Secrets
web/api_app/Controllers/      # Status-, Import- und Admin-Schema-Controller
web/api_app/config.local.sample.php
web/assets/styles.css          # Gemeinsames Styling
web/llms.txt                   # LLM-Einstiegspunkt
web/ecosystem-reference.md     # LLM-freundliche Markdown-Referenz
web/data/ecosystem.json        # Maschinenlesbare JSON-Referenz
web-smarty/bootstrap.php       # privater Website-Bootstrap
web-smarty/content/            # versionierte Site- und Seiten-Daten
web-smarty/templates/          # versionierte Smarty-Templates
web-smarty/cache/              # nur .gitkeep; Laufzeitcache ignoriert
```

Die Smarty-Library selbst wird nicht versioniert. Benötigt wird
`smarty/smarty ^5.0`. Für lokale Render-Smokes und Deployment wird die
Abhängigkeit in `web-smarty/vendor/` installiert; `vendor/` und
`composer.lock` bleiben ignoriert.

Die inhaltlichen Doku-Quellen im Master sind:

```text
docs/ecosystem-reference.md
docs/data/ecosystem.json
docs/ecosystem-map.md
docs/product-positioning.md
docs/benchmarks.md
docs/benchmark-results/README.md
docs/integrations.md
docs/roadmap.md
docs/orchestration.md
docs/quality.md
docs/local-setup.md
skills/kimi-cli-cailama-ecosystem/SKILL.md
```

Synchronisationsregel:

- `docs/ecosystem-reference.md` muss identisch zu `web/ecosystem-reference.md`
  sein.
- `docs/data/ecosystem.json` muss identisch zu `web/data/ecosystem.json` sein.
- `scripts/check-ecosystem.sh` prüft diese Gleichheit.
- Sichtbare deutsche Webseitentexte verwenden echte Umlaute und `ß`, keine
  Umschreibungen wie `ae`, `oe`, `ue` oder `ss`, sofern es sich nicht um Code,
  URLs, Dateinamen, API-Felder oder andere technische Bezeichner handelt.
- Sichtbare Seiten führen im Footer einen Kontakt-Link auf
  `mailto:info@cailama.org`.

Die Seite nutzt das vorhandene CaiLama-Logo direkt aus dem Haupt-Repository:

```text
https://raw.githubusercontent.com/TotoBa/CaiLama/main/img/logo-big.png
```

Dadurch wird die Logo-Datei nicht im Master-Repo dupliziert.

Im Hero-Bereich wird das Hintergrund-Logo farbig, aber leicht aufgehellt
gerendert, weil der dunkle `Cai`-Teil des Logos auf dem dunklen/grünen
Hintergrund sonst verschwindet. Das sichtbare Hero-Logo bleibt unverfälscht.

## Webspace

Der Live-Webspace-Pfad ist host-spezifisch und wird nicht in der offiziellen
Doku festgeschrieben. Der Webspace ist logisch so aufgebaut:

```text
<webspace-root>/
├── public/        # öffentlicher Document Root; Inhalt aus repo:web/
├── smarty/        # privater Website-App-Bereich; Inhalt aus repo:web-smarty/
└── cailama-private/
```

Öffentliche Controller unter `public/*.php` binden die private App
serverseitig über `../smarty/bootstrap.php` ein. Für lokale Tests im Repo
sucht `web/_private_app.php` zusätzlich `../web-smarty/bootstrap.php`.

Das Deployment-Skript nutzt für Live-Deployment natives SFTP. Ziel,
Remote-Verzeichnis und optionale SSH-/SFTP-Parameter kommen aus einer lokalen,
nicht versionierten Konfiguration oder aus Umgebungsvariablen.

Lokale Operator-Konfiguration:

```text
~/.config/cailama/web-deploy.env
```

Unterstützte Variablen:

```bash
CAILAMA_WEB_DEPLOY_METHOD=sftp
CAILAMA_WEB_SFTP_TARGET=<sftp-user-and-host>
CAILAMA_WEB_SFTP_REMOTE_DIR=<remote-public-dir>
CAILAMA_WEB_SFTP_REMOTE_ROOT=<remote-webspace-root>
CAILAMA_WEB_SFTP_REMOTE_SMARTY_DIR=<remote-smarty-dir>
CAILAMA_WEB_SFTP_IP_VERSION=4
CAILAMA_WEB_SFTP_PORT=<optional-port>
CAILAMA_WEB_SFTP_IDENTITY_FILE=<optional-key-file>
CAILAMA_WEB_SFTP_CONFIG=<optional-ssh-config>
CAILAMA_WEB_SFTP_KNOWN_HOSTS_FILE=<optional-known-hosts-file>
CAILAMA_WEB_SFTP_STRICT_HOST_KEY_CHECKING=accept-new
CAILAMA_WEB_SFTP_CONNECT_TIMEOUT=20
CAILAMA_WEB_SFTP_PASSWORD_FILE=<optional-local-secret-file>
CAILAMA_PUBLIC_URL=https://cailama.org
CAILAMA_DEPLOY_ALLOW_MISSING_VENDOR=0
```

Wenn `CAILAMA_WEB_SFTP_REMOTE_ROOT` gesetzt ist, leitet das Skript daraus
`public/` und `smarty/` ab. Wenn nur `CAILAMA_WEB_SFTP_REMOTE_DIR` gesetzt ist,
wird der private Smarty-Zielordner als Sibling des Public-Ordners abgeleitet.

Diese Datei und eine optionale Passwortdatei dürfen keine Vorlage für Commits
sein und gehören nicht ins Repo. Für lokale Tests kann das Skript weiterhin
mit einem expliziten lokalen Zielpfad aufgerufen werden.

Agenten dürfen die lokale Deploy-Konfiguration durch die vorhandenen Skripte
nutzen, wenn der Nutzer das Deployment ausdrücklich beauftragt. Sie dürfen
diese Dateien aber nicht anzeigen, kopieren, zusammenfassen oder in Logs,
TODOs, Dokumentation oder Commits übernehmen.

## Reproduzierbares Deployment

Die Website hat keinen Build-Schritt im Repository. Vor dem Deployment muss
die private Dependency lokal oder in der deployenden Arbeitskopie vorhanden
sein:

```bash
cd web-smarty
composer install --no-dev --optimize-autoloader
```

Live-Deployment ist ein nativer SFTP-Upload von `web/` nach `public/` und
`web-smarty/` nach `smarty/`. `web-smarty/vendor/` wird deployt, aber nicht
versioniert.

Standardbefehl:

```bash
scripts/deploy-website.sh
```

Expliziter lokaler Test-Zielpfad:

```bash
scripts/deploy-website.sh <local-public-dir>
```

Das Skript:

1. ermittelt das Git-Root,
2. prüft `web/`, `web-smarty/`, `web-smarty/bootstrap.php` und
   `web-smarty/vendor/autoload.php`,
3. lädt zuerst `web-smarty/` in den privaten Smarty-Zielordner,
4. lädt danach `web/` per OpenSSH-`sftp` in den öffentlichen Document Root,
5. entfernt stale Dateien anhand getrennter SFTP-Deploy-Manifeste,
6. schützt die echte, ignorierte `web/api_app/config.local.php` vor Löschung,
7. prüft beim Standard-Live-Ziel die gerenderten öffentlichen Seiten,
   statischen Dateien und LLM-Referenzen per SHA-256 über HTTPS.

Der Standard-Ecosystem-Check berührt den Live-Webspace nicht. Mit
`CAILAMA_CHECK_DEPLOYED_WEBSITE=1 bash scripts/check-ecosystem.sh` wird nur ein
HTTPS-Live-Check gegen die öffentliche Seite ausgeführt; der Check greift
nicht direkt auf den Webspace-Mount zu.

Deploy-Verifikation:

- Standard für SFTP-Live-Deployment: `CAILAMA_DEPLOY_VERIFY=http-hash`.
- Explizit aus: `CAILAMA_DEPLOY_VERIFY=none scripts/deploy-website.sh`.
- Direkter Zielpfad-Hash nur für lokale Testziele: `CAILAMA_DEPLOY_VERIFY=target-hash
  scripts/deploy-website.sh <local-public-dir>`. Dieser Modus liest keinen
  Live-Webspace-Mount.

## Reproduzierbare Prüfung

Nach jeder Website-Aenderung:

```bash
python3 -m json.tool docs/data/ecosystem.json >/dev/null
python3 -m json.tool web/data/ecosystem.json >/dev/null
cmp -s docs/ecosystem-reference.md web/ecosystem-reference.md
cmp -s docs/data/ecosystem.json web/data/ecosystem.json
find web web-smarty \
  -path 'web-smarty/vendor' -prune -o \
  -path 'web-smarty/cache/templates_c' -prune -o \
  -path 'web-smarty/cache/smarty' -prune -o \
  -name '*.php' -print0 | xargs -0 -n1 php -l
php web/index.php >/tmp/cailama-index.html
php web/projects.php >/tmp/cailama-projects.html
php web/status.php >/tmp/cailama-status.html
scripts/deploy-website.sh
bash scripts/check-ecosystem.sh
curl -I -L --max-time 12 https://cailama.org/
```

Erwartung:

- `https://cailama.org/` liefert `HTTP/2 200` oder einen gleichwertigen
  erfolgreichen HTTP-Status.
- HTTP-Aufrufe werden per Webserver-Regel auf HTTPS umgeleitet, sofern
  `.htaccess` vom Hoster ausgewertet wird.
- Alte Seiten-URLs mit `.html` werden serverseitig auf `.php` umgeleitet,
  sofern `.htaccess` vom Hoster ausgewertet wird.
- `https://cailama.org/llms.txt` ist erreichbar.
- `https://cailama.org/ecosystem-reference.md` ist erreichbar.
- `https://cailama.org/data/ecosystem.json` ist erreichbar und valides JSON.
- `https://cailama.org/robots.txt` ist erreichbar und verweist auf
  `https://cailama.org/sitemap.xml`.
- `https://cailama.org/sitemap.xml` ist erreichbar, valides XML und enthält
  nur kanonische HTTPS-URLs.

## Googlebot und Indexierung

Die Website stellt für Suchmaschinen bereit:

```text
https://cailama.org/robots.txt
https://cailama.org/sitemap.xml
```

`robots.txt` erlaubt die öffentlichen Seiten, verweist auf die Sitemap und
schließt API-, interne App-, Sitzungs- und Benchmark-Feedback-Endpunkte aus.
Die Sitemap enthält nur kanonische HTTPS-URLs der öffentlichen
Dokumentationsseiten und maschinenlesbar auslieferbaren Referenzen. Login-,
Konto- und Benchmark-Feedback-Seiten gehören nicht in die Sitemap; sie tragen
zusätzlich `noindex`.

Indexierung wird sauber über die Sitemap-Erkennung in `robots.txt` und über
Google Search Console angestossen. Der alte Sitemap-Ping-Endpunkt wird nicht
verwendet, weil Google HTTP-Pings auf diesen Endpunkt nicht mehr sinnvoll
verarbeitet. Für den manuellen Schritt in Search Console:

```text
Property: https://cailama.org/
Sitemap: https://cailama.org/sitemap.xml
URL-Prüfung: https://cailama.org/
```

## Webspace-API

Die DB-API ist als kleine PHP-Fassade vorbereitet. Sie ist kein generischer
SQL-Proxy und enthält keine produktiven Datenbankzugangsdaten. Der aktuelle
Stand stellt geschützten Status, Login-/Session-Shell, eine einzige
PDO-Konfiguration für den Shared-Hosting-Betrieb, kontrollierte CaiLama-Import-Endpunkte,
geschützte Schema-Setup-Endpunkte für den Provider und geschütztes
Benchmark-Feedback bereit:

```text
POST /api/v1/status
POST /api/v1/imports/cailama/append
POST /api/v1/imports/cailama/reset
POST /api/v1/admin/schema/cailama
POST /api/v1/admin/schema/all
/login.php
/account.php
/benchmark-feedback.php
```

Status-, Import- und Schema-Endpunkte nehmen weder Query-Parameter noch
Nutzdaten im Request-Body an. Gesendet wird nur ein Bearer-Key mit passendem
Scope: `status:read` für Status, `db_import:write` für Append-Import,
`db_import:reset` für Reset-Import oder `admin` für Schema-Setup und
Admin-Aktionen. Ohne gültigen Key liefert die API keine API-, DB-, Schema-
oder Importdetails. Der Import-Modus wird über den Pfad gewählt: `append`
fügt erlaubte Insert-Daten in die bestehende CaiLama-Datenbank ein; `reset`
ist nur aktiv, wenn `allow_reset` in der lokalen Konfiguration bewusst gesetzt
wurde.

Große Übertragungen laufen nicht über HTTP-Request-Body. Der Dump wird per
SFTP in einen nicht öffentlich erreichbaren Webspace-Ordner gelegt. Die API
verarbeitet nur den in `config.local.php` fest konfigurierten Dateinamen,
standardmäßig eine `.sql`- oder `.sql.gz`-Datei. Wenn keine Datei vorhanden
ist, wird der Import mit `no_import_file` abgelehnt. Nach erfolgreichem Import
wird die Datei gelöscht; ein fehlgeschlagener Cleanup wird als eigener Fehler
gemeldet, damit keine Importdatei versehentlich liegen bleibt.

Die versionierte `web/api_app/config.php` enthält sichere Defaults. Die echte
Provider-Konfiguration gehört nicht in den öffentlichen Document Root. Auf
dem Webspace liegt `/public` im öffentlichen Bereich; private Konfiguration
und Import-Drop liegen als Sibling-Ordner im Webspace-Root. Die API sucht aus
`/public/api_app/` zuerst diese private Konfig:

```text
../../cailama-private/api/config.local.php
```

Nur als lokale Legacy-/Fallback-Variante wird noch
`web/api_app/config.local.php` unterstützt; diese Datei bleibt gitignoriert und
wird beim Private-Deploy aus dem Public-Webspace entfernt. Als secretfreie
Vorlage dient `web/api_app/config.local.sample.php`.

Das wiederholbare Setup läuft über:

```bash
scripts/generate-web-api-keys.sh
scripts/setup-webspace-db-api.sh --source <private-db-config> --all --allow-reset
```

Nach dem ersten Normalisierungslauf kann das Setup ohne `--source` wiederholt
werden; dann nutzt es die private `databases.ini`. Der Setup-Pfad erzeugt
lokale private Dateien mit restriktiven Rechten unter
`~/.config/cailama`, schreibt die PHP-Konfig für den Webspace, legt
MySQL-Defaults für lokale Setup-Skripte an und lädt die private PHP-Konfig
per SFTP in einen nicht öffentlichen Webspace-Ordner. Lokale Schemas werden
mit dem lokalen MySQL-Client angelegt. Provider-Schemas werden bewusst nicht
von lokalem MySQL aus angefasst, sondern über die geschützten PHP-Endpunkte
im Webspace gesetzt, weil die Provider-DB nur vom Webspace aus erreichbar sein
soll.

Die Konfiguration besteht aus einer einzigen Datenbank:

- `databases.cailama`: Provider-Datenbank für Login/Users, Anwendungsdaten
  und Schema-Management (2 GB beim Provider).

Der Login nutzt PHP-Sessions mit `HttpOnly`, `SameSite=Lax`, HTTPS-abhängigem
Secure-Cookie, CSRF-Token, einfachem Session-basiertem Versuchslimit und
`password_verify()` gegen Passwort-Hashes aus `web_users` in derselben
`databases.cailama`-Provider-Datenbank. Die SQL-Vorlagen liegen unter
`web/api_app/schema/`.

Es gibt keine öffentliche Kontoanlage und keinen Registrierungsendpunkt. Nutzer
werden direkt als `web_users` in der Provider-Datenbank angelegt. Die Seite
`/benchmark-feedback.php` ist nur nach Login erreichbar und nutzt dieselbe
Session. Sie speichert wiederverwendbare Bewertungsdaten für Modellrollen in
`cailama_model_benchmark_cases` und `cailama_model_feedback`: Laufzeit,
Input-/Thinking-/Output-Tokens, Qualitäts-Score, Aufgaben-Score,
Logikfehler-Klasse, A/B-Präferenz und knappe Feedbacknotizen. Rohprompts,
volle Modellantworten, private Partiearchive, lokale Pfade und Secrets gehören
nicht in diese Tabellen.

API-Key-Prüfung und Scopes sind als Hash-basierte Bearer-Token-Prüfung
verdrahtet. Es gibt getrennte Keys für Status, Append-Import, Reset-Import und
Admin. Auf dem Server liegen nur Hashes; die Klartext-Keys bleiben in privaten
lokalen Client-Konfigdateien. Es gibt keinen öffentlichen GET-Status mit
DB-Information. Fachliche Read-/Write-Endpunkte jenseits Status und Dump-Import
bleiben Folgearbeit. Keine produktiven Keys, DB-Passwörter oder
Hoster-Zugangsdaten werden in `web/`, `docs/` oder Beispiele geschrieben.

Aktueller Betriebsstatus am 2026-05-22: Single-Database-Mode ist aktiv. Die
Provider-Schemaanlage und Provider-Verbindungschecks laufen über die
Webspace-API; `POST /api/v1/status` meldet `databases.cailama: ok`, und
`POST /api/v1/admin/schema/cailama` beziehungsweise
`POST /api/v1/admin/schema/all` wenden dasselbe Schema an. Echte Host-, User-
oder Passwortwerte werden nicht in Doku oder Repo geschrieben.

Deployment-Status am 2026-05-23: `scripts/deploy-website.sh` hat `web/`
per SFTP deployt und die öffentlichen Dateien per HTTPS-Hash verifiziert.
`CAILAMA_CHECK_DEPLOYED_WEBSITE=1 bash scripts/check-ecosystem.sh` bestätigt
robots, Sitemap und JSON über HTTPS.

Der Umsetzungsplan liegt unter `docs/db-api.plan.md`.

## Unterprojekte

Die Unterprojekte verweisen in ihren `README.md` auf die gemeinsame
Ecosystem-Doku:

```text
https://cailama.org/reference.php
https://cailama.org/llms.txt
https://cailama.org/ecosystem-reference.md
https://cailama.org/data/ecosystem.json
```

Damit ist die Master-Doku sowohl die Human-Version für Nutzer als auch die
LLM-freundliche Nachschlagebasis für alle CaiLama-Repositories.

Die Webserver-, DNS- und TLS-Konfiguration für `cailama.org` liegt außerhalb
dieses Repositories. Keine Zertifikate, Tokens oder Server-Secrets in dieses
Repo schreiben.
