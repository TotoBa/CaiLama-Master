"""FastAPI application for the CaiLama internal origin gateway."""
from __future__ import annotations

import json
import os
import sys
import time
import uuid
from dataclasses import dataclass
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import httpx
from fastapi import BackgroundTasks, FastAPI, Header, HTTPException, Request
from fastapi.responses import JSONResponse

from cailama_origin.auth import OriginAuthError, load_auth_config, verify_origin_request
from cailama_origin.jobs import JobNotFoundError, JobStore, assert_profile, run_pgn_analysis_job


@dataclass(frozen=True)
class OriginSettings:
    router_base_url: str = "http://router:18080"
    search_base_url: str = "http://search:8080"
    job_dir: Path = Path("/data/jobs")
    audit_log_path: Path | None = None


def load_settings() -> OriginSettings:
    audit_log_path = os.environ.get("CAILAMA_AUDIT_LOG_PATH", "").strip()
    return OriginSettings(
        router_base_url=os.environ.get("CAILAMA_ROUTER_BASE_URL", "http://router:18080").rstrip("/"),
        search_base_url=os.environ.get("CAILAMA_SEARCH_BASE_URL", "http://search:8080").rstrip("/"),
        job_dir=Path(os.environ.get("CAILAMA_JOB_DIR", "/data/jobs")),
        audit_log_path=Path(audit_log_path) if audit_log_path else None,
    )


def get_job_store() -> JobStore:
    return JobStore(load_settings().job_dir)


app = FastAPI(title="CaiLama Origin Gateway", docs_url=None, redoc_url=None)


def _auth_error(exc: OriginAuthError) -> HTTPException:
    return HTTPException(status_code=exc.status_code, detail={"error": exc.code})


def _require_auth(
    method: str,
    path: str,
    body: bytes,
    key: str | None,
    timestamp: str | None,
    signature: str | None,
    body_sha: str | None,
) -> None:
    if (method, path) == ("GET", "/v1/health"):
        return
    try:
        verify_origin_request(
            method=method,
            path=path,
            body=body,
            proxy_key=key,
            timestamp=timestamp,
            signature=signature,
            body_sha=body_sha,
            config=load_auth_config(),
        )
    except OriginAuthError as exc:
        raise _auth_error(exc) from exc


async def _read_json(request: Request) -> dict[str, Any]:
    body = await request.body()
    if not body:
        return {}
    try:
        data = json.loads(body.decode("utf-8"))
    except json.JSONDecodeError as exc:
        raise HTTPException(status_code=400, detail={"error": "invalid_json"}) from exc
    if not isinstance(data, dict):
        raise HTTPException(status_code=400, detail={"error": "json_object_required"})
    return data


def _profile(request: Request) -> dict[str, str]:
    return {
        "profile_key": request.headers.get("x-cailama-profile-key", ""),
        "training_name": request.headers.get("x-cailama-training-name", ""),
    }


def _job_not_found(exc: JobNotFoundError) -> HTTPException:
    return HTTPException(status_code=404, detail={"error": "job_not_found"})


async def _json_from_backend(response: httpx.Response) -> dict[str, Any] | list[Any]:
    if not response.content:
        return {}
    try:
        payload = response.json()
    except ValueError as exc:
        raise HTTPException(status_code=502, detail={"error": "backend_invalid_json"}) from exc
    if not isinstance(payload, (dict, list)):
        raise HTTPException(status_code=502, detail={"error": "backend_invalid_json"})
    return payload


@app.middleware("http")
async def audit_middleware(request: Request, call_next):
    start_time = time.time()
    response = await call_next(request)
    duration_ms = (time.time() - start_time) * 1000
    audit_entry = {
        "timestamp": datetime.now(timezone.utc).isoformat(),
        "remote_addr": request.client.host if request.client else "",
        "method": request.method,
        "path": request.url.path,
        "status_code": response.status_code,
        "duration_ms": round(duration_ms, 2),
        "user_agent": request.headers.get("user-agent", "")[:512],
    }
    _write_audit_entry(audit_entry, load_settings().audit_log_path)
    return response


def _write_audit_entry(audit_entry: dict[str, Any], audit_log_path: Path | None) -> None:
    line = json.dumps(audit_entry, ensure_ascii=False) + "\n"
    sys.stderr.write(line)
    sys.stderr.flush()
    if audit_log_path is None:
        return
    try:
        audit_log_path.parent.mkdir(parents=True, exist_ok=True)
        with audit_log_path.open("a", encoding="utf-8") as handle:
            handle.write(line)
    except OSError as exc:
        error_entry = {
            "timestamp": datetime.now(timezone.utc).isoformat(),
            "event": "audit_log_write_failed",
            "path": str(audit_log_path),
            "error": exc.__class__.__name__,
        }
        sys.stderr.write(json.dumps(error_entry, ensure_ascii=False) + "\n")
        sys.stderr.flush()


@app.middleware("http")
async def security_middleware(request: Request, call_next):
    response = await call_next(request)
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["Referrer-Policy"] = "no-referrer"
    response.headers["X-Robots-Tag"] = "noindex, nofollow"
    return response


@app.get("/v1/health")
async def health() -> dict[str, Any]:
    settings = load_settings()
    status: dict[str, Any] = {"status": "ok", "services": {}}
    async with httpx.AsyncClient(timeout=3.0) as client:
        for name, url in {"router": f"{settings.router_base_url}/health", "search": f"{settings.search_base_url}/healthz"}.items():
            try:
                response = await client.get(url)
                status["services"][name] = {"ok": response.status_code < 500, "status_code": response.status_code}
            except httpx.HTTPError:
                status["services"][name] = {"ok": False}
    return status


