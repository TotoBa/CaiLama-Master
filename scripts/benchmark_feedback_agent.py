#!/usr/bin/env python3
"""Evaluate open CaiLama benchmark feedback items via the web API.

The script intentionally keeps secrets outside the repository. It reads a
Bearer token from an environment variable or from the local private key file
created by scripts/generate-web-api-keys.sh.
"""

from __future__ import annotations

import argparse
import json
import os
import re
import sys
import textwrap
import urllib.error
import urllib.request
from collections import defaultdict
from dataclasses import dataclass
from pathlib import Path
from typing import Any


DEFAULT_API_URL = "https://cailama.org/api/v1"
DEFAULT_KEY_FILE = "~/.config/cailama/web-api.keys"
DEFAULT_KEY_NAME = "CAILAMA_DB_API_ADMIN_KEY"
TOKEN_ENV_NAMES = (
    "CAILAMA_BENCHMARK_FEEDBACK_TOKEN",
    "CAILAMA_DB_API_ADMIN_KEY",
)


@dataclass(frozen=True)
class Evaluation:
    observation_id: int
    quality_score: int
    task_solution_score: int
    duration_score: int
    logic_error_level: str
    preferred_option: str
    translation_score: int | None
    feedback_text: str
    improvement_note: str
    translation_note: str | None

    def as_payload(self) -> dict[str, Any]:
        payload: dict[str, Any] = {
            "observation_id": self.observation_id,
            "quality_score": self.quality_score,
            "task_solution_score": self.task_solution_score,
            "duration_score": self.duration_score,
            "logic_error_level": self.logic_error_level,
            "preferred_option": self.preferred_option,
            "feedback_text": self.feedback_text,
            "improvement_note": self.improvement_note,
        }
        if self.translation_score is not None:
            payload["translation_score"] = self.translation_score
        if self.translation_note:
            payload["translation_note"] = self.translation_note
        return payload


class ApiError(RuntimeError):
    pass


def clamp_score(value: int) -> int:
    return max(1, min(5, value))


def load_token(args: argparse.Namespace) -> str:
    if args.token:
        return args.token.strip()

    for env_name in TOKEN_ENV_NAMES:
        value = os.environ.get(env_name, "").strip()
        if value:
            return value

    key_file = Path(args.keys_file).expanduser()
    if not key_file.is_file():
        raise ApiError(
            "No API token found. Set CAILAMA_BENCHMARK_FEEDBACK_TOKEN or "
            f"provide --keys-file (missing: {key_file})."
        )

    key_prefix = args.key_name + "="
    for line in key_file.read_text(encoding="utf-8", errors="ignore").splitlines():
        if line.startswith(key_prefix):
            value = line.split("=", 1)[1].strip()
            if value:
                return value

    raise ApiError(f"Key {args.key_name!r} not found in {key_file}.")


def post_json(api_url: str, endpoint: str, token: str, payload: dict[str, Any]) -> dict[str, Any]:
    url = api_url.rstrip("/") + endpoint
    body = json.dumps(payload, ensure_ascii=False).encode("utf-8")
    request = urllib.request.Request(
        url,
        data=body,
        headers={
            "Authorization": "Bearer " + token,
            "Content-Type": "application/json",
            "Accept": "application/json",
        },
        method="POST",
    )
    try:
        with urllib.request.urlopen(request, timeout=120) as response:
            decoded = json.loads(response.read().decode("utf-8"))
    except urllib.error.HTTPError as exc:
        detail = exc.read().decode("utf-8", errors="replace")
        raise ApiError(f"HTTP {exc.code} from {endpoint}: {detail[:500]}") from exc
    except urllib.error.URLError as exc:
        raise ApiError(f"Could not call {endpoint}: {exc}") from exc

    if not isinstance(decoded, dict):
        raise ApiError(f"Unexpected JSON response from {endpoint}.")
    if decoded.get("status") not in (None, "ok"):
        raise ApiError(f"API error from {endpoint}: {decoded}")
    return decoded


