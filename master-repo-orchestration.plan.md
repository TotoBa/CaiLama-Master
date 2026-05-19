# Plan: CaiLama-Master als Orchestrierungs-Repo aufbauen

Statusannahme: Lokal existiert bereits folgende Struktur:

```text
./CaiLama
./CaiLama-LLM-Router
./CaiLama-Search
./status.plan.cailama.md
```

Wichtig: Das GitHub-Repo `TotoBa/CaiLama-Master` ist über den Connector sichtbar, aber die GitHub-Contents-API meldet es aktuell als leer. Der untenstehende Plan ist deshalb bewusst für den **lokalen Arbeitsstand** formuliert.

Grundlage: Das Master-Repo soll die drei bestehenden CaiLama-Repos nur koordinieren. Die Projektregeln aus `hinweise.md` gelten: CaiLama bleibt Hauptsystem, Router und Search sind eigenständige Dienste, Konfiguration/Credentials gehören nicht ins Repo, und Dienste sollen über klare Schnittstellen gekoppelt werden. 

---

## Ziel

Das Repository `TotoBa/CaiLama-Master` wird als reines Orchestrierungs-, Planungs- und Koordinations-Repo eingerichtet.

Es soll:

1. die drei lokalen Unter-Repos ignorieren,
2. keine fremden `.git`-Verzeichnisse oder Subrepo-Dateien aufnehmen,
3. eine eigene `AGENTS.md` für Codex/Kimi/LLM-Arbeit enthalten,
4. eine eigene `TODO.md` für Ecosystem-weite Aufgaben enthalten,
5. optional minimale Orchestrierungsdateien vorbereiten,
6. keine Secrets, Tokens, lokalen Pfade oder produktiven Credentials committen.

---

## Nicht-Ziele

Codex soll **nicht**:

* Code in `./CaiLama`, `./CaiLama-LLM-Router` oder `./CaiLama-Search` ändern.
* Die drei Unter-Repos als Git-Submodule einrichten.
* Die Unter-Repos committen.
* Lokale `.env`, API-Keys, Meilisearch-Keys, Router-Keys oder persönliche Pfade ins Repo schreiben.
* Bestehende Dateien in den Unter-Repos löschen, verschieben oder normalisieren.
* Das Master-Repo mit produktiver Laufzeitlogik überladen.

---

## Gewünschte Zielstruktur

```text
CaiLama-Master/
├── .gitignore
├── AGENTS.md
├── TODO.md
├── README.md
├── status.plan.cailama.md
├── docs/
│   ├── ecosystem-map.md
│   └── orchestration.md
├── scripts/
│   └── check-ecosystem.sh
├── CaiLama/                 # lokal vorhanden, aber gitignored
├── CaiLama-LLM-Router/      # lokal vorhanden, aber gitignored
└── CaiLama-Search/          # lokal vorhanden, aber gitignored
```

`README.md`, `docs/` und `scripts/check-ecosystem.sh` sind sinnvoll, aber nicht zwingend. `AGENTS.md`, `TODO.md` und `.gitignore` sind Pflicht.

---

## Codex-CLI-Prompt

Diesen Prompt im lokalen Ordner `CaiLama-Master` ausführen:

