# Webseite

Die oeffentliche CaiLama-Webseite ist fuer folgende URL vorgesehen:

```text
https://cailama.org/
```

## Ziel

Die Webseite ist die Human-Version der Master-Gesamtdokumentation. Zusaetzlich
werden LLM-freundliche und maschinenlesbare Dateien ausgeliefert.

## Versionierte Quellen

Die Website liegt vollstaendig im Master-Repo unter:

```text
web/
```

Wichtige Dateien:

```text
web/index.html                 # Startseite
web/projects.html              # Projekt- und Repo-Details
web/architecture.html          # Architektur und Schnittstellen
web/roadmap.html               # Roadmap aus status.plan.cailama.md
web/operations.html            # Betrieb, Checks, Deployment
web/reference.html             # Human-/LLM-Referenzseite
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

## Lokaler Webspace

Der aktuell vorgesehene lokale Webspace ist:

```text
/srv/cailama-web/public
```

## Reproduzierbares Deployment

Die Website hat keinen Build-Schritt. Deployment ist ein synchronisierter
Kopiervorgang von `web/` in den Webspace.

Standardbefehl:

```bash
scripts/deploy-website.sh
```

Expliziter Zielpfad:

```bash
scripts/deploy-website.sh /srv/cailama-web/public
```

Das Skript:

1. ermittelt das Git-Root,
2. synchronisiert `web/` nach `/srv/cailama-web/public`,
3. entfernt dort Dateien, die nicht mehr in `web/` existieren,
4. vergleicht jede ausgelieferte Datei bytegenau mit der Quelle.

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
- `https://cailama.org/llms.txt` ist erreichbar.
- `https://cailama.org/ecosystem-reference.md` ist erreichbar.
- `https://cailama.org/data/ecosystem.json` ist erreichbar und valides JSON.

## Unterprojekte

Die Unterprojekte verweisen in ihren `README.md` auf die gemeinsame
Ecosystem-Doku:

```text
https://cailama.org/reference.html
https://cailama.org/llms.txt
https://cailama.org/ecosystem-reference.md
https://cailama.org/data/ecosystem.json
```

Damit ist die Master-Doku sowohl die Human-Version fuer Nutzer als auch die
LLM-freundliche Nachschlagebasis fuer alle CaiLama-Repositories.

Die Webserver-, DNS- und TLS-Konfiguration fuer `cailama.org` liegt ausserhalb
dieses Repositories. Keine Zertifikate, Tokens oder Server-Secrets in dieses
Repo schreiben.