def text_value(observation: dict[str, Any], key: str) -> str:
    value = observation.get(key)
    return value if isinstance(value, str) else ""


def int_value(observation: dict[str, Any], key: str) -> int:
    value = observation.get(key)
    if isinstance(value, int):
        return value
    if isinstance(value, str) and value.isdigit():
        return int(value)
    return 0


def total_tokens(observation: dict[str, Any]) -> int:
    explicit = int_value(observation, "total_tokens")
    if explicit:
        return explicit
    return (
        int_value(observation, "input_tokens")
        + int_value(observation, "thinking_tokens")
        + int_value(observation, "output_tokens")
    )


def duration_score(duration_ms: int, total_tokens: int) -> int:
    if duration_ms <= 0:
        return 3
    if duration_ms <= 30_000:
        score = 5
    elif duration_ms <= 90_000:
        score = 4
    elif duration_ms <= 180_000:
        score = 3
    elif duration_ms <= 300_000:
        score = 2
    else:
        score = 1

    if total_tokens > 12_000:
        score -= 1
    if total_tokens > 24_000:
        score -= 1
    return clamp_score(score)


def extract_json_array(output: str) -> list[Any] | None:
    candidates = []
    fenced = re.findall(r"```(?:json)?\s*(\[.*?\])\s*```", output, flags=re.I | re.S)
    candidates.extend(fenced)
    start = output.find("[")
    end = output.rfind("]")
    if start != -1 and end > start:
        candidates.append(output[start : end + 1])

    for candidate in candidates:
        try:
            parsed = json.loads(candidate)
        except json.JSONDecodeError:
            continue
        if isinstance(parsed, list):
            return parsed
    return None


def contains_any(text: str, needles: tuple[str, ...]) -> bool:
    lower = text.lower()
    return any(needle in lower for needle in needles)


def count_any(text: str, needles: tuple[str, ...]) -> int:
    lower = text.lower()
    return sum(1 for needle in needles if needle in lower)


def base_quality(output: str) -> tuple[int, list[str]]:
    notes: list[str] = []
    stripped = output.strip()
    if not stripped:
        return 1, ["empty output"]
    if len(stripped) < 40:
        return 2, ["very short output"]

    score = 3
    if len(stripped) >= 250:
        score += 1
    if len(stripped) >= 900:
        score += 1
    if re.search(r"(^|\n)\s*(#|[-*]\s+|\d+\.)", stripped):
        score += 1
    if contains_any(stripped, ("traceback", "exception", "error:", "i cannot", "i can't")):
        score -= 2
        notes.append("contains error/refusal marker")
    if stripped.count("not_available") > 4:
        score -= 1
        notes.append("overuses not_available")
    return clamp_score(score), notes


def extract_json_object(output: str) -> dict[str, Any] | None:
    candidates: list[str] = []
    fenced = re.findall(r"```(?:json)?\s*(\{.*?\})\s*```", output, flags=re.I | re.S)
    candidates.extend(fenced)
    start = output.find("{")
    end = output.rfind("}")
    if start != -1 and end > start:
        candidates.append(output[start : end + 1])

    for candidate in candidates:
        try:
            parsed = json.loads(candidate)
        except json.JSONDecodeError:
            continue
        if isinstance(parsed, dict):
            return parsed
    return None


def score_routing_decision(output: str) -> tuple[int, int, list[str]]:
    notes: list[str] = []
    quality, base_notes = base_quality(output)
    task = quality
    notes.extend(base_notes)

    parsed = extract_json_object(output)
    if parsed is None:
        return clamp_score(quality), 1, notes + ["expected routing JSON but no parseable object was found"]

    role = str(parsed.get("role") or parsed.get("role_hint") or "").strip()
    source = str(parsed.get("routing_source") or "").strip()
    reason = str(parsed.get("reason") or "").strip()
    confidence = parsed.get("confidence")

    if role:
        task += 2
    else:
        notes.append("routing role missing")

    if source == "llm_semantic":
        task += 2
    elif source:
        task += 1
        notes.append(f"routing_source={source}")
    else:
        notes.append("routing_source missing")

    if reason:
        task += 1
    else:
        notes.append("routing reason missing")

    if isinstance(confidence, (int, float)):
        task += 1
    else:
        notes.append("routing confidence missing")

    tools = parsed.get("tools")
    if isinstance(tools, list):
        quality += 1

    return clamp_score(max(quality, task - 1)), clamp_score(task), notes