```text
Du arbeitest im lokalen Repository `TotoBa/CaiLama-Master`.

Dieses Repository ist KEIN Monorepo und KEIN Submodule-Repo. Es dient ausschließlich der Orchestrierung, Koordination und Dokumentation des CaiLama-Ökosystems.

Aktuelle lokale Struktur:
- ./CaiLama
- ./CaiLama-LLM-Router
- ./CaiLama-Search
- ./status.plan.cailama.md

Die drei Unterordner `CaiLama`, `CaiLama-LLM-Router` und `CaiLama-Search` sind eigenständige Git-Repositories. Sie dürfen nicht Bestandteil des Master-Repos werden und müssen vollständig über `.gitignore` ignoriert werden.

Wichtige Projektregeln:
- CaiLama bleibt das Hauptsystem.
- CaiLama-LLM-Router bleibt eigenständiger LLM-Router.
- CaiLama-Search bleibt eigenständiger Such-/RAG-/DWZ-Dienst.
- Das Master-Repo enthält nur Orchestrierung, Pläne, Statusdokumente, allgemeine Skripte und agentenbezogene Arbeitsanweisungen.
- Keine Secrets, Tokens, lokalen Credentials, API-Keys oder privaten Zugangsdaten committen.
- Keine Dateien in den drei Unter-Repos verändern.
- Keine Submodules anlegen.
- Keine produktive Laufzeitlogik im Master-Repo einbauen.
- Bestehende Datei `status.plan.cailama.md` erhalten.

Aufgabe:

1. Prüfe zuerst den aktuellen Git-Status:
   - `git status --short`
   - `git rev-parse --show-toplevel`
   - `find . -maxdepth 2 -name .git -type d`
   - Prüfe, ob die Unter-Repos versehentlich bereits im Index des Master-Repos liegen.

2. Erstelle oder aktualisiere `.gitignore` so, dass mindestens ignoriert werden:
   - `/CaiLama/`
   - `/CaiLama-LLM-Router/`
   - `/CaiLama-Search/`
   - `.env`
   - `.env.*`
   - `!.env.example`
   - lokale Logs
   - lokale Caches
   - Python/Node/Editor/OS-Artefakte
   - temporäre Arbeitsdateien
   - lokale Secrets
   - lokale Datenbanken
   - Meilisearch-Datenverzeichnisse, falls sie im Master-Repo entstehen könnten

3. Falls die Unter-Repos bereits versehentlich getrackt werden, entferne sie nur aus dem Master-Index, ohne lokale Dateien zu löschen:
   - `git rm -r --cached CaiLama CaiLama-LLM-Router CaiLama-Search`
   - Nur ausführen, wenn sie tatsächlich getrackt sind.
   - Niemals `rm -rf` auf diese Ordner anwenden.

4. Erstelle `AGENTS.md` für das Master-Repo.
   Inhaltliche Anforderungen:
   - Zweck des Master-Repos erklären.
   - Explizit sagen, dass Unter-Repos nicht verändert werden dürfen.
   - Arbeitsregeln für Codex/Kimi/LLM-Agenten definieren.
   - Erlaubte Dateien/Ordner nennen.
   - Verbotene Aktionen nennen.
   - Regeln für Statuspflege, TODO-Pflege und Plan-Dateien definieren.
   - Umgang mit Secrets klar regeln.
   - Akzeptanzkriterien für Änderungen nennen.
   - Sprache: Deutsch, präzise, technisch, umsetzungsorientiert.

5. Erstelle `TODO.md` für Ecosystem-weite Aufgaben.
   Inhaltliche Anforderungen:
   - Aufgaben in Phasen gliedern:
     1. Master-Repo-Basis
     2. Orchestrierung und Status
     3. CaiLama Integrationsthemen
     4. CaiLama-Search Integrationsthemen
     5. CaiLama-LLM-Router Integrationsthemen
     6. Qualitätssicherung
     7. Dokumentation
   - Aktuelle laufende Arbeiten aufnehmen:
     - CaiLama: personalisierter Trainingsgenerator
     - CaiLama-Search: Meilisearch-API-Key-Management
   - Aufgaben als Checkboxen formulieren.
   - Keine Aufgaben erfinden, die Änderungen in Unter-Repos direkt im Master-Repo durchführen.
   - Stattdessen Cross-Repo-Aufgaben als Koordinationspunkte formulieren.

6. Erstelle optional `README.md`, falls nicht vorhanden.
   Inhalt:
   - Kurzbeschreibung des Master-Repos.
   - Lokale Ordnerstruktur.
   - Hinweis, dass Unter-Repos ignoriert werden.
   - Empfohlener lokaler Checkout.
   - Verweis auf `AGENTS.md`, `TODO.md` und `status.plan.cailama.md`.

7. Erstelle optional `docs/ecosystem-map.md`.
   Inhalt:
   - Drei-Repos-Architektur.
   - Verantwortlichkeiten je Repo.
   - API-/Schnittstellenrichtung:
     - CaiLama nutzt CaiLama-LLM-Router für LLM-Zugriff.
     - CaiLama nutzt CaiLama-Search für Suche/RAG/DWZ.
     - Router und Search bleiben unabhängig deploybar.
   - Keine Implementierungsdetails erfinden.

8. Erstelle optional `docs/orchestration.md`.
   Inhalt:
   - Wie dieses Master-Repo gepflegt werden soll.
   - Wie Statuspläne aktualisiert werden.
   - Wie Cross-Repo-Aufgaben dokumentiert werden.
   - Wie lokale Unter-Repos geprüft werden können, ohne sie zu committen.

9. Erstelle optional `scripts/check-ecosystem.sh`.
   Anforderungen:
   - Bash-Skript.
   - Kein Schreiben in Unter-Repos.
   - Nur Statusprüfung.
   - Prüft, ob die drei Unterordner existieren.
   - Prüft, ob sie eigene `.git`-Verzeichnisse haben.
   - Gibt je Repo `git status --short` aus, ohne Änderungen vorzunehmen.
   - Prüft, ob die Unter-Repos im Master-Git ignoriert sind.
   - Muss ohne Secrets laufen.
   - Skript ausführbar machen.

10. Führe Abschlussprüfungen aus:
    - `git status --short`
    - `git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search`
    - Falls `scripts/check-ecosystem.sh` existiert: `bash scripts/check-ecosystem.sh`

11. Gib am Ende eine knappe Zusammenfassung aus:
    - Welche Dateien wurden erstellt/geändert?
    - Sind die drei Unter-Repos ignoriert?
    - Wurden versehentlich Unter-Repo-Dateien getrackt?
    - Welche nächsten manuellen Schritte sind sinnvoll?

Akzeptanzkriterien:
- `.gitignore` ignoriert die drei Unter-Repos eindeutig.
- `AGENTS.md` existiert und beschreibt die Master-Repo-Regeln.
- `TODO.md` existiert und enthält Ecosystem-weite Aufgaben.
- `status.plan.cailama.md` bleibt erhalten.
- Keine Secrets wurden erzeugt oder committed.
- Keine Dateien innerhalb der drei Unter-Repos wurden geändert.
- `git status --short` zeigt nur Master-Repo-Dateien.
```

