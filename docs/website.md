# Webseite

Die oeffentliche CaiLama-Webseite ist fuer folgende URL vorgesehen:

```text
https://cailama.org/
```

## Ziel

Die Webseite ist die Human-Version der Master-Gesamtdokumentation. Sie wird als
PHP-Webspace vorbereitet, auch wenn die sichtbaren Seiten aktuell noch ohne
dynamische Produktlogik auskommen. Zusaetzlich werden LLM-freundliche und
maschinenlesbare Dateien ausgeliefert.

## Versionierte Quellen

Die Website liegt vollstaendig im Master-Repo unter:

```text
web/
```

Wichtige Dateien:

```text
web/index.php                 # Startseite
web/projects.php              # Projekt- und Repo-Details
web/architecture.php          # Architektur und Schnittstellen
web/roadmap.php               # Roadmap aus status.plan.cailama.md
web/operations.php            # Betrieb, Checks, Deployment
web/reference.php             # Human-/LLM-Referenzseite
web/login.php                 # Login-Formular mit Session-Schutz
web/account.php               # geschuetzter Konto-Stub
web/logout.php                # CSRF-geschuetzter Logout
web/robots.txt                # Crawler-Regeln mit Sitemap-Verweis
web/sitemap.xml               # Canonical XML-Sitemap fuer Suchmaschinen
web/api/public/index.php      # vorbereiteter API-Frontcontroller
web/api_app/                  # interne API-Skelettstruktur ohne Secrets
web/api_app/Controllers/      # Status-, Import- und Admin-Schema-Controller
web/api_app/config.local.sample.php
web/assets/styles.css          # Gemeinsames Styling
web/llms.txt                   # LLM-Einstiegspunkt
web/ecosystem-reference.md     # LLM-freundliche Markdown-Referenz
web/data/ecosystem.json        # Maschinenlesbare JSON-Referenz
```

Die inhaltlichen Doku-Quellen im Master sind:

```text
docs/ecosystem-reference.md
docs/data/ecosystem.json
docs/ecosystem-map.md
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
- `scripts/check-ecosystem.sh` prueft diese Gleichheit.

Die Seite nutzt das vorhandene CaiLama-Logo direkt aus dem Haupt-Repository:

```text
https://raw.githubusercontent.com/TotoBa/CaiLama/main/img/logo-big.png
```

Dadurch wird die Logo-Datei nicht im Master-Repo dupliziert.

Im Hero-Bereich wird das Hintergrund-Logo farbig, aber leicht aufgehellt
gerendert, weil der dunkle `Cai`-Teil des Logos auf dem dunklen/gruenen
Hintergrund sonst verschwindet. Das sichtbare Hero-Logo bleibt unverfaelscht.

## Webspace

Der Live-Webspace-Pfad ist host-spezifisch und wird nicht in der offiziellen
Doku festgeschrieben. Das Deployment-Skript nutzt fuer Live-Deployment natives
SFTP. Ziel, Remote-Verzeichnis und optionale SSH-/SFTP-Parameter kommen aus
einer lokalen, nicht versionierten Konfiguration oder aus Umgebungsvariablen.

Lokale Operator-Konfiguration:

```text
~/.config/cailama/web-deploy.env
```

Unterstuetzte Variablen:

```bash
CAILAMA_WEB_DEPLOY_METHOD=sftp
CAILAMA_WEB_SFTP_TARGET=<sftp-user-and-host>
CAILAMA_WEB_SFTP_REMOTE_DIR=<remote-public-dir>
CAILAMA_WEB_SFTP_IP_VERSION=4
CAILAMA_WEB_SFTP_PORT=<optional-port>
CAILAMA_WEB_SFTP_IDENTITY_FILE=<optional-key-file>
CAILAMA_WEB_SFTP_CONFIG=<optional-ssh-config>
CAILAMA_WEB_SFTP_KNOWN_HOSTS_FILE=<optional-known-hosts-file>
CAILAMA_WEB_SFTP_STRICT_HOST_KEY_CHECKING=accept-new
CAILAMA_WEB_SFTP_CONNECT_TIMEOUT=20
CAILAMA_WEB_SFTP_PASSWORD_FILE=<optional-local-secret-file>
CAILAMA_PUBLIC_URL=https://cailama.org
```

Diese Datei und eine optionale Passwortdatei duerfen keine Vorlage fuer Commits
sein und gehoeren nicht ins Repo. Fuer lokale Tests kann das Skript weiterhin
mit einem expliziten lokalen Zielpfad aufgerufen werden.

## Reproduzierbares Deployment

Die Website hat keinen Build-Schritt. Live-Deployment ist ein nativer
SFTP-Upload von `web/` in den PHP-Webspace.

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
2. laedt `web/` per OpenSSH-`sftp` in das konfigurierte Remote-Verzeichnis,
3. entfernt stale Dateien anhand eines SFTP-Deploy-Manifests,
4. schuetzt die echte, ignorierte `web/api_app/config.local.php` vor Loeschung,
5. prueft beim Standard-Live-Ziel ausgewaehlte oeffentliche Dateien per
   SHA-256 ueber HTTPS und ruft die oeffentlichen PHP-Seiten kurz ab.

Der Standard-Ecosystem-Check beruehrt den Live-Webspace nicht. Mit
`CAILAMA_CHECK_DEPLOYED_WEBSITE=1 bash scripts/check-ecosystem.sh` wird nur ein
HTTPS-Live-Check gegen die oeffentliche Seite ausgefuehrt; der Check greift
nicht direkt auf den Webspace-Mount zu.

Deploy-Verifikation:

- Standard fuer SFTP-Live-Deployment: `CAILAMA_DEPLOY_VERIFY=http-hash`.
- Explizit aus: `CAILAMA_DEPLOY_VERIFY=none scripts/deploy-website.sh`.
- Direkter Zielpfad-Hash nur fuer lokale Testziele: `CAILAMA_DEPLOY_VERIFY=target-hash
  scripts/deploy-website.sh <local-public-dir>`. Dieser Modus liest keinen
  Live-Webspace-Mount.

## Reproduzierbare Pruefung

Nach jeder Website-Aenderung:

```bash
python3 -m json.tool docs/data/ecosystem.json >/dev/null
python3 -m json.tool web/data/ecosystem.json >/dev/null
cmp -s docs/ecosystem-reference.md web/ecosystem-reference.md
cmp -s docs/data/ecosystem.json web/data/ecosystem.json
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
- `https://cailama.org/sitemap.xml` ist erreichbar, valides XML und enthaelt
  nur kanonische HTTPS-URLs.

