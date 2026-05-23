"""Benchmark smoke — secret-free, offline, import check.

Uses each sub-repo's own .venv and benchmark modules.
No live services, no secrets, no local paths.
"""
from __future__ import annotations

import json
import os
import subprocess
import sys
import tempfile
from pathlib import Path

MASTER = Path(__file__).resolve().parent.parent
REPOS = {
    "CaiLama": MASTER / "CaiLama",
    "CaiLama-Search": MASTER / "CaiLama-Search",
    "CaiLama-LLM-Router": MASTER / "CaiLama-LLM-Router",
}


def _python(repo: Path) -> Path:
    for name in ("python", "python3"):
        venv = repo / ".venv" / "bin" / name
        if venv.exists():
            return venv
    return Path(sys.executable)


def _run_script(repo_name: str, code: str) -> dict:
    repo = REPOS[repo_name]
    py = _python(repo)
    env = os.environ.copy()
    env["PYTHONPATH"] = str(repo / "src")
    result = subprocess.run(
        [str(py), "-c", code],
        cwd=str(repo),
        env=env,
        capture_output=True,
        text=True,
        timeout=30,
    )
    if result.returncode != 0:
        return {
            "repo": repo_name,
            "status": "FAIL",
            "error": result.stderr.strip(),
            "stdout": result.stdout.strip(),
        }
    lines = result.stdout.strip().splitlines()
    try:
        data = json.loads(lines[-1])
    except (json.JSONDecodeError, IndexError):
        data = {"raw_stdout": result.stdout.strip()}
    data["repo"] = repo_name
    data["status"] = "OK"
    return data


def smoke_cailama_ptg() -> dict:
    code = r"""
import json, tempfile
from pathlib import Path
from cailama.player_profile.benchmark import (
    scan_ptg_sessions,
    build_ptg_benchmark_summary,
    export_benchmark_json,
)

with tempfile.TemporaryDirectory() as td:
    session = Path(td) / "session-01"
    session.mkdir()
    qg = session / "quality_gates.json"
    qg.write_text(json.dumps({
        "pgn_roundtrip_valid": True,
        "legal_moves_valid": True,
        "legal_positions_valid": True,
        "key_positions_count": 3,
        "training_cards_generated": 2,
        "cards_per_position_avg": 0.67,
        "errors": [],
        "warnings": [],
        "session_duration_seconds": 120.0,
    }))
    tj = session / "training.json"
    tj.write_text(json.dumps({
        "positions": [{"fen": "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1", "key_move": "e4", "tags": ["opening"]}],
        "cards": [],
    }))
    sessions = scan_ptg_sessions(td)
    summary = build_ptg_benchmark_summary(sessions, git_ref="smoke")
    export_benchmark_json(summary, Path(td) / "benchmark.json")
    out = {
        "test": "ptg_benchmark",
        "session_count": summary.session_count,
        "total_key_positions": summary.total_key_positions,
        "export_exists": (Path(td) / "benchmark.json").exists(),
    }
    print(json.dumps(out))
"""
    return _run_script("CaiLama", code)


def smoke_cailama_events() -> dict:
    code = r"""
import json, tempfile
from pathlib import Path
import cailama.agent.benchmark_events as be
from cailama.agent.benchmark_events import BenchmarkEvent, BenchmarkStore

with tempfile.TemporaryDirectory() as td:
    orig = be._benchmark_dir
    be._benchmark_dir = lambda: Path(td)
    try:
        store = BenchmarkStore()
        ev = BenchmarkEvent(
            timestamp="2026-05-23T12:00:00Z",
            task_type="smoke_test",
            role="tester",
            model="smoke-model",
            duration_ms=123,
            input_tokens=10,
            completion_tokens=5,
            thinking_tokens=0,
            total_tokens=15,
            artifact_ref="ref-1",
            error="",
            retry_count=0,
        )
        store.record(ev)
        s = store.summary()
        out = {
            "test": "benchmark_events",
            "total_events": s["total_events"],
            "export_exists": store.export_json().exists(),
        }
        print(json.dumps(out))
    finally:
        be._benchmark_dir = orig
"""
    return _run_script("CaiLama", code)


def smoke_cailama_ocr_gate() -> dict:
    code = r"""
import json, tempfile
from pathlib import Path
from cailama.knowledge.ocr_documents import (
    ChessDiagramCandidate,
    OcrDocumentResult,
    OcrPageResult,
)
from cailama.knowledge.ocr_quality_gates import run_ocr_quality_gates, gate_summary

page = OcrPageResult(
    page_number=1,
    text="Ein langer Satz mit vielen Worten und mehr Inhalt fuer Tests ueber 50 Zeichen.",
    text_language="deu",
    diagram_candidates=[
        ChessDiagramCandidate(page_number=1, bbox=(0,0,100,100), confidence=0.9,
            fen="rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1"),
    ],
)
result = OcrDocumentResult(
    source_path="/tmp/smoke.pdf",
    source_type="pdf",
    pages=[page],
)
gates = run_ocr_quality_gates(result, max_diagrams=5)
summary = gate_summary(gates)
out = {
    "test": "ocr_quality_gates",
    "gates_total": summary["total"],
    "all_passed": summary["all_passed"],
}
print(json.dumps(out))
"""
    return _run_script("CaiLama", code)


def smoke_search_goldsets() -> dict:
    code = r"""
import json
from cailama.search_backend.goldsets import (
    load_goldsets,
    DEFAULT_GOLDSET_DIR,
    summarize_goldsets,
    validate_goldset,
)

gs = load_goldsets(DEFAULT_GOLDSET_DIR)
summary = summarize_goldsets(gs)
validations = [{"name": g["name"], "ok": (validate_goldset(g) is None)} for g in gs]
out = {
    "test": "goldsets",
    "goldsets": summary["goldsets"],
    "cases": summary["cases"],
    "fixtures": summary["fixtures"],
    "validations": validations,
}
print(json.dumps(out))
"""
    return _run_script("CaiLama-Search", code)


def smoke_router_metrics() -> dict:
    code = r"""
import json
from llm_router.metrics import RequestMetrics

m = RequestMetrics()
m.record_request(alias="chess-small", backend="openai", latency_ms=100.0, success=True, fallback_used=False, limit_detected=False)
m.record_request(alias="chess-small", backend="anthropic", latency_ms=200.0, success=True, fallback_used=False, limit_detected=False)
m.record_request(alias="chess-large", backend="openai", latency_ms=50.0, success=False, fallback_used=True, limit_detected=False)
snap = m.snapshot()
out = {
    "test": "usage_metrics",
    "total_requests": snap["requests"]["total"],
    "avg_latency_ms": snap["requests"]["average_latency_ms"],
    "fallbacks": snap["requests"]["fallbacks"],
}
print(json.dumps(out))
"""
    return _run_script("CaiLama-LLM-Router", code)


def main() -> None:
    results: list[dict] = [
        smoke_cailama_ptg(),
        smoke_cailama_events(),
        smoke_cailama_ocr_gate(),
        smoke_search_goldsets(),
        smoke_router_metrics(),
    ]

    all_ok = all(r["status"] == "OK" for r in results)
    grouped: dict[str, list[dict]] = {}
    for r in results:
        repo = r["repo"]
        grouped.setdefault(repo, [])
        grouped[repo].append({k: v for k, v in r.items() if k not in ("status", "repo")})

    output = {
        "all_ok": all_ok,
        "count": len(results),
        "by_repo": grouped,
    }
    print(json.dumps(output, indent=2))
    sys.exit(0 if all_ok else 1)


if __name__ == "__main__":
    main()
