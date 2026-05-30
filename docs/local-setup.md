# Local Setup

Dieses Dokument beschreibt den lokalen Checkout ohne Secrets. Es ersetzt keine
Runtime-Konfiguration der Unter-Repositories.

## Repository-Layout

Empfohlene lokale Struktur:

```text
CaiLama-Master/
├── CaiLama/
├── CaiLama-LLM-Router/
├── CaiLama-Search/
├── docs/
├── scripts/
└── web/
```

Die drei Unterordner sind eigenstaendige Git-Repositories. Sie werden im
Master-Repo ignoriert und duerfen nicht als Submodule eingetragen werden.

## Webspace

Die statische Webseite liegt versioniert unter:

```text
web/
```

Der Live-Webspace-Pfad ist host-spezifisch und wird nicht in der offiziellen
Doku festgeschrieben.

Deployment:

```bash
scripts/deploy-website.sh
```

Das Skript nutzt im Live-Betrieb natives SFTP. Die Zielkonfiguration liegt
lokal ausserhalb des Repos, zum Beispiel in
`~/.config/cailama/web-deploy.env`, oder wird per Umgebungsvariablen gesetzt.
Fuer lokale Tests kann ein lokaler Zielpfad uebergeben werden:

```bash
scripts/deploy-website.sh <local-public-dir>
```

Deployt wird der komplette Inhalt von `web/`, also PHP-Seiten, Stylesheet,
`llms.txt`, `ecosystem-reference.md` und `data/ecosystem.json`.

Die URL `https://cailama.org/` wurde am 2026-05-20 per `curl -I -L` mit
`HTTP/2 200` verifiziert.

Die Webspace-API liest echte DB-Zugaenge, API-Token-Hashes,
Origin-Proxy-Keys und das Import-Drop-Verzeichnis aus einer privaten Konfig
ausserhalb des oeffentlichen Document Roots. Die versionierte Doku beschreibt
nur die konfigurierbaren Felder und Sicherheitsanforderungen, keine echten
Serverpfade, Hosts, Nutzerkonten oder Zugangsdaten. Grosse CaiLama-Dumps
werden per SFTP in einen nicht oeffentlich erreichbaren Import-Ordner gelegt
und danach per no-query/no-body-Import-Endpunkt verarbeitet.

Provider-Datenbanken werden nicht direkt vom lokalen Rechner aus eingerichtet.
Das Setup-Skript legt lokale Schemas mit dem lokalen MySQL-Client an; Provider-
Schemas setzt es ueber geschuetzte `POST /api/v1/admin/schema/...`-Endpunkte
in der PHP-API, weil die Provider-DB nur vom Webspace aus bearbeitet werden
soll.

Wiederholbare Hilfen:

```bash
scripts/generate-web-api-keys.sh
scripts/setup-webspace-db-api.sh --source <private-db-config> --all --allow-reset
scripts/setup-webspace-db-api.sh --set-provider-password-file <private-password-file> --write-configs --deploy-private
scripts/setup-webspace-db-api.sh --setup-databases all
```

Nach dem ersten Normalisierungslauf kann `setup-webspace-db-api.sh --all` die
private `databases.ini` wiederverwenden. Die Skripte schreiben echte Secrets
nur in private lokale Konfigdateien und in die private Webspace-Konfiguration.
Versionierte Dateien enthalten nur Struktur, Variablennamen und Platzhalter.

## Runtime-Ordner

Laufende Dienste und testbare Kopien werden ausserhalb des Master-Repos in
separaten Runtime-Ordnern gehalten. Details stehen in
`docs/runtime-projects.md`.

```bash
scripts/update-runtime-projects.sh all
```

## Konsolen-Client und Server-Origin

Der lokale Konsolen-Client spricht die oeffentliche Webspace-API per HTTPS an.
Die Webspace-API prueft profilgebundene Konsolen-Keys und leitet erlaubte
Requests an einen separat konfigurierten Origin-Dienst weiter. Dieser Origin
wird in privater Konfiguration ueber folgende Felder beschrieben:

- `origin.base_url`: HTTPS-Basis-URL des Origin-Dienstes.
- `origin.proxy_key`: shared Proxy-Key fuer den Webspace-Origin-Hop.
- `origin.hmac_secret`: HMAC-Secret fuer signierte Origin-Requests.
- `origin.timeout_seconds`: begrenzte Wartezeit fuer synchrone Proxy-Requests.

Versionierte Dateien enthalten dafuer nur Platzhalter. Echte Werte gehoeren in
private Operator-Konfigurationen ausserhalb des Repos.

Die aktuellen Konsolen-Endpunkte sind:

```text
POST /api/v1/console/search/query
POST /api/v1/console/llm/chat
POST /api/v1/console/jobs
```

PGN-Analyse ist als asynchroner Job zu behandeln: Die Konsole startet den Job,
der Origin verarbeitet die Analyse, und der Status beziehungsweise das Ergebnis
wird profilgebunden abrufbar. Wenn die Konsole zwischenzeitlich beendet wurde,
muss ein neuer Start ueber denselben Profil-Key offene oder abgeschlossene Jobs
erkennen koennen.

## Konfiguration

- Keine lokalen `.env`-Dateien im Master-Repo versionieren.
- Keine Tokens, API-Keys, Passwoerter, Zertifikate oder private Pfade
  dokumentieren.
- `.env.example` ist nur erlaubt, wenn es keine echten Secrets enthaelt.
- Produktive Runtime-Konfiguration gehoert in die jeweiligen Unter-Repos oder
  in lokale Betriebsumgebungen, nicht in `CaiLama-Master`.

## Lokale Pruefung

```bash
git status --short
git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search prompt.md
bash scripts/check-ecosystem.sh
```
