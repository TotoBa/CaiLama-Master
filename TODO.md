# TODO - CaiLama-Master

Dieses TODO koordiniert Ecosystem-weite Aufgaben. Es ersetzt nicht die TODOs der
einzelnen Repositories und verlangt keine direkten Code-Aenderungen in den
Unter-Repos.

## 1. Master-Repo-Basis

- [x] `.gitignore` erstellen und Unter-Repos ignorieren:
  - [x] `/CaiLama/`
  - [x] `/CaiLama-LLM-Router/`
  - [x] `/CaiLama-Search/`
- [x] Pruefen, ob Unter-Repos versehentlich im Master-Index liegen.
- [x] `AGENTS.md` fuer Master-Repo-Regeln erstellen.
- [x] `README.md` mit Zweck und lokaler Struktur erstellen.
- [x] `docs/` fuer Ecosystem-Dokumentation anlegen.
- [x] `scripts/check-ecosystem.sh` als reine Statuspruefung ergaenzen.
- [ ] Bei kuenftigen Aenderungen erneut pruefen, dass keine Unter-Repo-Dateien
  getrackt werden.

## 2. Orchestrierung und Status

- [x] `master-repo-orchestration.plan.md` als Orchestrierungsplan erhalten.
- [x] `status.plan.cailama.md` als aktuellen Ecosystem-Status erhalten.
- [ ] Namensschema fuer neue Plan-Dateien konsequent anwenden.
- [ ] Statusplaene aktualisieren, wenn sich Repo-Rollen, Schnittstellen oder
  laufende Integrationsarbeiten wesentlich aendern.
- [ ] Cross-Repo-Aufgaben im Master dokumentieren, Umsetzung aber in den
  jeweiligen Repos belassen.
- [ ] Laufende Arbeiten regelmaessig nachfuehren:
  - [ ] CaiLama: personalisierter Trainingsgenerator und Folgehaertung.
  - [ ] CaiLama-Search: Meilisearch-API-Key-Management.
  - [ ] CaiLama-LLM-Router: Betriebs-, Fallback- und Backend-Haertung.

## 3. CaiLama Integrationsthemen

- [ ] Status des personalisierten Trainingsgenerators regelmaessig
  dokumentieren.
- [ ] Schnittstelle CaiLama -> CaiLama-Search fuer Suche, RAG und DWZ als
  Koordinationsthema beschreiben.
- [ ] Schnittstelle CaiLama -> CaiLama-LLM-Router fuer Modellzugriff als
  Koordinationsthema beschreiben.
- [ ] Klaeren, welche Runtime-Konfigurationen dokumentiert werden muessen, ohne
  Secrets offenzulegen.
- [ ] Pruefen, welche Smoke-Tests fuer das Gesamtsystem sinnvoll sind.

## 4. CaiLama-Search Integrationsthemen

- [ ] Fortschritt des Meilisearch-API-Key-Managements dokumentieren.
- [ ] Offene Punkte fuer Search-Auth-Hardening sammeln.
- [ ] Einheitliche Env-Namen und Runtime-Konfiguration als Cross-Repo-Thema
  dokumentieren.
- [ ] Search-API-Endpunkte dokumentieren, die CaiLama nutzen soll:
  - [ ] `/v1/search`
  - [ ] `/v1/context`
  - [ ] `/v1/dwz/search`
  - [ ] `/v1/dwz/player/{pkz}`
- [ ] Pruefen, welche Quellenlisten und Crawler-Policies in CaiLama-Search
  gepflegt werden sollen.

## 5. CaiLama-LLM-Router Integrationsthemen

- [ ] Modellklassen und Rollen-Aliase dokumentieren:
  - [ ] `router`
  - [ ] `small`
  - [ ] `large`
  - [ ] `task`
  - [ ] `coach`
  - [ ] `analyst`
  - [ ] `critic`
  - [ ] `vision`
  - [ ] `scribe`
  - [ ] `researcher`
- [ ] Pruefen, welche Modell-Aliase CaiLama erwartet.
- [ ] Pruefen, welche Health- und Smoke-Checks aus dem Master heraus
  dokumentiert werden koennen.
- [ ] Fallback- und Limit-Verhalten als Betriebsnotiz dokumentieren.
- [ ] Keine lokalen Provider-Secrets im Master speichern.

## 6. Qualitaetssicherung

- [x] Master-Repo-Checkskript ergaenzen.
- [ ] Bei jeder Master-Aenderung pruefen, dass die Unter-Repos ignoriert sind.
- [ ] Bei jeder Master-Aenderung pruefen, dass keine `.env`-Dateien getrackt
  sind.
- [ ] Bei jeder Master-Aenderung pruefen, dass keine Secrets in Markdown-Dateien
  stehen.
- [ ] Pruefen, dass Statusdateien keine falschen Aussagen ueber Repo-Zustaende
  enthalten.
- [ ] Pruefen, dass alle Cross-Repo-Aufgaben klare Ziel-Repos nennen.

## 7. Dokumentation

- [x] `docs/ecosystem-map.md` erstellen.
- [x] `docs/orchestration.md` erstellen.
- [ ] Lokale Setup-Hinweise ohne Secrets aktuell halten.
- [ ] Betriebsrollen der vier Repos klar halten.
- [ ] Historische Namen nur als historische Referenz erwaehnen.
- [ ] Dokumentation aktualisieren, wenn Repos umbenannt, verschoben oder neu
  strukturiert werden.

## Kimi-Arbeitsregeln

- [ ] Vor Arbeitsbeginn `AGENTS.md`, `README.md`, diese `TODO.md`,
  `docs/ecosystem-map.md`, `docs/orchestration.md`,
  `status.plan.cailama.md` und `master-repo-orchestration.plan.md` lesen.
- [ ] Nur Master-Repo-Dateien bearbeiten; Unter-Repos bleiben eigene Repos und
  werden im Master nur ignoriert.
- [ ] Keine Prompt-Dateien versionieren. Operative Folgearbeit gehoert in
  `TODO.md`; groessere Konzepte duerfen als `*.plan.md` abgelegt werden.
- [ ] Abschlusspruefung ausfuehren:
  `git status --short`,
  `git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search`,
  `bash scripts/check-ecosystem.sh`,
  `git diff --check`.
