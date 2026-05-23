# Handoff-Session 2026-05-23 — Implementierung: Background Agent + Benchmark Events + PTG Review-Gate

## Erledigt

1. **Background-Agent (`/task` Slash)** — 7 Tests
   - `src/cailama/agent/background.py`: Thread-basierte Jobs mit JSON-Persistenz
   - `src/cailama/agent/slash.py`: `start|list|status|stop|result|max-steps`

2. **Benchmark-Event Recorder** — 14 Tests
   - `src/cailama/agent/benchmark_events.py`: Frozen `BenchmarkEvent` + `BenchmarkStore` (JSONL)
   - `src/cailama/agent/controller.py`: Hooks in `_handle_qa` und `_handle_task`
   - `src/cailama/agent/loop.py`: `_llm_chat()` mit Timing + `on_llm_call` Callback

3. **PTG Review-Gate CLI** — Syntax-Argumente vorhanden, Integration in `_cmd_ptg_games` begonnen, aber **Tests noch flaky/tree**.

## Bekannte Probleme (Codex soll diese beheben)

### P1: `scripts/update-runtime-projects.sh` bricht ab
- Fehler: `ERROR: source repo missing or not a git repo: ~/CaiLama-Master/CaiLama/CaiLama`
- Ursache: Das Script erwartet `CaiLama` als Unterordner des Master-Roots, aber im aktuellen Master-Checkout liegt `CaiLama/` direkt unter dem Root. Vermutlich ein Pfad-Resolution-Problem im Script.
- Workaround: Direkter `rsync` aus dem Master-Checkout in `~/CaiLama` (Runtime).
- Fix: Das Script muss `source_dir()` so anpassen, dass es mit dem aktuellen Master-Layout funktioniert.

### P2: Runtime-`.venv` hat kein `pytest`
- Nach `update-runtime-projects.sh` ist pytest nicht installiert. Loesung: `~/CaiLama/.venv/bin/pip install pytest` manuell.
- Fix: Die `--install` Flag des Scripts oder ein Post-Deploy-Setup-Schritt sollte test-Abhaengigkeiten mitinstallieren.

### P3: Monkeypatching klappt nicht per String-Referenz
- `monkeypatch.setattr("cailama.agent.background._default_jobs_dir", lambda: target)` wirkt nicht, weil das Modul zur Importzeit an `_default_jobs_dir` gebunden ist.
- Loesung: Direkte Modul-Referenz nutzen:
  ```python
  import cailama.agent.background as _bg
  _bg._default_jobs_dir = lambda: target
  ```
- Fix: Alle Tests mit statischen Monkeypatch-Strings umstellen oder eine Fixture-Helfer einfuehren.

### P4: `AgentLoop`-Cancel-Test ist zeitkritisch
- `test_cancel_running_job` produziert Race-Conditions: Thread kann schneller fertig werden als `cancel_job()` aufgerufen wird.
- Loesung (provisorisch): LLM mit `NEXT` (statt `DONE`) nutzen und `active_count()` pollen statt Disk-Status.
- Sauberer Fix: `BackgroundAgent.cancel_job()` sollte synchron warten koennen (z.B. `join(timeout=)`), oder der Test braucht eine injizierbare Pause zwischen Schritten.

### P5: `load_review_decision` erwartet Verzeichnis, nicht File-Pfad
- `--review-gate-decision` nimmt einen Pfad, aber `load_review_decision(path)` erwartet `session_dir` und haengt `"review_decision.json"` an.
- Das fuehrt dazu, dass `--review-gate-decision /abs/path/zu/dec.json` nicht funktioniert.
- Fix: Entweder der CLI-Parameter sollte ein Verzeichnis verlangen (`--review-gate-dir`), oder `load_review_decision` sollte sowohl Verzeichnis als auch direkte Datei akzeptieren.

### P6: CSV-Export Float-Formatierung
- `BenchmarkStore.export_csv()` mit `duration_ms=100` (int) erzeugt CSV-Feld `"100"`, Test erwartete `"100.0"`.
- Fix: Test-Eingabe auf `100.0` aendern, oder Export explizit formatieren.

### P7: Editierungs-Fehler bei `--min-count`
- Ein StrReplaceFile hat versehentlich `ptg_p.add_argument("--min-count", ...)` entfernt, weil es zwischen zwei gleichen Blockgrenzen patchen wollte.
- Fix: Pruefung nach jedem Edit (z.B. `grep` auf die entfernte Zeile).

## Offene Arbeit fuer Codex

1. **`scripts/update-runtime-projects.sh` debuggen** — Warum schlaegt es mit "source repo missing" fehl?
2. **Review-Gate-Parameter inkonsistent** — `load_review_decision` vs. CLI-Parameter `--review-gate-decision` auf einander abstimmen.
3. **Test-Stabilitaet** — `test_cancel_running_job` sauber machen (optional: `join()` ins BackgroundAgent-API einbauen).
4. **Gesamtsuite-Zaehler** — In `TODO.md` steht `1621 passed`; nach Review-Gate-Tests anpassen.

## Keine Secrets in dieser Session
- Keine API-Keys, DB-Credentials oder lokale Pfade (ausser `~`) committiert.
- Benchmark-Events sind explizit secretfrei (keine Prompts/Responses).

---
*Session: 2026-05-23, Kimi K2.6*
