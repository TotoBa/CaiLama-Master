# CaiLama-Master

<p align="center">
  <img src="https://raw.githubusercontent.com/TotoBa/CaiLama/main/img/logo-big.png" alt="CaiLama Logo" width="600">
</p>

`CaiLama-Master` ist das Orchestrierungs- und Koordinations-Repository fuer das
CaiLama-Oekosystem.

Webseite: <https://cailama.org/>

Human-/LLM-Referenz:

- <https://cailama.org/reference.php>
- <https://cailama.org/status.php>
- <https://cailama.org/llms.txt>
- <https://cailama.org/ecosystem-reference.md>
- <https://cailama.org/data/ecosystem.json>
- <https://cailama.org/robots.txt>
- <https://cailama.org/sitemap.xml>

Dieses Repository ist kein Monorepo und enthaelt keinen produktiven Code der
Unterprojekte.

## Lokale Struktur

Typischer lokaler Checkout:

```text
CaiLama-Master/
├── CaiLama/
├── CaiLama-LLM-Router/
├── CaiLama-Search/
├── master-repo-orchestration.plan.md
├── status.plan.cailama.md
├── AGENTS.md
├── TODO.md
├── docs/
├── scripts/
├── web-smarty/
└── web/
```

Die drei Unterordner sind eigenstaendige Git-Repositories und werden im
Master-Repo ignoriert:

- `/CaiLama/`
- `/CaiLama-LLM-Router/`
- `/CaiLama-Search/`

Es werden keine Submodules verwendet.

## Repositories

- [`TotoBa/CaiLama-Master`](https://github.com/TotoBa/CaiLama-Master) -
  Koordination, Status, Plaene und lokale Orchestrierungspruefungen.
- [`TotoBa/CaiLama`](https://github.com/TotoBa/CaiLama) - Hauptsystem fuer
  Schachanalyse, Training, Profile, Agent-CLI und produktnahe Workflows.
- [`TotoBa/CaiLama-LLM-Router`](https://github.com/TotoBa/CaiLama-LLM-Router) -
  OpenAI-kompatibler LLM-Router fuer Modellzugriff, Aliase, Backends und
  Fallbacks.
- [`TotoBa/CaiLama-Search`](https://github.com/TotoBa/CaiLama-Search) - Such-,
  Index-, DWZ- und RAG-Dienst auf Basis von Meilisearch und FastAPI.

## Zweck

Dieses Repo dient dazu:

- Ecosystem-weite Plaene zu speichern,
- Cross-Repo-Aufgaben zu koordinieren,
- Statusstaende festzuhalten,
- Agentenregeln fuer Codex, Kimi und andere LLM-Agenten zu definieren,
- lokale Orchestrierungspruefungen bereitzustellen.

## Wichtige Dateien

- `AGENTS.md` - Regeln fuer Codex/Kimi/LLM-Agenten im Master-Repo.
- `hinweise.md` - allgemeine Projekthinweise fuer ChatGPT-Kontexte; keine
  operativen Codex-Anweisungen.
- `TODO.md` - Ecosystem-weite Aufgaben.
- `master-repo-orchestration.plan.md` - Plan fuer den Aufbau dieses
  Master-Repos.
- `status.plan.cailama.md` - aktueller Status- und Ausbaupfad des
  CaiLama-Oekosystems.
- `docs/ecosystem-map.md` - Architekturkarte und Verantwortlichkeiten.
- `docs/ecosystem-reference.md` - LLM-freundliche Gesamtreferenz des
  Oekosystems.
- `docs/data/ecosystem.json` - maschinenlesbare Struktur der Repos,
  Schnittstellen und Roadmap.
- `docs/orchestration.md` - Pflege- und Arbeitsregeln fuer das Master-Repo.
- `docs/local-setup.md` - lokaler Checkout, Webspace und Konfiguration ohne
  Secrets.
- `docs/integrations.md` - Cross-Repo-Schnittstellen, Rollen, Endpunkte und
  Smoke-Test-Grenzen.
- `docs/product-positioning.md` - Trainingsfokus: Trainingswerkstatt,
  PGN-zu-Trainingsaufgabe-Loop, Zielgruppe und Qualitaetsgrenzen.
- `docs/benchmarks.md` - Master-Rahmen fuer repo-uebergreifende Benchmarks
  ohne Secrets oder private Rohdaten.
- `docs/benchmark-results/README.md` - Ablage- und Formatregeln fuer spaetere
  Master-Benchmark-Ergebnisse.
- `docs/roadmap.md` - Roadmap aus `status.plan.cailama.md` als pflegbare
  Master-Sicht.
- `docs/quality.md` - Master-Checks, Index-Regeln und TODO-Konsistenz.
- `docs/website.md` - URL, Quellpfad und Deployment-Pfad der Webseite.
- `skills/kimi-cli-cailama-ecosystem/SKILL.md` - Kimi-CLI-Skill fuer
  repo-uebergreifenden Kontext aus Website und Online-Doku ohne Secret-Zugriff.
- `web/` - oeffentlicher PHP-Document-Root fuer `https://cailama.org/`;
  die Dokumentationsseiten sind duenne Controller, statische LLM-/JSON-
  Referenzen und die vorbereitete Webspace-API-Fassade.
- `web-smarty/` - private CaiLama-Website-Schicht mit Bootstrap, Content-Daten
  und Smarty-Templates. Die benoetigte Dependency `smarty/smarty ^5.0` liegt
  nur lokal/deployt unter dem ignorierten `web-smarty/vendor/`, nicht im Git.
- `web/api_app/config.local.sample.php` - Vorlage fuer die echte, ignorierte
  Webspace-Konfiguration im Single-Database-Mode mit einer `cailama`-
  Provider-Datenbank, API-Token-Hash und Import-Drop-Konfiguration.
- `scripts/check-ecosystem.sh` - lokale Statuspruefung ohne Schreibzugriffe.
- `scripts/deploy-website.sh` - reproduzierbares Deployment von `web/` nach
  `public/` und `web-smarty/` in den privaten `smarty/`-Sibling.
- `scripts/generate-web-api-keys.sh` - idempotenter Generator fuer private
  Webspace-API-Keys ohne Ausgabe der Key-Werte.
- `scripts/setup-webspace-db-api.sh` - wiederholbares Setup fuer private
  DB-Konfiguration, Webspace-API-Konfig, SFTP-Deploy der privaten Konfig,
  lokale DB-Schemaanlage und providerseitige Schemaanlage ueber die PHP-API.

## Sicherheitsregel

Keine Secrets, Tokens, API-Keys, lokalen `.env`-Dateien oder privaten
Zugangsdaten in dieses Repository committen.