---

## Vorgeschlagener Inhalt für `.gitignore`

```gitignore
# CaiLama ecosystem sub-repositories
# These folders are checked out locally for orchestration only.
# They must never be committed into CaiLama-Master.
 /CaiLama/
 /CaiLama-LLM-Router/
 /CaiLama-Search/

# Environment / secrets
.env
.env.*
!.env.example
*.key
*.pem
*.crt
*.p12
*.pfx
secrets/
.secret/
.local-secrets/
credentials/
tokens/

# Local runtime data
data/
runtime/
storage/
logs/
*.log
*.sqlite
*.sqlite3
*.db
*.db-journal
meili_data/
meilisearch_data/

# Python
__pycache__/
*.py[cod]
.pytest_cache/
.mypy_cache/
.ruff_cache/
.coverage
htmlcov/
.venv/
venv/
env/

# Node / frontend tooling
node_modules/
.npm/
.pnpm-store/
dist/
build/
coverage/

# Editor / OS
.vscode/
.idea/
*.swp
*.swo
.DS_Store
Thumbs.db

# Temporary working files
tmp/
temp/
*.tmp
*.bak
*.orig
*.rej
```

Hinweis: Die führenden Leerzeichen vor `/CaiLama/` etc. sollte Codex entfernen, falls sie beim Kopieren entstehen. Die Einträge müssen exakt als `/CaiLama/`, `/CaiLama-LLM-Router/`, `/CaiLama-Search/` in der Datei stehen.

---

## Vorgeschlagener Inhalt für `AGENTS.md`

