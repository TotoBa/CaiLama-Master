#!/usr/bin/env python3
"""Export per-model CaiLama benchmark result tables.

Input is the secret-free JSON summary written by
``scripts/benchmark_feedback_agent.py --summary-json`` plus, optionally, the
local benchmark artifact metadata. The output is a Markdown document owned by
the Master repository.
"""
from __future__ import annotations

import argparse
import json
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path
from typing import Any


ROLE_ORDER = [
    "chess-router",
    "chess-small",
    "chess-large",
    "chess-task",
    "chess-translator",
    "chess-coach",
    "chess-analyst",
    "chess-critic",
    "chess-vision",
    "chess-scribe",
    "chess-researcher",
]


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--summary-json", required=True, type=Path)
    parser.add_argument("--artifact-json", type=Path, default=None)
    parser.add_argument("--output", required=True, type=Path)
    parser.add_argument(
        "--selected-model",
        action="append",
        default=[],
        help="Model alias selected for the next retest. Can be passed repeatedly.",
    )
    parser.add_argument(
        "--extra-model",
        action="append",
        default=[],
        help="Additional untested model alias planned for the next retest.",
    )
    args = parser.parse_args()

    summary = _read_json(args.summary_json)
    artifact = _read_json(args.artifact_json) if args.artifact_json else {}
    output = render_markdown(
        summary,
        artifact=artifact,
        selected_models=args.selected_model,
        extra_models=args.extra_model,
    )
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(output, encoding="utf-8")
    return 0


def render_markdown(
    summary: dict[str, Any],
    *,
    artifact: dict[str, Any],
    selected_models: list[str],
    extra_models: list[str],
) -> str:
    run_key = str(summary.get("run_key") or artifact.get("run_id") or "")
    created_at = str(artifact.get("created_at") or "")
    generated_at = datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")
    model_rows = [row for row in summary.get("model_roles", []) if isinstance(row, dict)]
    by_model: dict[str, list[dict[str, Any]]] = defaultdict(list)
    for row in model_rows:
        model = str(row.get("model_label") or "").strip()
        if model:
            by_model[model].append(row)

    lines = [
        "# Aktuelle Modellrollen-Ergebnisse",
        "",
        f"Stand: {generated_at}.",
        f"Quelle: Website-Feedback-Summary fuer Run `{run_key}`.",
    ]
    if created_at:
        lines.append(f"Testlauf erzeugt: {created_at}.")
    if artifact:
        lines.append(
            "Umfang: "
            f"{len(artifact.get('models') or by_model)} Modelle, "
            f"{len(artifact.get('roles') or ROLE_ORDER)} Rollen, "
            f"{artifact.get('role_task_count', 'n/a')} Rollenaufgaben, "
            f"PTG-Teil {'uebersprungen' if artifact.get('ptg_skipped') else 'ausgefuehrt'}."
        )
    lines.extend(
        [
            "",
            "Die folgenden Tabellen sind nicht kumuliert: Jedes getestete Modell",
            "hat eine eigene Tabelle. Scores stammen aus dem geschuetzten",
            "Website-Feedback und bleiben secretfrei.",
            "",
            "Wichtige Einordnung: Der abgeschlossene Lauf nutzte vor den aktuellen",
            "Fixes noch eine zu strenge Task-/Tool-Strukturpruefung. Besonders",
            "`chess-task` und Teile von `chess-router` werden deshalb im naechsten",
            "Re-Test neu bewertet.",
            "",
            "## Naechster Re-Test",
            "",
            "Ziel: nur die 10 ausgewaehlten bereits starken Kandidaten plus ein",
            "Mistral-API-Modell testen.",
            "",
            "| Typ | Modell | Grund |",
            "| --- | --- | --- |",
        ]
    )
    selected_reasons = _selected_reasons()
    for model in selected_models:
        lines.append(f"| Auswahl | `{model}` | {selected_reasons.get(model, 'starker Kandidat aus dem aktuellen Lauf')} |")
    for model in extra_models:
        lines.append(f"| Zusatz | `{model}` | Mistral-API-Free-/Experiment-Plan-Smoke; direkter Provider, nicht Ollama-Cloud |")
    lines.extend(["", "## Ergebnisse Pro Modell", ""])

    for model in sorted(by_model):
        rows = sorted(by_model[model], key=lambda row: _role_sort_key(str(row.get("role_name") or "")))
        lines.append(f"### `{model}`")
        if created_at:
            lines.append(f"Testdatum: {created_at}.")
        lines.append(f"Run: `{run_key}`.")
        if model in selected_models:
            lines.append("Status: fuer den naechsten Re-Test ausgewaehlt.")
        lines.extend(
            [
                "",
                "| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |",
                "| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |",
            ]
        )
        for row in rows:
            lines.append(
                "| "
                + " | ".join(
                    [
                        f"`{_text(row, 'role_name')}`",
                        _fmt(_score(row)),
                        _fmt(row.get("feedback_count")),
                        _fmt(row.get("quality_avg")),
                        _fmt(row.get("task_solution_avg")),
                        _fmt(row.get("duration_score_avg")),
                        _fmt(row.get("major_logic_errors")),
                        _fmt(row.get("minor_logic_errors")),
                        _fmt(row.get("error_observations")),
                        _fmt(row.get("duration_ms_avg")),
                        _fmt(row.get("total_tokens_avg")),
                        _fmt(row.get("estimated_usage_units_sum")),
                    ]
                )
                + " |"
            )
        lines.append("")
    return "\n".join(lines).rstrip() + "\n"


