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
- [x] Bei kuenftigen Aenderungen erneut pruefen, dass keine Unter-Repo-Dateien getrackt werden.
- [x] Webseite fuer `https://cailama.org/` als statische HTML-Seite aufbauen:
  Quelle `web/index.html`, Deployment nach `/srv/cailama-web/public/index.html`,
  Inhalt angelehnt an die aktuelle `README.md` aus `TotoBa/CaiLama` mit Logo.

## 2. Orchestrierung und Status

- [x] `master-repo-orchestration.plan.md` als Orchestrierungsplan erhalten.
- [x] `status.plan.cailama.md` als aktuellen Ecosystem-Status erhalten.
- [x] Namensschema fuer neue Plan-Dateien konsequent anwenden.
- [x] Statusplaene aktualisieren, wenn sich Repo-Rollen, Schnittstellen oder
  laufende Integrationsarbeiten wesentlich aendern.
- [x] Cross-Repo-Aufgaben im Master dokumentieren, Umsetzung aber in den
  jeweiligen Repos belassen.
- [x] Laufende Arbeiten regelmaessig nachfuehren:
  - [x] CaiLama: personalisierter Trainingsgenerator und Folgehaertung.
  - [x] CaiLama-Search: Meilisearch-API-Key-Management.
  - [x] CaiLama-LLM-Router: Betriebs-, Fallback- und Backend-Haertung.
- [x] Roadmap aus `status.plan.cailama.md` vollstaendig nachhalten:
  - [x] Jetzt: Search-Auth-Hardening in CaiLama-Search.
  - [x] Jetzt: interner SearchAdapter in CaiLama.
  - [x] Danach: PTG-MVP/Folgehaertung in CaiLama.
  - [x] Danach: DWZ-Identity-Linking zwischen CaiLama und CaiLama-Search.
  - [x] Spaeter: RAG-gestuetzte Analysepakete.
  - [x] Spaeter: einheitliche Job-Orchestrierung fuer Import, Crawl,
    Game-Analyse, PTG und Reindex.
  - [x] Ausbau: Observability/KPIs fuer Router, Search und PTG.
  - [x] Ausbau: optionale semantische Retrieval-Schicht in CaiLama-Search.

## 3. CaiLama Integrationsthemen

- [x] Status des personalisierten Trainingsgenerators regelmaessig
  dokumentieren.
- [x] Schnittstelle CaiLama -> CaiLama-Search fuer Suche, RAG und DWZ als
  Koordinationsthema beschreiben.
- [x] Schnittstelle CaiLama -> CaiLama-LLM-Router fuer Modellzugriff als
  Koordinationsthema beschreiben.
- [x] Klaeren, welche Runtime-Konfigurationen dokumentiert werden muessen, ohne
  Secrets offenzulegen.
- [x] Pruefen, welche Smoke-Tests fuer das Gesamtsystem sinnvoll sind.

## 4. CaiLama-Search Integrationsthemen

- [x] Fortschritt des Meilisearch-API-Key-Managements dokumentieren.
- [x] Offene Punkte fuer Search-Auth-Hardening sammeln.
- [x] Einheitliche Env-Namen und Runtime-Konfiguration als Cross-Repo-Thema
  dokumentieren.
- [x] Search-API-Endpunkte dokumentieren, die CaiLama nutzen soll:
  - [x] `/v1/search`
  - [x] `/v1/context`
  - [x] `/v1/dwz/search`
  - [x] `/v1/dwz/player/{pkz}`
- [x] Pruefen, welche Quellenlisten und Crawler-Policies in CaiLama-Search
  gepflegt werden sollen.

## 5. CaiLama-LLM-Router Integrationsthemen

- [x] Modellklassen und Rollen-Aliase dokumentieren:
  - [x] `router`
  - [x] `small`
  - [x] `large`
  - [x] `task`
  - [x] `coach`
  - [x] `analyst`
  - [x] `critic`
  - [x] `vision`
  - [x] `scribe`
  - [x] `researcher`
- [x] Pruefen, welche Modell-Aliase CaiLama erwartet.
- [x] Pruefen, welche Health- und Smoke-Checks aus dem Master heraus
  dokumentiert werden koennen.
- [x] Fallback- und Limit-Verhalten als Betriebsnotiz dokumentieren.
- [x] Keine lokalen Provider-Secrets im Master speichern.

## 6. Qualitaetssicherung

- [x] Master-Repo-Checkskript ergaenzen.
- [x] Bei jeder Master-Aenderung pruefen, dass die Unter-Repos ignoriert sind.
- [x] Bei jeder Master-Aenderung pruefen, dass keine `.env`-Dateien getrackt
  sind.
- [x] Bei jeder Master-Aenderung pruefen, dass keine Secrets in Markdown-Dateien
  stehen.
- [x] Pruefen, dass Repo-TODOs die jeweils relevanten Punkte aus
  `status.plan.cailama.md` enthalten.
- [x] Pruefen, dass Statusdateien keine falschen Aussagen ueber Repo-Zustaende
  enthalten.
- [x] Pruefen, dass alle Cross-Repo-Aufgaben klare Ziel-Repos nennen.

## 7. Dokumentation

- [x] `docs/ecosystem-map.md` erstellen.
- [x] `docs/orchestration.md` erstellen.
- [x] `docs/website.md` fuer URL, Webspace und Deployment der Webseite erstellen.
- [x] Lokale Setup-Hinweise ohne Secrets aktuell halten.
- [x] Betriebsrollen der vier Repos klar halten.
- [x] Historische Namen nur als historische Referenz erwaehnen.
- [x] Dokumentation aktualisieren, wenn Repos umbenannt, verschoben oder neu
  strukturiert werden.

## Kimi-Arbeitsregeln

- [x] Vor Arbeitsbeginn `AGENTS.md`, `README.md`, diese `TODO.md`,
  `docs/ecosystem-map.md`, `docs/orchestration.md`,
  `status.plan.cailama.md` und `master-repo-orchestration.plan.md` lesen.
- [x] Nur Master-Repo-Dateien bearbeiten; Unter-Repos bleiben eigene Repos und
  werden im Master nur ignoriert.
- [x] Keine Prompt-Dateien versionieren. Operative Folgearbeit gehoert in
  `TODO.md`; groessere Konzepte duerfen als `*.plan.md` abgelegt werden.
- [x] Abschlusspruefung ausfuehren:
  `git status --short`,
  `git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search`,
  `bash scripts/check-ecosystem.sh`,
  `git diff --check`.
