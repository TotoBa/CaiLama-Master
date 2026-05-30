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

## Configuration

Runtime configuration is read from environment variables:

- `CAILAMA_PROXY_KEY`
- `CAILAMA_PROXY_HMAC_SECRET`
- `CAILAMA_ROUTER_BASE_URL`
- `CAILAMA_SEARCH_BASE_URL`
- `CAILAMA_JOB_DIR`
- `CAILAMA_MAX_BODY_BYTES`

Secrets are provided by the private runtime environment. Do not put real values
into this repository.

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