def score_task_plan(output: str) -> tuple[int, int, list[str]]:
    notes: list[str] = []
    quality, base_notes = base_quality(output)
    task = quality
    notes.extend(base_notes)

    parsed = extract_json_object(output)
    if parsed is None:
        return clamp_score(quality), 1, notes + ["expected task plan JSON but no parseable object was found"]

    steps = parsed.get("steps")
    if not isinstance(steps, list) or not steps:
        return clamp_score(quality), 1, notes + ["task plan has no steps array"]

    actions: list[str] = []
    for step in steps:
        if not isinstance(step, dict):
            continue
        action = str(step.get("action") or step.get("tool_name") or "").strip()
        if action:
            actions.append(action)

    if not actions:
        return clamp_score(quality), 1, notes + ["task plan steps contain no tool actions"]

    task += 1
    if len(actions) >= 2:
        task += 2
    if len(actions) >= 3:
        quality += 1

    source = str(parsed.get("planning_source") or "").strip()
    if source == "llm_semantic":
        task += 2
    elif source:
        task += 1
        notes.append(f"planning_source={source}")
    else:
        notes.append("planning_source missing")

    reason = str(parsed.get("reason") or "").strip()
    if reason:
        task += 1

    if all(isinstance(step, dict) and str(step.get("description") or step.get("purpose") or "").strip() for step in steps if isinstance(step, dict)):
        quality += 1

    return clamp_score(max(quality, task - 1)), clamp_score(task), notes


