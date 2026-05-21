# TODO - CaiLama-Master

Dieses TODO koordiniert Ecosystem-weite Aufgaben. Es ersetzt nicht die TODOs der
einzelnen Repositories und verlangt keine direkten Code-Aenderungen in den
Unter-Repos.

Erledigte TODOs werden nur auf ausdrueckliche Nutzeranweisung entfernt; TODO
ist nicht gleich Handoff. Diese Bereinigung wurde am 2026-05-20 auf
ausdrueckliche Nutzeranweisung durchgefuehrt.

## Arbeitskontext

Vor Arbeitsbeginn lesen:

- `AGENTS.md`, `README.md`, diese `TODO.md`.
- `docs/ecosystem-map.md`, `docs/orchestration.md`,
  `status.plan.cailama.md`, `master-repo-orchestration.plan.md`.
- Bei Website-Aenderungen zusaetzlich `docs/website.md`,
  `docs/ecosystem-reference.md`, `docs/data/ecosystem.json` und `web/`.

## Naechster Arbeitsschritt

- [x] Googlebot-Grundlage fuer die Website bereitstellen: `robots.txt`,
  `sitemap.xml`, kanonische URLs, `noindex` fuer Login-/Konto-Seiten,
  Deployment- und Check-Dokumentation. Indexierungsanstoß erfolgt ueber
  Sitemap-Verweis in `robots.txt`; Search-Console-Einreichung bleibt ein
  manueller Schritt mit verifizierter Property.
- [ ] CaiLama-DB-Hybrid koordinieren: native MariaDB/MySQL, fachliche Webspace-
  API und Hybridbetrieb in Master-Doku/Website nachziehen, sobald die
  Umsetzung in `TotoBa/CaiLama` beginnt. Master-Seite: Login-/Session-Shell,
  getrennte `auth`-/`cailama`-PDO-Konfiguration und Schema-Vorlagen sind
  vorbereitet; echte Provider-Credentials bleiben in ignorierter
  `web/api_app/config.local.php`.
- [ ] CaiLama-Search-Vertrag weiter pruefen: `POST /v1/search`,
  kompatibles `GET /v1/search`, `POST /v1/context`, `items`/`results`,
  `context`/`sources` und DWZ-Endpunkte in Doku und Website synchron halten.
- [ ] Kimi-CLI-Ecosystem-Skill nach erstem realen Kimi-Lauf pruefen:
  `skills/kimi-cli-cailama-ecosystem/SKILL.md` bei Bedarf schaerfen, ohne
  lokale Secrets oder Runtime-Pfade aufzunehmen. Der Skill ist lokal in den
  Kimi-Skills-Ordner deployt; die versionierte Quelle bleibt der Master-Stand.
- [ ] Runtime-Aktualisierung nach groesseren Unterrepo-Releases pruefen:
  `scripts/update-runtime-projects.sh` fuer Router/Search/CaiLama nutzen und
  dokumentieren, ob Dienste aus Runtime-Ordnern gestartet wurden.
- [ ] Roadmap regelmaessig aus den Unterrepo-`TODO.md`-Dateien abgleichen:
  CaiLama = DB-Hybrid, PTG-Live-Verifikation, DWZ-Identity-Linking,
  RAG-Provenienz; Router = aktuell keine aktive Folgearbeit ohne neuen
  Nutzerauftrag; Search = aktueller Ausbau-Fokus mit Goldsets,
  Job-Orchestrierung, API-Qualitaet und semantischem Retrieval nach erledigter
  Crawler-Quellenpolitik und Observability-Grundlage.

## Kimi-Handoff

Der Master bleibt Koordination, Website und Doku. Keine Unterrepo-Dateien im
Master tracken, keine Submodules, keine produktive Runtime-Logik.

```text
Du arbeitest im CaiLama-Master-Repository. Lies zuerst AGENTS.md, README.md und
TODO.md vollstaendig. Lies danach docs/ecosystem-map.md, docs/orchestration.md,
status.plan.cailama.md und master-repo-orchestration.plan.md. Wenn die Aufgabe
Website oder LLM-Doku betrifft, lies zusaetzlich docs/website.md,
docs/ecosystem-reference.md, docs/data/ecosystem.json und die betroffenen
Dateien unter web/.

Arbeite danach ausschliesslich den ersten offenen Punkt in TODO.md ab. Keine
Secrets, lokalen Pfade oder produktiven Zugangsdaten aufnehmen. Keine
Unterrepo-Dateien im Master committen. Erledigte TODO-Punkte nicht loeschen,
ausser der Nutzer fordert diese Bereinigung ausdruecklich an. TODO ist nicht
gleich Handoff.

Nach jeder Aenderung:
1. Betroffene Master-Doku oder Website knapp aktualisieren.
2. git diff --check ausfuehren.
3. git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search ausfuehren.
4. bash scripts/check-ecosystem.sh ausfuehren.
5. git status --short ausfuehren.
6. Commit und Push im Master-Repository ausfuehren, wenn der Nutzer das
   verlangt.
```

## Master-Arbeitsregeln

- [ ] Vor Arbeitsbeginn die Dateien aus "Arbeitskontext" lesen.
- [ ] Unter-Repos bleiben eigenstaendige Git-Repositories und im Master
  ignoriert.
- [ ] Keine Prompt- oder Handoff-Dateien ausserhalb von `TODO.md` anlegen;
  groessere Konzepte duerfen als klar benannte `*.plan.md` abgelegt werden.
- [ ] Keine Secrets, Tokens, `.env`, lokalen Service-Dateien oder produktiven
  Credentials in Doku, Website, Skripte oder Beispiele schreiben.
- [ ] Abschlusspruefung ausfuehren:
  `git status --short`,
  `git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search`,
  `bash scripts/check-ecosystem.sh`,
  `git diff --check`.
