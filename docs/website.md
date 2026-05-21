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
Doku festgeschrieben. Das Deployment-Skript kann lokal mit einem expliziten
Zielpfad aufgerufen werden.

## Reproduzierbares Deployment

Die Website hat keinen Build-Schritt. Deployment ist ein synchronisierter
Kopiervorgang von `web/` in den PHP-Webspace.

Standardbefehl:

```bash
scripts/deploy-website.sh
```

Expliziter Zielpfad:

```bash
scripts/deploy-website.sh <webspace-public-dir>
```

Das Skript:

1. ermittelt das Git-Root,
2. synchronisiert `web/` in den angegebenen PHP-Webspace,
3. entfernt dort Dateien, die nicht mehr in `web/` existieren,
4. schuetzt die echte, ignorierte `web/api_app/config.local.php` vor Loeschung,
5. vergleicht jede ausgelieferte versionierte Datei bytegenau mit der Quelle.

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
Stand stellt Struktur, neutralen Status, Login-/Session-Shell und zwei getrennte
PDO-Konfigurationen bereit:

```text
/api/v1/status
/login.php
/account.php
```

Die versionierte `web/api_app/config.php` enthaelt sichere Defaults. Die echte
Provider-Konfiguration gehoert in `web/api_app/config.local.php` und bleibt
gitignoriert. Als Vorlage dient:

```text
web/api_app/config.local.sample.php
```

Die Konfiguration trennt:

- `databases.auth`: Provider-Datenbank fuer Website-Login und Sessions.
- `databases.cailama`: separate CaiLama-Datenbank fuer spaetere Fachlogik.

Der Login nutzt PHP-Sessions mit `HttpOnly`, `SameSite=Lax`, HTTPS-abhaengigem
Secure-Cookie, CSRF-Token, einfachem Session-basiertem Versuchslimit und
`password_verify()` gegen Passwort-Hashes aus der Auth-Datenbank. Die
SQL-Vorlagen liegen unter `web/api_app/schema/`.

API-Key-Pruefung, Scopes, Rate-Limits und fachliche Read-/Write-Endpunkte
werden erst mit lokaler Hosting-Konfiguration und separater Secret-Datei
verdrahtet. Keine produktiven Keys, DB-Passwoerter oder Hoster-Zugangsdaten
werden in `web/`, `docs/` oder Beispiele geschrieben.

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