def expected_type_score(expected: str, role: str, output: str) -> tuple[int, int, list[str]]:
    lower = output.lower()
    notes: list[str] = []
    quality, base_notes = base_quality(output)
    task = quality
    notes.extend(base_notes)

    if expected == "routing_decision":
        return score_routing_decision(output)

    if expected == "task_plan":
        return score_task_plan(output)

    if expected == "json_array":
        parsed = extract_json_array(output)
        if parsed is None:
            return 2, 1, notes + ["expected a JSON array but no parseable array was found"]
        if not parsed:
            return 3, 2, notes + ["JSON array is empty"]
        dict_items = sum(isinstance(item, dict) for item in parsed)
        task = 4 if dict_items else 3
        quality = max(quality, 4)
        if any(
            isinstance(item, dict)
            and any(key in item for key in ("move", "san", "uci", "question", "answer", "task"))
            for item in parsed
        ):
            task = 5
        return clamp_score(quality), clamp_score(task), notes

    if expected == "translation":
        translation_markers = ("übersetzung", "deutsch", "springer", "läufer", "turm", "dame", "könig", "bauer")
        task += 1 if contains_any(output, translation_markers) else -1
        if contains_any(output, ("translation:", "translate", "english")) and not contains_any(output, translation_markers):
            task -= 1
            notes.append("translation may not be German-facing")
        return clamp_score(quality), clamp_score(task), notes

    if expected == "critique":
        markers = ("issue", "problem", "fehler", "gap", "correction", "korrektur", "safer", "verbesser")
        marker_count = count_any(output, markers)
        task += 2 if marker_count >= 3 else 1 if marker_count >= 2 else -1
        if "correction" not in lower and "korrektur" not in lower and "safer" not in lower:
            notes.append("critique lacks an explicit correction")
            task -= 1
        return clamp_score(quality), clamp_score(task), notes

    if expected in {"analysis", "training_card"}:
        chess_markers = (
            "move",
            "zug",
            "candidate",
            "kandidat",
            "variation",
            "line",
            "tactic",
            "taktik",
            "king",
            "könig",
            "queen",
            "dame",
            "rook",
            "turm",
            "bishop",
            "läufer",
            "knight",
            "springer",
            "pawn",
            "bauer",
            "fen",
            "san",
        )
        marker_count = count_any(output, chess_markers)
        task += 2 if marker_count >= 5 else 1 if marker_count >= 2 else -1
        if expected == "training_card":
            card_markers = ("frage", "question", "answer", "antwort", "motif", "motiv", "solution", "lösung")
            task += 1 if count_any(output, card_markers) >= 2 else -1
        return clamp_score(quality), clamp_score(task), notes

    if expected == "coach_question":
        asks_question = "?" in output
        coach_markers = ("warum", "what", "which", "welcher", "calculate", "berechne", "finde", "zug")
        task += 1 if asks_question else -1
        task += 1 if contains_any(output, coach_markers) else 0
        if not asks_question:
            notes.append("coach output does not ask a clear question")
        return clamp_score(quality), clamp_score(task), notes

    if expected == "ocr_decision":
        ocr_markers = ("fen", "diagram", "diagramm", "confidence", "konfidenz", "ocr", "board", "brett")
        task += 2 if count_any(output, ocr_markers) >= 3 else 0
        if contains_any(output, ("guess", "rate", "maybe", "vielleicht")):
            task -= 1
            notes.append("OCR decision sounds uncertain")
        return clamp_score(quality), clamp_score(task), notes

    if expected == "rag_answer":
        source_markers = ("source", "quelle", "citation", "zitat", "provenance", "herkunft", "stand", "freshness")
        task += 2 if count_any(output, source_markers) >= 2 else -1
        if "http" in lower:
            task += 1
        return clamp_score(quality), clamp_score(task), notes

    if expected == "summary":
        if 80 <= len(output.strip()) <= 2500:
            task += 1
        if contains_any(output, ("summary", "zusammenfassung", "key", "wichtig", "fazit")):
            task += 1
        return clamp_score(quality), clamp_score(task), notes

    role_markers = {
        "chess-critic": ("critique", "issue", "correction", "fehler"),
        "chess-coach": ("?", "frage", "why", "warum"),
        "chess-translator": ("übersetzung", "deutsch", "translation"),
        "chess-researcher": ("source", "quelle", "provenance", "herkunft"),
        "chess-scribe": ("summary", "zusammenfassung", "notiz"),
    }
    markers = role_markers.get(role, ())
    if markers and contains_any(output, markers):
        task += 1
    return clamp_score(quality), clamp_score(task), notes


def evaluate_observation(observation: dict[str, Any]) -> Evaluation:
    observation_id = int_value(observation, "observation_id")
    output = text_value(observation, "output_excerpt")
    expected = text_value(observation, "expected_output_type")
    role = text_value(observation, "role_name")
    error_status = text_value(observation, "error_status")

    if error_status:
        quality, task, notes = 1, 1, [f"contract error: {error_status}"]
    else:
        quality, task, notes = expected_type_score(expected, role, output)

    duration = duration_score(
        int_value(observation, "duration_ms"),
        total_tokens(observation),
    )

    if quality <= 2 or task <= 2:
        logic = "major"
    elif quality == 3 or task == 3:
        logic = "minor"
    else:
        logic = "none"

    if notes:
        feedback_text = "Automated benchmark feedback: " + "; ".join(notes[:4]) + "."
    else:
        feedback_text = "Automated benchmark feedback: output is usable for the requested role."

    improvement_note = {
        "major": "Repair the role/output contract before using this model for the role.",
        "minor": "Tighten role-specific structure and make the answer more directly task-grounded.",
        "none": "No blocking issue found by the automated content heuristic.",
    }[logic]

    translation_score = quality if expected == "translation" else None
    translation_note = None
    if expected == "translation" and translation_score <= 3:
        translation_note = "Translation quality or target-language clarity needs review."

    return Evaluation(
        observation_id=observation_id,
        quality_score=quality,
        task_solution_score=task,
        duration_score=duration,
        logic_error_level=logic,
        preferred_option="not_applicable",
        translation_score=translation_score,
        feedback_text=feedback_text,
        improvement_note=improvement_note,
        translation_note=translation_note,
    )


