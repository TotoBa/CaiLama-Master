"""Authentication helpers for CaiLama origin proxy requests."""
from __future__ import annotations

import hashlib
import hmac
import os
import time
from dataclasses import dataclass


@dataclass(frozen=True)
class OriginAuthConfig:
    proxy_key: str = ""
    hmac_secret: str = ""
    max_body_bytes: int = 1_048_576
    timestamp_window_seconds: int = 120


class OriginAuthError(PermissionError):
    """Authentication or request-integrity failure."""

    def __init__(self, code: str, status_code: int = 401) -> None:
        super().__init__(code)
        self.code = code
        self.status_code = status_code


def load_auth_config() -> OriginAuthConfig:
    return OriginAuthConfig(
        proxy_key=os.environ.get("CAILAMA_PROXY_KEY", "").strip(),
        hmac_secret=os.environ.get("CAILAMA_PROXY_HMAC_SECRET", "").strip(),
        max_body_bytes=int(os.environ.get("CAILAMA_MAX_BODY_BYTES", "1048576")),
    )


def body_sha256(body: bytes) -> str:
    return hashlib.sha256(body).hexdigest()


def signature_payload(method: str, path: str, timestamp: str, body_sha: str) -> bytes:
    return f"{method}\n{path}\n{timestamp}\n{body_sha}".encode()


def sign_request(*, method: str, path: str, timestamp: str, body_sha: str, secret: str) -> str:
    return hmac.new(secret.encode(), signature_payload(method, path, timestamp, body_sha), hashlib.sha256).hexdigest()


def verify_origin_request(
    *,
    method: str,
    path: str,
    body: bytes,
    proxy_key: str | None,
    timestamp: str | None,
    signature: str | None,
    body_sha: str | None,
    config: OriginAuthConfig,
) -> None:
    if len(body) > config.max_body_bytes:
        raise OriginAuthError("body_too_large", status_code=413)
    if not config.proxy_key or not config.hmac_secret:
        raise OriginAuthError("gateway_secret_missing", status_code=503)
    if not proxy_key or not hmac.compare_digest(proxy_key, config.proxy_key):
        raise OriginAuthError("invalid_proxy_key")
    if not timestamp or not signature or not body_sha:
        raise OriginAuthError("missing_signature_headers")
    try:
        ts_int = int(timestamp)
    except ValueError as exc:
        raise OriginAuthError("invalid_timestamp") from exc
    if abs(int(time.time()) - ts_int) > config.timestamp_window_seconds:
        raise OriginAuthError("timestamp_out_of_window")
    actual_sha = body_sha256(body)
    if not hmac.compare_digest(actual_sha, body_sha):
        raise OriginAuthError("body_sha_mismatch")
    expected = sign_request(method=method, path=path, timestamp=timestamp, body_sha=body_sha, secret=config.hmac_secret)
    if not hmac.compare_digest(expected, signature):
        raise OriginAuthError("invalid_signature")