def _read_json(path: Path | None) -> dict[str, Any]:
    if path is None:
        return {}
    return json.loads(path.read_text(encoding="utf-8"))


def _role_sort_key(role: str) -> tuple[int, str]:
    try:
        return (ROLE_ORDER.index(role), role)
    except ValueError:
        return (len(ROLE_ORDER), role)


def _score(row: dict[str, Any]) -> float:
    quality = float(row.get("quality_avg") or 0)
    task = float(row.get("task_solution_avg") or 0)
    duration = float(row.get("duration_score_avg") or 0)
    count = int(row.get("feedback_count") or 0)
    major = int(row.get("major_logic_errors") or 0)
    minor = int(row.get("minor_logic_errors") or 0)
    errors = int(row.get("error_observations") or 0)
    confidence = min(count / 10.0, 1.0)
    return round((quality * 0.45 + task * 0.4 + duration * 0.15) * confidence - major * 0.35 - minor * 0.1 - errors * 0.2, 3)


def _fmt(value: Any) -> str:
    if value is None or value == "":
        return ""
    if isinstance(value, float):
        return f"{value:.3f}".rstrip("0").rstrip(".")
    return str(value)


def _text(row: dict[str, Any], key: str) -> str:
    return str(row.get(key) or "")


def _selected_reasons() -> dict[str, str]:
    return {
        "gpt-oss:20b-cloud:think-medium": "bester Analyst-Kandidat im abgeschlossenen Lauf",
        "gemini-3-flash-preview:cloud:think-on": "bester Coach-Kandidat im abgeschlossenen Lauf",
        "qwen3-next:80b-cloud:think-on": "bester Critic-Kandidat im abgeschlossenen Lauf",
        "gemini-3-flash-preview:cloud:think-off": "bester Large-/Scribe-Kandidat im abgeschlossenen Lauf",
        "qwen3-coder:480b-cloud": "bester Researcher-Kandidat und Task-faehiger Coder-Kandidat",
        "gpt-oss:120b-cloud:think-medium": "bester Small-/Klassifikationskandidat im abgeschlossenen Lauf",
        "devstral-small-2:24b-cloud": "starker kompakter Translator-/Task-Kandidat",
        "minimax-m2.7:cloud:think-off": "bester Vision/OCR-FEN-Kandidat im abgeschlossenen Lauf",
        "ministral-3:3b-cloud": "guenstiger Translator-/Small-Kandidat aus dem aktuellen Lauf",
        "kimi-k2.6:cloud:think-on": "aktueller Kimi-Cloud-Default und wichtiger Router-/Agent-Vergleich",
    }


if __name__ == "__main__":
    raise SystemExit(main())