## Googlebot und Indexierung

Die Website stellt fuer Suchmaschinen bereit:

```text
https://cailama.org/robots.txt
https://cailama.org/sitemap.xml
```

`robots.txt` erlaubt die oeffentlichen Seiten, verweist auf die Sitemap und
schliesst API-, interne App- und Sitzungsendpunkte aus. Die Sitemap enthaelt nur
kanonische HTTPS-URLs der oeffentlichen Dokumentationsseiten und maschinenlesbar
auslieferbaren Referenzen. Login- und Konto-Seiten gehoeren nicht in die
Sitemap; sie tragen zusaetzlich `noindex`.

Indexierung wird sauber ueber die Sitemap-Erkennung in `robots.txt` und ueber
Google Search Console angestossen. Der alte Sitemap-Ping-Endpunkt wird nicht
verwendet, weil Google HTTP-Pings auf diesen Endpunkt nicht mehr sinnvoll
verarbeitet. Fuer den manuellen Schritt in Search Console:

```text
Property: https://cailama.org/
Sitemap: https://cailama.org/sitemap.xml
URL-Pruefung: https://cailama.org/
```

## Webspace-API

Die DB-API ist als kleine PHP-Fassade vorbereitet. Sie ist kein generischer
SQL-Proxy und enthaelt keine produktiven Datenbankzugangsdaten. Der aktuelle
Stand stellt geschuetzten Status, Login-/Session-Shell, eine einzige
PDO-Konfiguration fuer den Shared-Hosting-Betrieb, kontrollierte CaiLama-Import-Endpunkte und geschuetzte
Schema-Setup-Endpunkte fuer den Provider bereit:

```text
POST /api/v1/status
POST /api/v1/imports/cailama/append
POST /api/v1/imports/cailama/reset
POST /api/v1/admin/schema/cailama
POST /api/v1/admin/schema/all
/login.php
/account.php
```

Status-, Import- und Schema-Endpunkte nehmen weder Query-Parameter noch
Nutzdaten im Request-Body an. Gesendet wird nur ein Bearer-Key mit passendem
Scope: `status:read` fuer Status, `db_import:write` fuer Append-Import,
`db_import:reset` fuer Reset-Import oder `admin` fuer Schema-Setup und
Admin-Aktionen. Ohne gueltigen Key liefert die API keine API-, DB-, Schema-
oder Importdetails. Der Import-Modus wird ueber den Pfad gewaehlt: `append`
fuegt erlaubte Insert-Daten in die bestehende CaiLama-Datenbank ein; `reset`
ist nur aktiv, wenn `allow_reset` in der lokalen Konfiguration bewusst gesetzt
wurde.

