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

Die Webspace-API liest echte DB-Zugaenge, API-Token-Hashes und das
Import-Drop-Verzeichnis aus einer privaten Konfig ausserhalb des oeffentlichen
Document Roots. Auf dem Webspace ist `/public` der oeffentliche Bereich; echte
Konfig und Import-Drop liegen im privaten Webspace-Root, zum Beispiel
`/cailama-private/api/config.local.php` und `/cailama-imports`. Aus
`/public/api_app/` werden diese Pfade relativ ueber `../../...` erreicht.
Grosse CaiLama-Dumps werden per SFTP in den privaten Import-Ordner gelegt und
danach per no-query/no-body-Import-Endpunkt verarbeitet.

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
