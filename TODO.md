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
- [x] CaiLama-DB-Hybrid koordinieren: native MariaDB/MySQL, fachliche Webspace-
  API und Hybridbetrieb in Master-Doku/Website nachziehen, sobald die
  Umsetzung in `TotoBa/CaiLama` beginnt. Master-Seite: Login-/Session-Shell,
  getrennte `auth`-/`cailama`-PDO-Konfiguration und Schema-Vorlagen sind
  vorbereitet; echte Provider-Credentials bleiben in ignorierter
  privater Webspace-Konfiguration ausserhalb von `/public`. `TotoBa/CaiLama`
  konfiguriert jetzt
  `database.access_mode = native|api|hybrid`, API-Metadaten ohne Secrets und
  einen begrenzten DB-API-Statusclient per geschuetztem `POST /api/v1/status`.
  Die Webspace-API stellt jetzt no-query/no-body-Import-Endpunkte fuer
  serverseitig hochgeladene `.sql`-/`.sql.gz`-Dumps bereit; fehlende
  Importdateien werden abgelehnt und erfolgreich verarbeitete Dateien
  geloescht. Provider-Schemas werden ueber admin-geschuetzte PHP-Endpunkte im
  Webspace gesetzt, weil die Provider-DBs nur von dort bearbeitet werden
  sollen. Private Webspace-Konfig und API-Keys liegen ausserhalb des
  Public-Webroots; lokale DB-Schemas sind angelegt. Offen bleibt die
  Live-Verifikation beider Provider-DB-Verbindungen nach Private-Config-Deploy
  und Schema-Setup.
- [ ] Webspace-DB-API live-testfaehig fertigstellen: private Provider-Konfig
  deployen, Provider-Schemas ueber `POST /api/v1/admin/schema/auth` und
  `POST /api/v1/admin/schema/cailama` setzen, `POST /api/v1/status` nur mit
  Bearer-Key pruefen, **no-key/no-body/no-file-Import-Smokes in
  `docs/integrations.md` dokumentiert**, danach die minimalen fachlichen
  CaiLama-Read-/Write-Endpunkte als naechsten Kimi-Schritt schneiden. Keine
  grossen Daten ueber API-Body; Bulk-Import bleibt SFTP in den privaten
  Webspace-Root plus geschuetzter Import-Endpunkt.
  Live-Stand 2026-05-22: API-Code und private Konfig sind deployed,
  `pdo_mysql` ist verfuegbar, Login-DB meldet `auth_failed`; das IONOS-
  Passwort muss sicher per privater Passwortdatei nachgezogen werden, bevor
  Provider-Schema-Setup erfolgreich sein kann.
- [ ] CaiLama-Search-Vertrag weiter pruefen: `POST /v1/search`,
  kompatibles `GET /v1/search`, `POST /v1/context`, `items`/`results`,
  `context`/`sources` und DWZ-Endpunkte **in `docs/integrations.md` als
  Response-Vertrag ergaenzt**; Website (`ecosystem-reference.md` und
  `ecosystem.json`) sind synchron.
- [ ] Kimi-CLI-Ecosystem-Skill nach erstem realen Kimi-Lauf geprueft und
  geschaerft: `skills/kimi-cli-cailama-ecosystem/SKILL.md` enthaelt jetzt
  explizite Initialisierungspruefungen (`pwd`, `git rev-parse`,
  `git status --short`, `git check-ignore`) und Abschlusspruefungen
  (`git diff --check`, `bash scripts/check-ecosystem.sh`) im Working Loop.
  Keine Secrets oder Runtime-Pfade.
- [ ] Runtime-Aktualisierung nach groesseren Unterrepo-Releases pruefen:
  `scripts/update-runtime-projects.sh` fuer Router/Search/CaiLama nutzen und
  dokumentieren, ob Dienste aus Runtime-Ordnern gestartet wurden.
- [ ] Roadmap regelmaessig aus den Unterrepo-`TODO.md`-Dateien abgleichen:
  CaiLama = DB-Hybrid, PTG-Live-Verifikation, DWZ-Identity-Linking,
  RAG-Provenienz; Router = aktuell keine aktive Folgearbeit ohne neuen
  Nutzerauftrag; Search = aktueller Ausbau-Fokus mit Goldset-E2E-Smoke,
  Job-Orchestrierung, API-Qualitaet und semantischem Retrieval nach erledigter
  Crawler-Quellenpolitik, Observability-Grundlage, synthetischen Goldsets,
  localhost-geschuetztem Testindex-Seeding und automatisiertem Smoke-Befehl.

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

Arbeite danach die offenen Punkte in TODO.md von oben nach unten ab. Pro
Schritt nur eine kleine, nachvollziehbare Aufgabe bearbeiten: Kontext lesen,
umsetzen, gezielt pruefen, TODO/Doku aktualisieren, dann committen und pushen.
Keine Secrets, lokalen Pfade oder produktiven Zugangsdaten aufnehmen. Keine
Unterrepo-Dateien im Master committen. Erledigte TODO-Punkte nicht loeschen,
ausser der Nutzer fordert diese Bereinigung ausdruecklich an. TODO ist nicht
gleich Handoff.

Nach jeder Aenderung:
1. Betroffene Master-Doku oder Website knapp aktualisieren.
2. git diff --check ausfuehren.
3. git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search ausfuehren.
4. bash scripts/check-ecosystem.sh ausfuehren.
5. git status --short ausfuehren.
6. Commit und Push im Master-Repository ausfuehren, bevor der naechste TODO-
   Schritt begonnen wird.
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
