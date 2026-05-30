# Origin API Security

The Origin API is a private server-to-server gateway. Except for
`GET /v1/health`, every request must include:

- `X-Cailama-Proxy-Key`
- `X-Cailama-Timestamp`
- `X-Cailama-Body-Sha256`
- `X-Cailama-Signature`

The signature payload is:

```text
METHOD
PATH
TIMESTAMP
BODY_SHA256
```

The payload is signed with HMAC-SHA256 using `CAILAMA_PROXY_HMAC_SECRET`.
Timestamps must be inside the configured short window, and the body hash must
match the exact request body. Oversized request bodies are rejected before
business logic runs.

The service emits JSON audit lines to stderr with timestamp, remote address,
method, path, status code, duration and user agent. It does not log request
bodies, signatures, proxy keys, HMAC secrets, prompts or model responses.

Security headers are added to every response:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: no-referrer`
- `X-Robots-Tag: noindex, nofollow`