def fetch_open(
    api_url: str,
    token: str,
    run_key: str,
    limit: int,
    include_model_labels: bool,
) -> dict[str, Any]:
    payload: dict[str, Any] = {
        "limit": limit,
        "include_model_labels": include_model_labels,
    }
    if run_key:
        payload["run_key"] = run_key
    return post_json(api_url, "/benchmarks/feedback/open", token, payload)


def submit_feedback(api_url: str, token: str, evaluations: list[Evaluation]) -> dict[str, Any]:
    return post_json(
        api_url,
        "/benchmarks/feedback",
        token,
        {"feedback": [evaluation.as_payload() for evaluation in evaluations]},
    )


def fetch_summary(api_url: str, token: str, run_key: str, include_model_labels: bool) -> dict[str, Any]:
    payload: dict[str, Any] = {"include_model_labels": include_model_labels}
    if run_key:
        payload["run_key"] = run_key
    return post_json(api_url, "/benchmarks/feedback/summary", token, payload)


def score_row(row: dict[str, Any]) -> float:
    quality = float(row.get("quality_avg") or 0)
    task = float(row.get("task_solution_avg") or 0)
    duration = float(row.get("duration_score_avg") or 0)
    major = int(row.get("major_logic_errors") or 0)
    minor = int(row.get("minor_logic_errors") or 0)
    errors = int(row.get("error_observations") or 0)
    count = int(row.get("feedback_count") or 0)
    confidence = min(count, 10) / 10.0
    return round((quality * 0.45 + task * 0.4 + duration * 0.15) * confidence - major * 0.35 - minor * 0.1 - errors * 0.2, 3)


def top_by_role(summary: dict[str, Any], top_n: int) -> dict[str, list[dict[str, Any]]]:
    rows = summary.get("model_roles") or summary.get("model_role_summary") or []
    grouped: dict[str, list[dict[str, Any]]] = defaultdict(list)
    for row in rows:
        if not isinstance(row, dict):
            continue
        role = str(row.get("role_name") or "")
        if not role:
            continue
        enriched = dict(row)
        enriched["score"] = score_row(enriched)
        grouped[role].append(enriched)
    for role_rows in grouped.values():
        role_rows.sort(
            key=lambda row: (
                float(row.get("score") or 0),
                float(row.get("quality_avg") or 0),
                float(row.get("task_solution_avg") or 0),
                -int(row.get("major_logic_errors") or 0),
                float(row.get("duration_score_avg") or 0),
            ),
            reverse=True,
        )
    return {role: rows[:top_n] for role, rows in sorted(grouped.items())}


def print_top_tables(summary: dict[str, Any], top_n: int, include_model_labels: bool) -> None:
    print(f"\nRun: {summary.get('run_key', '')}")
    print(f"Top {top_n} per role\n")
    for role, rows in top_by_role(summary, top_n).items():
        print(f"### {role}")
        headers = ["#", "candidate"]
        if include_model_labels:
            headers.append("model")
        headers.extend(["score", "n", "quality", "task", "duration", "major", "minor", "errors"])
        print("| " + " | ".join(headers) + " |")
        print("|" + "|".join(["---"] * len(headers)) + "|")
        for index, row in enumerate(rows, 1):
            values = [
                str(index),
                str(row.get("candidate_code") or ""),
            ]
            if include_model_labels:
                values.append(str(row.get("model_label") or ""))
            values.extend(
                [
                    str(row.get("score") or 0),
                    str(row.get("feedback_count") or 0),
                    str(row.get("quality_avg") or ""),
                    str(row.get("task_solution_avg") or ""),
                    str(row.get("duration_score_avg") or ""),
                    str(row.get("major_logic_errors") or 0),
                    str(row.get("minor_logic_errors") or 0),
                    str(row.get("error_observations") or 0),
                ]
            )
            print("| " + " | ".join(values) + " |")
        print()


