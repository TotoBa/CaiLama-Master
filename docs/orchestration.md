# Orchestration

Dieses Dokument beschreibt, wie `TotoBa/CaiLama-Master` gepflegt wird.

## Pflege des Master-Repos

Das Master-Repo enthaelt nur Koordination, Status, Plaene, Agentenregeln,
Dokumentation und lokale Pruefskripte. Es darf nicht zum Monorepo werden.

Vor jeder Aenderung im Master-Repo:

```bash
pwd
git rev-parse --show-toplevel
git status --short
find . -maxdepth 2 -name .git -type d
git ls-files | grep -E '^(CaiLama|CaiLama-LLM-Router|CaiLama-Search)/' || true
```

Wenn Unter-Repo-Dateien versehentlich im Master-Index liegen, werden sie nur aus
dem Index entfernt:

```bash
git rm -r --cached CaiLama CaiLama-LLM-Router CaiLama-Search
```

Die lokalen Ordner duerfen dabei nicht geloescht werden.

## Umgang mit Plan-Dateien

- `master-repo-orchestration.plan.md` beschreibt den Aufbau und die Regeln des
  Master-Repos.
- `status.plan.cailama.md` beschreibt den aktuellen Stand und Ausbaupfade des
  Oekosystems.
- `hinweise.md` enthaelt allgemeine Projekthinweise fuer ChatGPT-Kontexte. Fuer
  Codex-Arbeit gelten weiterhin `AGENTS.md`, `TODO.md` und die
  aufgabenspezifischen Nutzeranweisungen.
- Neue Plaene muessen klar benannt und als Plan, Status oder Handoff erkennbar
  sein.
- Plan-Dateien duerfen keine Secrets, lokalen Zugangsdaten oder privaten
  Credential-Werte enthalten.
- Namensschema fuer neue Plaene:
  `YYYY-MM-DD.<thema>.plan.md`, zum Beispiel
  `2026-05-19.search-integration.plan.md`.
- Statusplaene werden aktualisiert, wenn sich Repo-Rollen, Schnittstellen,
  aktive Integrationsarbeiten oder belastbare Pruefergebnisse wesentlich
  aendern.

## Umgang mit Cross-Repo-Aufgaben

Cross-Repo-Aufgaben werden im Master als Koordinationspunkte beschrieben. Die
Umsetzung erfolgt im jeweiligen Ziel-Repo:

- CaiLama-Themen in `TotoBa/CaiLama`.
- Router-Themen in `TotoBa/CaiLama-LLM-Router`.
- Search-Themen in `TotoBa/CaiLama-Search`.

Master-TODOs sollen deshalb Ziel-Repo, Schnittstelle und erwartetes Ergebnis
nennen, aber keine direkten Code-Aenderungen in Unter-Repos verlangen.

Die aktuelle Roadmap steht in `docs/roadmap.md`. Schnittstellen- und
Smoke-Test-Regeln stehen in `docs/integrations.md`.

## Statuspruefung der Unter-Repos

Das Skript `scripts/check-ecosystem.sh` prueft lokal:

- ob `CaiLama`, `CaiLama-LLM-Router` und `CaiLama-Search` existieren,
- ob sie eigene `.git`-Verzeichnisse haben,
- welchen `git status --short` jedes Unter-Repo hat,
- ob die drei Ordner im Master-Repo ignoriert werden.
- ob die Human-/LLM-Webquellen vorhanden sind,
- ob `docs/ecosystem-reference.md` und `web/ecosystem-reference.md`
  identisch sind,
- ob `docs/data/ecosystem.json` und `web/data/ecosystem.json` identisch sind,
- ob der lokale Webspace `/srv/cailama-web/public` den Dateien unter `web/`
  entspricht, sofern der Webspace existiert.

Das Skript nimmt keine Aenderungen vor.

## Lokale, nicht versionierte Konfiguration

Lokale Konfiguration bleibt ausserhalb des Master-Repos:

- `.env` und `.env.*` werden ignoriert.
- `.env.example` darf versioniert werden, wenn es keine echten Secrets
  enthaelt.
- API-Keys, Tokens, Meilisearch-Keys, Router-Keys und private Credentials
  werden nicht dokumentiert und nicht committed.
- Lokale Datenbanken, Logs, Caches und Meilisearch-Datenverzeichnisse werden
  ignoriert.

## Qualitaet

Die Master-Checks und Akzeptanzkriterien stehen in `docs/quality.md`.
