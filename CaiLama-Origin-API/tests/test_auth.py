from __future__ import annotations

import time

import pytest

from cailama_origin.auth import OriginAuthConfig, OriginAuthError, body_sha256, sign_request, verify_origin_request


def _headers(body: bytes, *, ts: str | None = None) -> dict[str, str]:
    timestamp = ts or str(int(time.time()))
    body_sha = body_sha256(body)
    return {
        "proxy_key": "proxy",
        "timestamp": timestamp,
        "body_sha": body_sha,
        "signature": sign_request(method="POST", path="/v1/jobs", timestamp=timestamp, body_sha=body_sha, secret="secret"),
    }


def test_verify_origin_request_accepts_valid_signature() -> None:
    body = b'{"type":"ping"}'
    headers = _headers(body)

    verify_origin_request(method="POST", path="/v1/jobs", body=body, config=OriginAuthConfig("proxy", "secret"), **headers)


def test_verify_origin_request_rejects_invalid_proxy_key() -> None:
    body = b"{}"
    headers = _headers(body)
    headers["proxy_key"] = "bad"

    with pytest.raises(OriginAuthError) as err:
        verify_origin_request(method="POST", path="/v1/jobs", body=body, config=OriginAuthConfig("proxy", "secret"), **headers)

    assert err.value.code == "invalid_proxy_key"


def test_verify_origin_request_rejects_stale_timestamp() -> None:
    body = b"{}"
    headers = _headers(body, ts=str(int(time.time()) - 1000))

    with pytest.raises(OriginAuthError) as err:
        verify_origin_request(method="POST", path="/v1/jobs", body=body, config=OriginAuthConfig("proxy", "secret"), **headers)

    assert err.value.code == "timestamp_out_of_window"


def test_verify_origin_request_rejects_body_hash_mismatch() -> None:
    body = b"{}"
    headers = _headers(body)
    headers["body_sha"] = body_sha256(b"changed")

    with pytest.raises(OriginAuthError) as err:
        verify_origin_request(method="POST", path="/v1/jobs", body=body, config=OriginAuthConfig("proxy", "secret"), **headers)

    assert err.value.code == "body_sha_mismatch"


def test_verify_origin_request_rejects_oversized_body() -> None:
    body = b"12345"
    headers = _headers(body)

    with pytest.raises(OriginAuthError) as err:
        verify_origin_request(method="POST", path="/v1/jobs", body=body, config=OriginAuthConfig("proxy", "secret", max_body_bytes=4), **headers)

    assert err.value.code == "body_too_large"
    assert err.value.status_code == 413