Grosse Uebertragungen laufen nicht ueber HTTP-Request-Body. Der Dump wird per
SFTP in einen nicht oeffentlich erreichbaren Webspace-Ordner gelegt. Die API
verarbeitet nur den in `config.local.php` fest konfigurierten Dateinamen,
standardmaessig eine `.sql`- oder `.sql.gz`-Datei. Wenn keine Datei vorhanden
ist, wird der Import mit `no_import_file` abgelehnt. Nach erfolgreichem Import
wird die Datei geloescht; ein fehlgeschlagener Cleanup wird als eigener Fehler
gemeldet, damit keine Importdatei versehentlich liegen bleibt.

Die versionierte `web/api_app/config.php` enthaelt sichere Defaults. Die echte
Provider-Konfiguration gehoert nicht in den oeffentlichen Document Root. Auf
dem Webspace liegt `/public` im oeffentlichen Bereich; private Konfiguration
und Import-Drop liegen als Sibling-Ordner im Webspace-Root. Die API sucht aus
`/public/api_app/` zuerst diese private Konfig:

```text
../../cailama-private/api/config.local.php
```

Nur als lokale Legacy-/Fallback-Variante wird noch
`web/api_app/config.local.php` unterstuetzt; diese Datei bleibt gitignoriert und
wird beim Private-Deploy aus dem Public-Webspace entfernt. Als secretfreie
Vorlage dient `web/api_app/config.local.sample.php`.

Das wiederholbare Setup laeuft ueber:

```bash
scripts/generate-web-api-keys.sh
scripts/setup-webspace-db-api.sh --source <private-db-config> --all --allow-reset
```

Nach dem ersten Normalisierungslauf kann das Setup ohne `--source` wiederholt
werden; dann nutzt es die private `databases.ini`. Der Setup-Pfad erzeugt
lokale private Dateien mit restriktiven Rechten unter
`~/.config/cailama`, schreibt die PHP-Konfig fuer den Webspace, legt
MySQL-Defaults fuer lokale Setup-Skripte an und laedt die private PHP-Konfig
per SFTP in einen nicht oeffentlichen Webspace-Ordner. Lokale Schemas werden
mit dem lokalen MySQL-Client angelegt. Provider-Schemas werden bewusst nicht
von lokalem MySQL aus angefasst, sondern ueber die geschuetzten PHP-Endpunkte
im Webspace gesetzt, weil die Provider-DB nur vom Webspace aus erreichbar sein
soll.

Die Konfiguration besteht aus einer einzigen Datenbank:

- `databases.cailama`: Provider-Datenbank fuer Login/Users, Anwendungsdaten
  und Schema-Management (2 GB beim Provider).

Der Login nutzt PHP-Sessions mit `HttpOnly`, `SameSite=Lax`, HTTPS-abhaengigem
Secure-Cookie, CSRF-Token, einfachem Session-basiertem Versuchslimit und
`password_verify()` gegen Passwort-Hashes aus der Auth-Datenbank. Die
SQL-Vorlagen liegen unter `web/api_app/schema/`.

API-Key-Pruefung und Scopes sind als Hash-basierte Bearer-Token-Pruefung
verdrahtet. Es gibt getrennte Keys fuer Status, Append-Import, Reset-Import und
Admin. Auf dem Server liegen nur Hashes; die Klartext-Keys bleiben in privaten
lokalen Client-Konfigdateien. Es gibt keinen oeffentlichen GET-Status mit
DB-Information. Fachliche Read-/Write-Endpunkte jenseits Status und Dump-Import
bleiben Folgearbeit. Keine produktiven Keys, DB-Passwoerter oder
Hoster-Zugangsdaten werden in `web/`, `docs/` oder Beispiele geschrieben.

Aktueller Betriebsstatus am 2026-05-22: lokale Login- und CaiLama-Schemas sind
angelegt. Provider-Schemaanlage und Provider-Verbindungschecks laufen ueber
die Webspace-API. Der IONOS-Login-DB-Eintrag wird ausschliesslich in der
privaten lokalen Konfig und der privaten Webspace-Konfig korrigiert; echte
Host-, User- oder Passwortwerte werden nicht in Doku oder Repo geschrieben.
Live ist `pdo_mysql` verfuegbar, aber die Login-DB meldet noch
`auth_failed`; das fehlende oder abweichende IONOS-Passwort wird ueber eine
private Passwortdatei in die lokale Privatkonfig uebernommen und danach erneut
in den privaten Webspace deployed.

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

Damit ist die Master-Doku sowohl die Human-Version fuer Nutzer als auch die
LLM-freundliche Nachschlagebasis fuer alle CaiLama-Repositories.

Die Webserver-, DNS- und TLS-Konfiguration fuer `cailama.org` liegt ausserhalb
dieses Repositories. Keine Zertifikate, Tokens oder Server-Secrets in dieses
Repo schreiben.