@app.post("/v1/llm/chat")
async def llm_chat(
    request: Request,
    x_cailama_proxy_key: str | None = Header(None),
    x_cailama_timestamp: str | None = Header(None),
    x_cailama_signature: str | None = Header(None),
    x_cailama_body_sha256: str | None = Header(None),
):
    body = await request.body()
    _require_auth("POST", "/v1/llm/chat", body, x_cailama_proxy_key, x_cailama_timestamp, x_cailama_signature, x_cailama_body_sha256)
    data = await _read_json(request)
    payload: dict[str, Any] = {
        "model": data.get("model") or data.get("model_profile") or "chess-small",
        "messages": data.get("messages", []),
        "stream": bool(data.get("stream", False)),
    }
    if "temperature" in data:
        payload["temperature"] = data["temperature"]
    async with httpx.AsyncClient(timeout=60.0) as client:
        response = await client.post(f"{load_settings().router_base_url}/v1/chat/completions", json=payload)
    return JSONResponse(status_code=response.status_code, content=await _json_from_backend(response))


@app.post("/v1/search/query")
async def search_query(
    request: Request,
    x_cailama_proxy_key: str | None = Header(None),
    x_cailama_timestamp: str | None = Header(None),
    x_cailama_signature: str | None = Header(None),
    x_cailama_body_sha256: str | None = Header(None),
):
    body = await request.body()
    _require_auth("POST", "/v1/search/query", body, x_cailama_proxy_key, x_cailama_timestamp, x_cailama_signature, x_cailama_body_sha256)
    data = await _read_json(request)
    if "q" in data and "query" not in data:
        data["query"] = data.pop("q")
    async with httpx.AsyncClient(timeout=20.0) as client:
        response = await client.post(f"{load_settings().search_base_url}/v1/search", json=data)
    return JSONResponse(status_code=response.status_code, content=await _json_from_backend(response))


@app.post("/v1/jobs")
async def create_job(
    background_tasks: BackgroundTasks,
    request: Request,
    x_cailama_proxy_key: str | None = Header(None),
    x_cailama_timestamp: str | None = Header(None),
    x_cailama_signature: str | None = Header(None),
    x_cailama_body_sha256: str | None = Header(None),
):
    body = await request.body()
    _require_auth("POST", "/v1/jobs", body, x_cailama_proxy_key, x_cailama_timestamp, x_cailama_signature, x_cailama_body_sha256)
    data = await _read_json(request)
    job_id = str(uuid.uuid4())
    job_type = str(data.get("type") or "unknown")
    profile = _profile(request)
    status = "done" if job_type == "ping" else "queued"
    result = {"message": data.get("input", {}).get("message", "ok")} if job_type == "ping" else None
    record = {
        "job_id": job_id,
        "type": job_type,
        "status": status,
        "input": data.get("input", {}),
        "result": result,
        "created_at": int(time.time()),
        **profile,
    }
    store = get_job_store()
    store.write(record)
    if job_type == "pgn_analysis":
        background_tasks.add_task(run_pgn_analysis_job, store, job_id)
    return {"job_id": job_id, "status": status}


@app.post("/v1/jobs/list")
async def list_jobs(
    request: Request,
    x_cailama_proxy_key: str | None = Header(None),
    x_cailama_timestamp: str | None = Header(None),
    x_cailama_signature: str | None = Header(None),
    x_cailama_body_sha256: str | None = Header(None),
):
    body = await request.body()
    _require_auth("POST", "/v1/jobs/list", body, x_cailama_proxy_key, x_cailama_timestamp, x_cailama_signature, x_cailama_body_sha256)
    data = await _read_json(request)
    wanted_status = str(data.get("status") or "")
    limit = max(1, min(int(data.get("limit") or 10), 100))
    jobs = get_job_store().list_for_profile(request.headers.get("x-cailama-profile-key", ""), status=wanted_status, limit=limit)
    return {"jobs": jobs, "count": len(jobs)}


@app.post("/v1/jobs/status")
async def job_status(
    request: Request,
    x_cailama_proxy_key: str | None = Header(None),
    x_cailama_timestamp: str | None = Header(None),
    x_cailama_signature: str | None = Header(None),
    x_cailama_body_sha256: str | None = Header(None),
):
    body = await request.body()
    _require_auth("POST", "/v1/jobs/status", body, x_cailama_proxy_key, x_cailama_timestamp, x_cailama_signature, x_cailama_body_sha256)
    data = await _read_json(request)
    try:
        record = get_job_store().read(str(data.get("job_id") or ""))
        assert_profile(record, request.headers.get("x-cailama-profile-key", ""))
    except JobNotFoundError as exc:
        raise _job_not_found(exc) from exc
    return {k: record.get(k) for k in ("job_id", "type", "status", "created_at", "started_at", "finished_at", "error")}


@app.post("/v1/jobs/result")
async def job_result(
    request: Request,
    x_cailama_proxy_key: str | None = Header(None),
    x_cailama_timestamp: str | None = Header(None),
    x_cailama_signature: str | None = Header(None),
    x_cailama_body_sha256: str | None = Header(None),
):
    body = await request.body()
    _require_auth("POST", "/v1/jobs/result", body, x_cailama_proxy_key, x_cailama_timestamp, x_cailama_signature, x_cailama_body_sha256)
    data = await _read_json(request)
    try:
        record = get_job_store().read(str(data.get("job_id") or ""))
        assert_profile(record, request.headers.get("x-cailama-profile-key", ""))
    except JobNotFoundError as exc:
        raise _job_not_found(exc) from exc
    if record.get("status") != "done":
        return JSONResponse(status_code=202, content={"job_id": record.get("job_id"), "status": record.get("status"), "error": record.get("error")})
    return {"job_id": record.get("job_id"), "status": record.get("status"), "result": record.get("result")}