```markdown
# AGENTS.md – CaiLama-Master

## Zweck dieses Repositories

`TotoBa/CaiLama-Master` ist das Orchestrierungs- und Koordinations-Repository für das CaiLama-Ökosystem.

Es ist kein Monorepo. Es enthält keinen produktiven Code der Unterprojekte.

Die lokal daneben bzw. darunter liegenden Repositories sind eigenständig:

- `CaiLama` – Hauptsystem für Schachanalyse, Training, Profile, PGN-/Stockfish-/LLM-Workflows und DGT-Integration.
- `CaiLama-LLM-Router` – eigenständiger LLM-Router mit OpenAI-kompatibler API, Modellrouting, Backends und Fallbacks.
- `CaiLama-Search` – eigenständiger Such-/Index-/DWZ-/RAG-Dienst.

Diese drei Unterordner sind lokal hilfreich, werden aber im Master-Repo ignoriert.

## Grundregel

Agenten dürfen im Master-Repo koordinieren, dokumentieren und prüfen.

Agenten dürfen nicht ungefragt Code in den Unter-Repos ändern.

## Erlaubte Bereiche

Agenten dürfen folgende Dateien und Ordner im Master-Repo bearbeiten:

- `.gitignore`
- `README.md`
- `AGENTS.md`
- `TODO.md`
- `status.plan.cailama.md`
- `docs/`
- `scripts/`
- weitere reine Planungs-, Status- und Orchestrierungsdateien

## Verbotene Aktionen

Agenten dürfen nicht:

- Dateien aus `CaiLama/`, `CaiLama-LLM-Router/` oder `CaiLama-Search/` in das Master-Repo committen.
- Die Unter-Repos als Submodules hinzufügen, außer der Nutzer verlangt das ausdrücklich.
- `.git`-Verzeichnisse der Unter-Repos verändern.
- Lokale Secrets, Tokens, API-Keys, Meilisearch-Keys, Router-Keys oder `.env`-Dateien committen.
- produktive Credentials in Dokumentation oder Beispielkonfigurationen schreiben.
- Unter-Repos löschen, verschieben oder automatisch normalisieren.
- produktive Laufzeitlogik ins Master-Repo einbauen.

## Arbeitsweise

Vor Änderungen:

1. `git status --short` ausführen.
2. Prüfen, ob das aktuelle Arbeitsverzeichnis das Master-Repo ist.
3. Prüfen, ob Unter-Repos versehentlich im Master-Index liegen.
4. Bestehende Plan- und Statusdateien lesen.

Bei Änderungen:

1. Nur Master-Repo-Dateien ändern.
2. Änderungen klein und nachvollziehbar halten.
3. Keine lokalen Pfade oder Secrets einbauen.
4. Cross-Repo-Aufgaben als Koordinationspunkte dokumentieren, nicht direkt in Unter-Repos umsetzen.

Nach Änderungen:

1. `git status --short` ausführen.
2. `git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search` ausführen.
3. Prüfen, dass keine Dateien aus den Unter-Repos getrackt werden.
4. Ergebnis knapp zusammenfassen.

## Status- und TODO-Pflege

`TODO.md` enthält Ecosystem-weite Aufgaben.

`status.plan.cailama.md` enthält größere Analyse-, Architektur- oder Roadmap-Stände.

Neue Plan-Dateien sollen klar benannt werden, z. B.:

- `YYYY-MM-DD.master-orchestration.plan.md`
- `YYYY-MM-DD.search-integration.plan.md`
- `YYYY-MM-DD.ptg-roadmap.plan.md`

Abgeschlossene Punkte in `TODO.md` nur abhaken, wenn die Umsetzung überprüft wurde.

## Architekturprinzipien

- CaiLama bleibt das Hauptsystem.
- LLM-Zugriff erfolgt über CaiLama-LLM-Router.
- Suche, DWZ und RAG-Kontext laufen über CaiLama-Search.
- Dienste werden über HTTP/API gekoppelt.
- Konfiguration und Code bleiben getrennt.
- Produktionsdatenhaltung liegt im Hauptsystem nicht im Master-Repo.
- Das Master-Repo ist Koordination, nicht Runtime.

## Akzeptanzkriterien für Master-Repo-Änderungen

Eine Änderung ist nur akzeptabel, wenn:

- keine Unter-Repo-Dateien im Master-Repo getrackt werden,
- keine Secrets enthalten sind,
- die Dokumentation den aktuellen Ecosystem-Stand nicht verfälscht,
- offene Punkte klar als offen markiert sind,
- lokale Annahmen als Annahmen gekennzeichnet sind,
- `git status --short` plausibel ist.
```

---

## Vorgeschlagener Inhalt für `TODO.md`

