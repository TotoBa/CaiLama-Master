# Web / Console Parität

Matrix der Konsolenfunktionen und ihres Web-Zielzustands. Stand: Phase 0.

Legende:

- **Ja** — direkt über Web-Session/API nutzbar
- **Board** — benötigt VirtualBoardAdapter
- **Job** — asynchroner Hintergrundjob
- **Später** — nach MVP oder bewusst zurückgestellt

## Slash-Commands

| Befehl | Console | Web MVP | Web voll | Anmerkung |
|---|---|---|---|---|
| `/help` | Ja | Ja | Ja | Hilfetext |
| `/settings` | Ja | Später | Ja | Admin/tester |
| `/status` | Ja | Ja | Ja | Systemstatus |
| `/session` | Ja | Ja | Ja | Web-Session-CRUD |
| `/history` | Ja | Ja | Ja | Chatverlauf |
| `/clear` | Ja | Ja | Ja | UI-only im Browser |
| `/exit` | Ja | — | — | Terminal-only |
| `/model` | Ja | Ja | Ja | Modellwahl |
| `/models` | Ja | Ja | Ja | Modellliste |
| `/tools` | Ja | Ja | Ja | Tool-Katalog |
| `/tool` | Ja | Später | Ja | run/enable/install |
| `/mode` | Ja | Ja | Ja | qa/task/auto/safe |
| `/profile` | Ja | Ja | Ja | Tool-Profile |
| `/context` | Ja | Ja | Ja | Session-Kontext |
| `/engine` | Ja | Später | Ja | Engine-Konfiguration |
| `/db` | Ja | Später | Ja | Admin |
| `/task` | Ja | Job | Ja | Hintergrundjobs |
| `/activity` | Ja | — | — | Terminal-only |
| `/dwz` | Ja | Ja | Ja | Search-API |
| `/fide` | Ja | Ja | Ja | FIDE-Suche |
| `/board` | Ja | Board | Ja | VirtualBoard |
| `/fen` | Ja | Board | Ja | VirtualBoard |
| `/move` | Ja | Board | Ja | VirtualBoard |
| `/undo` | Ja | Board | Ja | VirtualBoard |
| `/flip` | Ja | Board | Ja | VirtualBoard |
| `/pgn` | Ja | Board/Job | Ja | Datei-Upload im Web |
| `/eval` | Ja | Board | Ja | BoardTruth |
| `/chain` | Ja | Board | Ja | Board-aware Chain |
| `/ensemble` | Ja | Board | Ja | Multi-Engine |
| `/plan` | Ja | Ja | Ja | Planmodus |
| `/dgt` | Ja | — | — | Kein DGT im Browser |
| `/coach` | Ja | Board | Ja | Training (Phase 6) |
| `/debug` | Ja | Später | Ja | Tester |
| `/thinking` | Ja | Ja | Ja | UI-Toggle |
| `/about` | Ja | Ja | Ja | Statische Info |
| `/review` | Ja | Job | Ja | Review-Gate |

## Fachliche Workflows

| Workflow | Console | Web MVP | Web voll | Anmerkung |
|---|---|---|---|---|
| Chat / QA | Ja | Ja | Ja | AgentController |
| Task / AgentLoop | Ja | Ja | Ja | SSE-Events |
| Tool-Aufrufe | Ja | Ja | Ja | Strukturierte Events |
| PGN-Analyse (PTG) | Ja | Job | Ja | Phase 5 |
| Training (Coach) | Ja | Board | Ja | Phase 6 |
| Engine-Partie | Ja | Board | Ja | Phase 7 |
| Search / RAG | Ja | Ja | Ja | Search-API |
| Artefakt-Download | Ja | Job | Ja | Login-geschützt |
| Benchmark-Feedback | Website | Website | Website | Separater Pfad |

## Live-Handler (nur in console.py)

Diese Befehle haben Platzhalter in `slash.py`, Live-Logik in `console.py`:

- `/board`, `/fen`, `/move`, `/undo`, `/flip`, `/pgn`, `/eval`, `/chain`, `/coach`

Extraktionsziel: `VirtualBoardAdapter` + `web_adapter.py`.

## MVP-Akzeptanz (Phase 8)

Für stabile Konsolenfunktionen darf die Spalte „Web MVP“ keine offenen Lücken mehr haben:

- [ ] Chat-Session
- [ ] Slash-Commands (Kern)
- [ ] VirtualBoard
- [ ] PGN-Analysejob
- [ ] `/models`, `/tools`, `/profile`, `/context`
- [ ] Search/RAG
