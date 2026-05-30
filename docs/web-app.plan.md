# Web-App Plan

Destillierte Umsetzungsplanung für die login-geschützte CaiLama-Web-UI.
Ausführliche Zielbeschreibung: internes `web.ui.plan.md` (nicht versioniert).

## Architektur

```text
Browser → /app/ (PHP/Smarty) → Webspace-API-Proxy → cailama-web (ASGI) → AgentController
```

PHP bleibt dünn: Login, CSRF, Proxy, Templates. Schachlogik nur in `TotoBa/CaiLama`.

## API-Verträge (Runtime)

Basis-Pfad intern: `/api/web/` (cailama-web Service).

| Methode | Pfad | Zweck |
|---|---|---|
| GET | `/health` | Health |
| GET | `/capabilities` | Feature-Flags |
| GET | `/models` | Modell-/Capability-Liste |
| POST | `/sessions` | Session anlegen |
| GET | `/sessions` | Session-Liste |
| GET | `/sessions/{id}` | Session-Details |
| DELETE | `/sessions/{id}` | Session beenden |
| POST | `/sessions/{id}/messages` | Chat-Nachricht |
| POST | `/sessions/{id}/commands` | Slash-Command |
| GET | `/sessions/{id}/events` | SSE-Event-Stream |
| POST | `/boards` | Brett anlegen |
| GET | `/boards/{id}` | Brett-Zustand |
| POST | `/boards/{id}/move` | Zug ausführen |
| POST | `/boards/{id}/load-fen` | FEN laden |
| POST | `/boards/{id}/load-pgn` | PGN laden |
| POST | `/boards/{id}/undo` | Zug zurück |
| POST | `/boards/{id}/reset` | Startstellung |
| POST | `/boards/{id}/flip` | Brett drehen |
| GET | `/boards/{id}/legal-moves` | Legale Züge |
| GET | `/boards/{id}/svg` | Brett-SVG |
| POST | `/analysis/jobs` | Analysejob starten |
| GET | `/analysis/jobs/{id}` | Job-Status |
| GET | `/analysis/jobs/{id}/artifacts` | Artefaktliste |
| GET | `/artifacts/{id}` | Artefakt-Download |
| POST | `/training/sessions` | Training starten |
| POST | `/engine-games` | Engine-Partie |

Legacy-Origin (Console-Proxy): `/v1/llm/chat`, `/v1/search/query`, `/v1/jobs/*`.

## Datenmodell (MariaDB)

Phase 3: `web_sessions`, `web_messages`, `web_session_events`, `web_boards`.

Phase 5+: `web_artifacts`, `analysis_jobs`, `training_web_sessions`, `engine_games`, `user_capabilities`.

Schema-Erweiterung synchron in `web/api_app/schema/cailama-data.sql`.

## Sicherheit

- Login für `/app/*`
- CSRF für state-changing Requests
- Keine Secrets im Browser/Repo
- Artefakte nur über authentifizierten Download
- Upload-Limits, PGN-Validierung
- RBAC: admin, tester, user, readonly

## Phasen

| Phase | Repo | Inhalt |
|---|---|---|
| 0 | Master | Doku, Paritätsmatrix |
| 1 | CaiLama | CailamaInteractiveSession |
| 2 | CaiLama | VirtualBoardAdapter |
| 3 | CaiLama | ASGI Web-API MVP |
| 4 | Master | `/app/` Shell + Proxy |
| 5 | Beide | PGN-Analysejobs |
| 6 | Beide | Training |
| 7 | Beide | Engine-Partien |
| 8 | Beide | Command-Parität |
| 9 | Master | Admin-Dashboard |
| 10 | Runtime | Deploy-Services |

Siehe auch [`web-console-parity.md`](web-console-parity.md) und [`integrations.md`](integrations.md).