```markdown
# TODO – CaiLama-Master

Dieses TODO koordiniert Ecosystem-weite Aufgaben. Es ersetzt nicht die TODOs der einzelnen Repositories.

## 1. Master-Repo-Basis

- [ ] `.gitignore` erstellen und Unter-Repos ignorieren:
  - [ ] `/CaiLama/`
  - [ ] `/CaiLama-LLM-Router/`
  - [ ] `/CaiLama-Search/`
- [ ] Prüfen, ob Unter-Repos versehentlich getrackt sind.
- [ ] Falls nötig, Unter-Repos aus dem Master-Index entfernen, ohne lokale Dateien zu löschen.
- [ ] `AGENTS.md` für Master-Repo-Regeln erstellen.
- [ ] `README.md` mit Zweck und lokaler Struktur erstellen.
- [ ] `docs/` für Ecosystem-Dokumentation anlegen.
- [ ] `scripts/check-ecosystem.sh` als reine Statusprüfung ergänzen.

## 2. Orchestrierung und Status

- [ ] `status.plan.cailama.md` als aktuellen Ecosystem-Plan erhalten.
- [ ] Namensschema für neue Plan-Dateien festlegen.
- [ ] Regel definieren, wann Statuspläne aktualisiert werden.
- [ ] Cross-Repo-Aufgaben im Master dokumentieren, aber Umsetzung in den jeweiligen Repos belassen.
- [ ] Übersicht pflegen, welche Themen gerade aktiv sind:
  - [ ] CaiLama: personalisierter Trainingsgenerator.
  - [ ] CaiLama-Search: Meilisearch-API-Key-Management.
  - [ ] CaiLama-LLM-Router: Betriebs-/Fallback-/Backend-Härtung.

## 3. CaiLama Integrationsthemen

- [ ] Status des personalisierten Trainingsgenerators regelmäßig dokumentieren.
- [ ] Schnittstelle CaiLama → CaiLama-Search für Suche/RAG/DWZ als Koordinationsthema beschreiben.
- [ ] Schnittstelle CaiLama → CaiLama-LLM-Router für Modellzugriff als Koordinationsthema beschreiben.
- [ ] Prüfen, welche Runtime-Konfigurationen dokumentiert werden müssen, ohne Secrets offenzulegen.
- [ ] Prüfen, welche Smoke-Tests für das Gesamtsystem sinnvoll sind.

## 4. CaiLama-Search Integrationsthemen

- [ ] Fortschritt des Meilisearch-API-Key-Managements dokumentieren.
- [ ] Offene Punkte für Search-Auth-Hardening sammeln.
- [ ] Einheitliche Env-Namen und Runtime-Konfiguration als Cross-Repo-Thema dokumentieren.
- [ ] Search-API-Endpunkte dokumentieren, die CaiLama nutzen soll:
  - [ ] `/v1/search`
  - [ ] `/v1/context`
  - [ ] `/v1/dwz/search`
  - [ ] `/v1/dwz/player/{pkz}`
- [ ] Prüfen, welche Quellenlisten und Crawler-Policies in CaiLama-Search gepflegt werden sollen.

## 5. CaiLama-LLM-Router Integrationsthemen

- [ ] Modellklassen dokumentieren:
  - [ ] router
  - [ ] small
  - [ ] large
  - [ ] task
- [ ] Prüfen, welche Modell-Aliase CaiLama erwartet.
- [ ] Prüfen, welche Health-/Smoke-Checks aus dem Master heraus dokumentiert werden können.
- [ ] Fallback- und Limit-Verhalten als Betriebsnotiz dokumentieren.
- [ ] Keine lokalen Provider-Secrets im Master speichern.

## 6. Qualitätssicherung

- [ ] Master-Repo-Checkskript ergänzen.
- [ ] Prüfen, dass Unter-Repos ignoriert sind.
- [ ] Prüfen, dass keine `.env`-Dateien getrackt sind.
- [ ] Prüfen, dass keine Secrets in Markdown-Dateien stehen.
- [ ] Prüfen, dass Statusdateien keine falschen Aussagen über Repo-Zustände enthalten.
- [ ] Prüfen, dass alle Cross-Repo-Aufgaben klare Ziel-Repos nennen.

## 7. Dokumentation

- [ ] `docs/ecosystem-map.md` erstellen.
- [ ] `docs/orchestration.md` erstellen.
- [ ] Lokale Setup-Hinweise ohne Secrets dokumentieren.
- [ ] Betriebsrollen der drei Repos klar beschreiben.
- [ ] Historische Namen nur als historische Referenz erwähnen.
- [ ] Dokumentation aktualisieren, wenn Repos umbenannt, verschoben oder neu strukturiert werden.
```

---

## Vorgeschlagener Inhalt für `README.md`

````markdown
# CaiLama-Master

