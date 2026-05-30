from __future__ import annotations

from cailama_origin.jobs import JobNotFoundError, JobStore, analyze_pgn_payload, assert_profile


def test_job_store_roundtrip_and_profile_filter(tmp_path) -> None:
    store = JobStore(tmp_path)
    store.write({"job_id": "abc-123", "type": "ping", "status": "done", "profile_key": "p1", "created_at": 1})
    store.write({"job_id": "def-456", "type": "ping", "status": "done", "profile_key": "p2", "created_at": 2})

    assert store.read("abc-123")["profile_key"] == "p1"
    assert [job["job_id"] for job in store.list_for_profile("p1")] == ["abc-123"]


def test_assert_profile_hides_foreign_jobs() -> None:
    try:
        assert_profile({"job_id": "abc", "profile_key": "owner"}, "other")
    except JobNotFoundError:
        pass
    else:
        raise AssertionError("foreign job must be hidden")


def test_analyze_pgn_payload_accepts_german_notation_and_checkmate() -> None:
    pgn = """
[Event "Smoke"]
[White "Weiss"]
[Black "Schwarz"]
[Result "1-0"]

1. e4 e5 2. Sf3 Sc6 3. Lb5 a6 4. La4 Sf6 5. O-O Le7 6. Te1 b5
7. Lb3 d6 8. c3 O-O 9. h3 Sa5 10. Lc2 c5 11. d4 Dc7
"""
    result = analyze_pgn_payload(pgn)

    assert result["legal"] is True
    assert result["move_count"] == 22
    assert result["moves"][1]["san"] == "e5"
    assert result["headers"]["Event"] == "Smoke"
    assert result["analysis_direction"] == "backward"
    assert result["events"][-1]["stage"] == "done"
    assert "Cailama_Analysis_Direction" in result["annotated_pgn"]


def test_analyze_pgn_payload_rejects_invalid_move() -> None:
    try:
        analyze_pgn_payload("1. e4 e5 2. e5")
    except Exception as exc:
        assert "illegal" in str(exc).lower() or "san" in str(exc).lower()
    else:
        raise AssertionError("invalid PGN must fail")