def evaluate_open_feedback(args: argparse.Namespace) -> int:
    token = load_token(args)
    include_model_labels = not args.hide_model_labels
    open_response = fetch_open(args.api_url, token, args.run_key, 1, include_model_labels)
    run_key = args.run_key or str(open_response.get("run_key") or "")
    stats = open_response.get("stats") if isinstance(open_response.get("stats"), dict) else {}
    open_count = int(stats.get("open_count") or 0)
    print(f"Run: {run_key}")
    print(f"Open feedback items before: {open_count}")

    processed = 0
    while True:
        if args.max_items and processed >= args.max_items:
            break
        batch_limit = args.limit
        if args.max_items:
            batch_limit = min(batch_limit, args.max_items - processed)
        response = fetch_open(args.api_url, token, run_key, batch_limit, include_model_labels)
        observations = response.get("observations") or []
        if not observations:
            break
        evaluations = [evaluate_observation(observation) for observation in observations if isinstance(observation, dict)]
        if not evaluations:
            break
        if args.dry_run:
            print(f"Dry run: would submit {len(evaluations)} feedback rows.")
            processed += len(evaluations)
            break
        submit_feedback(args.api_url, token, evaluations)
        processed += len(evaluations)
        print(f"Submitted {processed} feedback rows...", flush=True)

    summary = fetch_summary(args.api_url, token, run_key, include_model_labels)
    if args.summary_json:
        Path(args.summary_json).write_text(json.dumps(summary, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
        print(f"Summary written to {args.summary_json}")
    print_top_tables(summary, args.top, include_model_labels)

    after = fetch_open(args.api_url, token, run_key, 1, include_model_labels)
    after_stats = after.get("stats") if isinstance(after.get("stats"), dict) else {}
    print(f"Open feedback items after: {int(after_stats.get('open_count') or 0)}")
    return 0


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        description="Evaluate open CaiLama benchmark feedback cases and print role top tables.",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=textwrap.dedent(
            """\
            Examples:
              scripts/benchmark_feedback_agent.py --dry-run --max-items 10
              scripts/benchmark_feedback_agent.py --run-key ptg-three-games-...
              CAILAMA_BENCHMARK_FEEDBACK_TOKEN=... scripts/benchmark_feedback_agent.py
            """
        ),
    )
    parser.add_argument("--api-url", default=DEFAULT_API_URL)
    parser.add_argument("--run-key", default="")
    parser.add_argument("--limit", type=int, default=100, help="Batch size for open feedback fetches (max API limit: 200).")
    parser.add_argument("--max-items", type=int, default=0, help="Maximum items to evaluate; 0 means all currently open items.")
    parser.add_argument("--dry-run", action="store_true", help="Evaluate but do not submit feedback.")
    parser.add_argument("--hide-model-labels", action="store_true", help="Do not request admin-only model labels in summaries.")
    parser.add_argument("--token", default="", help=argparse.SUPPRESS)
    parser.add_argument("--keys-file", default=DEFAULT_KEY_FILE)
    parser.add_argument("--key-name", default=DEFAULT_KEY_NAME)
    parser.add_argument("--top", type=int, default=5)
    parser.add_argument("--summary-json", default="", help="Optional path to write the raw summary JSON.")
    return parser


def main(argv: list[str] | None = None) -> int:
    parser = build_parser()
    args = parser.parse_args(argv)
    if args.limit < 1 or args.limit > 200:
        parser.error("--limit must be between 1 and 200")
    if args.top < 1:
        parser.error("--top must be positive")
    try:
        return evaluate_open_feedback(args)
    except ApiError as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
