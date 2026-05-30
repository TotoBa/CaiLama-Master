from __future__ import annotations

import json
import time

from fastapi.testclient import TestClient

from cailama_origin.auth import body_sha256, sign_request
from cailama_origin.main import app


def _signed_headers(method: str, path: str, body: bytes, *, profile: str = "profile-a") -> dict[str, str]:
    timestamp = str(int(time.time()))
    body_sha = body_sha256(body)
    return {
        "X-Cailama-Proxy-Key": "proxy",
        "X-Cailama-Timestamp": timestamp,
        "X-Cailama-Body-Sha256": body_sha,
        "X-Cailama-Signature": sign_request(method=method, path=path, timestamp=timestamp, body_sha=body_sha, secret="secret"),
        "X-Cailama-Profile-Key": profile,
    }


def _post(client: TestClient, path: str, payload: dict, *, profile: str = "profile-a"):
    body = json.dumps(payload).encode()
    return client.post(path, content=body, headers=_signed_headers("POST", path, body, profile=profile))


def test_health_is_public_and_has_security_headers(monkeypatch, tmp_path) -> None:
    monkeypatch.setenv("CAILAMA_JOB_DIR", str(tmp_path))
    client = TestClient(app)

    response = client.get("/v1/health")

    assert response.status_code == 200
    assert response.headers["X-Content-Type-Options"] == "nosniff"
    assert response.headers["X-Frame-Options"] == "DENY"


def test_signed_ping_job_roundtrip(monkeypatch, tmp_path) -> None:
    monkeypatch.setenv("CAILAMA_PROXY_KEY", "proxy")
    monkeypatch.setenv("CAILAMA_PROXY_HMAC_SECRET", "secret")
    monkeypatch.setenv("CAILAMA_JOB_DIR", str(tmp_path))
    client = TestClient(app)

    created = _post(client, "/v1/jobs", {"type": "ping", "input": {"message": "pong"}})
    assert created.status_code == 200
    job_id = created.json()["job_id"]

    status = _post(client, "/v1/jobs/status", {"job_id": job_id})
    assert status.json()["status"] == "done"

    result = _post(client, "/v1/jobs/result", {"job_id": job_id})
    assert result.json()["result"] == {"message": "pong"}

    listing = _post(client, "/v1/jobs/list", {"limit": 10})
    assert listing.json()["count"] == 1


def test_missing_signature_is_rejected(monkeypatch, tmp_path) -> None:
    monkeypatch.setenv("CAILAMA_PROXY_KEY", "proxy")
    monkeypatch.setenv("CAILAMA_PROXY_HMAC_SECRET", "secret")
    monkeypatch.setenv("CAILAMA_JOB_DIR", str(tmp_path))
    client = TestClient(app)

    response = client.post("/v1/jobs", json={"type": "ping"})

    assert response.status_code == 401
    assert response.json()["detail"]["error"] == "invalid_proxy_key"


def test_profile_cannot_read_foreign_job(monkeypatch, tmp_path) -> None:
    monkeypatch.setenv("CAILAMA_PROXY_KEY", "proxy")
    monkeypatch.setenv("CAILAMA_PROXY_HMAC_SECRET", "secret")
    monkeypatch.setenv("CAILAMA_JOB_DIR", str(tmp_path))
    client = TestClient(app)

    created = _post(client, "/v1/jobs", {"type": "ping"}, profile="owner")
    job_id = created.json()["job_id"]

    foreign = _post(client, "/v1/jobs/result", {"job_id": job_id}, profile="other")

    assert foreign.status_code == 404


def test_pgn_analysis_job_completes(monkeypatch, tmp_path) -> None:
    monkeypatch.setenv("CAILAMA_PROXY_KEY", "proxy")
    monkeypatch.setenv("CAILAMA_PROXY_HMAC_SECRET", "secret")
    monkeypatch.setenv("CAILAMA_JOB_DIR", str(tmp_path))
    client = TestClient(app)
    pgn = """
[Event "Smoke"]
[Result "*"]

1. e4 e5 2. Sf3 Sc6 3. d4 exd4
"""

    created = _post(client, "/v1/jobs", {"type": "pgn_analysis", "input": {"pgn": pgn}})
    assert created.status_code == 200
    job_id = created.json()["job_id"]

    result = _post(client, "/v1/jobs/result", {"job_id": job_id})

    assert result.status_code == 200
    assert result.json()["result"]["legal"] is True
    assert result.json()["result"]["move_count"] == 6
    assert result.json()["result"]["analysis_direction"] == "backward"
    assert "annotated_pgn" in result.json()["result"]
