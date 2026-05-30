# CaiLama-Origin-API

Internal FastAPI origin gateway for the protected Webspace console proxy.

The service exposes the legacy console contract used by `cailama-cli` and the
Webspace API:

- `GET /v1/health`
- `POST /v1/llm/chat`
- `POST /v1/search/query`
- `POST /v1/jobs`
- `POST /v1/jobs/list`
- `POST /v1/jobs/status`
- `POST /v1/jobs/result`

The gateway is not a public API. It is intended to run behind the CaiLama
reverse proxy and accepts signed server-to-server requests only.

PGN jobs are intentionally lightweight in this component. They validate normal
English SAN and additionally normalize German SAN letters, return backward progress events, critical
mate/end moments, a summary and an annotated PGN. Deep Stockfish/Maia/DWZ
analysis stays in the CaiLama Web/Agent pipeline; the Origin response keeps the
console job contract useful even before a client fetches richer Web artifacts.

## Configuration

Runtime configuration is read from environment variables:

- `CAILAMA_PROXY_KEY`
- `CAILAMA_PROXY_HMAC_SECRET`
- `CAILAMA_ROUTER_BASE_URL`
- `CAILAMA_SEARCH_BASE_URL`
- `CAILAMA_JOB_DIR`
- `CAILAMA_AUDIT_LOG_PATH`
- `CAILAMA_MAX_BODY_BYTES`

Secrets are provided by the private runtime environment. Do not put real values
into this repository.

`CAILAMA_AUDIT_LOG_PATH` is optional for development. In hardened runtime it
should point at a writable JSONL file such as `/var/log/cailama/audit.log`.
The container must receive that path as an explicit writable volume or tmpfs
when `read_only: true` is active.

## Development

```bash
uv run --extra dev pytest -q
```

The tests are offline and use temporary job directories plus mocked backend
clients.

## Runtime

The Master deploy script syncs this directory into the runtime
`api-gateway` build context. Docker Compose builds the `cailama-api-gateway`
container from the synchronized runtime copy.
