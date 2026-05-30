"""File-backed jobs and PGN analysis for the Origin API."""
from __future__ import annotations

import io
import json
import re
import time
from dataclasses import dataclass
from pathlib import Path
from typing import Any


SAFE_JOB_ID = set("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-")


class JobNotFoundError(FileNotFoundError):
    """Raised when a job is missing or not visible to the current profile."""


@dataclass(frozen=True)
class JobStore:
    root: Path

    def path_for(self, job_id: str) -> Path:
        if not job_id or any(ch not in SAFE_JOB_ID for ch in job_id):
            raise JobNotFoundError(job_id)
        return self.root / f"{job_id}.json"

    def write(self, record: dict[str, Any]) -> None:
        self.root.mkdir(parents=True, exist_ok=True)
        self.path_for(str(record["job_id"])).write_text(
            json.dumps(record, ensure_ascii=True, indent=2),
            encoding="utf-8",
        )

    def read(self, job_id: str) -> dict[str, Any]:
        path = self.path_for(job_id)
        if not path.exists():
            raise JobNotFoundError(job_id)
        return json.loads(path.read_text(encoding="utf-8"))

    def list_for_profile(self, profile_key: str, *, status: str = "", limit: int = 10) -> list[dict[str, Any]]:
        self.root.mkdir(parents=True, exist_ok=True)
        jobs: list[dict[str, Any]] = []
        for path in sorted(self.root.glob("*.json"), key=lambda item: item.stat().st_mtime, reverse=True):
            try:
                record = json.loads(path.read_text(encoding="utf-8"))
            except Exception:
                continue
            if record.get("profile_key") != profile_key:
                continue
            if status and record.get("status") != status:
                continue
            jobs.append({k: record.get(k) for k in ("job_id", "type", "status", "created_at", "started_at", "finished_at", "error")})
            if len(jobs) >= limit:
                break
        return jobs


def assert_profile(record: dict[str, Any], profile_key: str) -> None:
    if record.get("profile_key") and record.get("profile_key") != profile_key:
        raise JobNotFoundError(str(record.get("job_id") or ""))


def normalize_german_san_pgn(pgn: str) -> str:
    replacements = {"D": "Q", "T": "R", "L": "B", "S": "N"}

    def repl(match: re.Match[str]) -> str:
        return match.group(1) + replacements.get(match.group(2), match.group(2))

    return re.sub(r"(^|\s)([DTLS])(?=[a-hx1-8#=+])", repl, pgn)


def headers_from_pgn(pgn: str) -> dict[str, str]:
    headers: dict[str, str] = {}
    for key, value in re.findall(r'^\[([^\s]+)\s+"(.*)"\]\s*$', pgn, flags=re.MULTILINE):
        headers[key] = value
    return headers


def parse_san_movetext(pgn: str) -> tuple[Any, list[dict[str, Any]]]:
    import chess

    board = chess.Board()
    moves: list[dict[str, Any]] = []
    body = re.sub(r"^\[[^\n]*\]\s*$", " ", pgn, flags=re.MULTILINE)
    body = re.sub(r"\{[^}]*\}|\([^)]*\)", " ", body)
    body = re.sub(r"\d+\.(\.\.)?", " ", body)
    tokens = [token for token in re.split(r"\s+", body.strip()) if token and token not in {"*", "1-0", "0-1", "1/2-1/2"}]
    for token in tokens:
        san = normalize_german_san_pgn(token)
        move = None
        last_error: Exception | None = None
        for candidate in [san]:
            try:
                move = board.parse_san(candidate)
                break
            except Exception as exc:
                last_error = exc
        if move is None:
            raise ValueError(f"Illegal SAN token {token!r}: {last_error}")
        rendered = board.san(move)
        board.push(move)
        moves.append({"ply": len(moves) + 1, "san": rendered, "uci": move.uci(), "fen_after": board.fen()})
    return board, moves


def analyze_pgn_payload(raw_pgn: str) -> dict[str, Any]:
    import chess.pgn

    pgn = normalize_german_san_pgn(raw_pgn)
    game = chess.pgn.read_game(io.StringIO(pgn))
    if game is None:
        raise ValueError("No valid PGN game found.")
    if getattr(game, "errors", None):
        raise ValueError(f"Invalid PGN game: {game.errors[0]}")
    board = game.board()
    moves: list[dict[str, Any]] = []
    for ply, move in enumerate(game.mainline_moves(), start=1):
        san = board.san(move)
        board.push(move)
        moves.append({"ply": ply, "san": san, "uci": move.uci(), "fen_after": board.fen()})
    if "#" in raw_pgn and not board.is_checkmate():
        board, moves = parse_san_movetext(raw_pgn)
    headers = headers_from_pgn(raw_pgn) or {key: str(value) for key, value in game.headers.items()}
    return {
        "headers": headers,
        "move_count": len(moves),
        "moves": moves,
        "final_fen": board.fen(),
        "is_checkmate": board.is_checkmate(),
        "legal": True,
        "result": headers.get("Result", "*"),
        "summary": "PGN parsed and analysed on server origin.",
    }


def run_pgn_analysis_job(store: JobStore, job_id: str) -> None:
    record = store.read(job_id)
    record["status"] = "running"
    record["started_at"] = int(time.time())
    store.write(record)
    try:
        raw_pgn = str(record.get("input", {}).get("pgn", ""))
        record["status"] = "done"
        record["result"] = analyze_pgn_payload(raw_pgn)
        record["finished_at"] = int(time.time())
        record["error"] = None
    except Exception as exc:
        record["status"] = "failed"
        record["error"] = str(exc)
        record["finished_at"] = int(time.time())
    store.write(record)