`CaiLama-Master` ist das Orchestrierungs- und Koordinations-Repository für das CaiLama-Ökosystem.

Es ist kein Monorepo und enthält keinen produktiven Code der Unterprojekte.

## Lokale Struktur

Typischer lokaler Checkout:

```text
CaiLama-Master/
├── CaiLama/
├── CaiLama-LLM-Router/
├── CaiLama-Search/
├── status.plan.cailama.md
├── AGENTS.md
├── TODO.md
└── docs/
````

Die drei Unterordner sind eigenständige Git-Repositories und werden im Master-Repo ignoriert.

## Repositories

* `TotoBa/CaiLama` – Hauptsystem.
* `TotoBa/CaiLama-LLM-Router` – LLM-Router.
* `TotoBa/CaiLama-Search` – Suche, DWZ, RAG-Kontext.

## Zweck

Dieses Repo dient dazu:

* Ecosystem-weite Pläne zu speichern,
* Cross-Repo-Aufgaben zu koordinieren,
* Statusstände festzuhalten,
* Agentenregeln zu definieren,
* lokale Orchestrierungsprüfungen bereitzustellen.

## Wichtige Dateien

* `AGENTS.md` – Regeln für Codex/Kimi/LLM-Agenten.
* `TODO.md` – Ecosystem-weite Aufgaben.
* `status.plan.cailama.md` – aktueller Status-/Roadmap-Plan.
* `docs/` – Architektur- und Orchestrierungsdokumentation.
* `scripts/` – lokale Prüfskripte ohne Secrets.

## Sicherheitsregel

Keine Secrets, Tokens, API-Keys, lokalen `.env`-Dateien oder privaten Zugangsdaten in dieses Repository committen.

````

---

## Vorgeschlagenes `scripts/check-ecosystem.sh`

```bash
#!/usr/bin/env bash
set -euo pipefail

repos=(
  "CaiLama"
  "CaiLama-LLM-Router"
  "CaiLama-Search"
)

echo "== CaiLama-Master ecosystem check =="
echo

echo "-- Master repository --"
git rev-parse --show-toplevel
git status --short
echo

echo "-- Ignore checks --"
for repo in "${repos[@]}"; do
  if git check-ignore -q "$repo"; then
    echo "OK: $repo is ignored by master .gitignore"
    git check-ignore -v "$repo" || true
  else
    echo "WARN: $repo is NOT ignored by master .gitignore"
  fi
done
echo

echo "-- Sub-repository checks --"
for repo in "${repos[@]}"; do
  echo
  echo "## $repo"

  if [[ ! -d "$repo" ]]; then
    echo "MISSING: directory does not exist"
    continue
  fi

  if [[ ! -d "$repo/.git" ]]; then
    echo "WARN: directory exists but has no .git folder"
    continue
  fi

  echo "OK: own .git directory found"
  git -C "$repo" status --short
done

echo
echo "Done."
````

---

## Manuelle Befehle nach Codex-Lauf

Nach der Umsetzung:

```bash
git status --short
git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search
```

Falls die Unter-Repos bereits versehentlich getrackt sind:

```bash
git rm -r --cached CaiLama CaiLama-LLM-Router CaiLama-Search
git status --short
```

Dann committen:

```bash
git add .gitignore AGENTS.md TODO.md README.md docs scripts status.plan.cailama.md
git commit -m "Set up CaiLama master orchestration repo"
git push
```

Nur `status.plan.cailama.md` adden, wenn diese Datei wirklich ins Master-Repo soll und keine privaten Inhalte/Secrets enthält.

---

## Bewertung

Das Master-Repo ist sinnvoll. Aber es sollte strikt **leichtgewichtig** bleiben. Sein Wert liegt nicht darin, eine vierte Codebasis zu werden, sondern darin, den Überblick über die drei echten Systeme zu halten:

* **CaiLama**: Produktkern und Training.
* **CaiLama-LLM-Router**: Modellzugriff und Fallbacks.
* **CaiLama-Search**: Suche, DWZ, RAG und Quellen.
* **CaiLama-Master**: Koordination, Status, Pläne, Orchestrierungschecks.

Wichtigster technischer Punkt: Die Unter-Repos müssen konsequent ignoriert bleiben. Sonst wird aus dem Master-Repo versehentlich ein kaputtes Pseudo-Monorepo.
