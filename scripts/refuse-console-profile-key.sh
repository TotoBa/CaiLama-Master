#!/usr/bin/env bash
set -euo pipefail

key_prefix=""
key_file=""
sql_only=0
private_dir="${CAILAMA_PRIVATE_CONFIG_DIR:-$HOME/.config/cailama}"
api_keys_file="${CAILAMA_WEB_API_KEYS_FILE:-$private_dir/web-api.keys}"
public_url="${CAILAMA_PUBLIC_URL:-https://cailama.org}"

usage() {
  cat <<'USAGE'
Usage:
  scripts/refuse-console-profile-key.sh --key-prefix PREFIX
  scripts/refuse-console-profile-key.sh --key-file PRIVATE_ENV_FILE

Refuses a profile-bound CaiLama console key through the protected Webspace
admin API. Use --sql-only to print SQL instead of calling the API.
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --key-prefix) key_prefix="${2:-}"; shift ;;
    --key-file) key_file="${2:-}"; shift ;;
    --sql-only) sql_only=1 ;;
    -h|--help) usage; exit 0 ;;
    *) echo "ERROR: unknown argument: $1" >&2; usage >&2; exit 2 ;;
  esac
  shift
done

if [[ -n "$key_file" ]]; then
  if [[ ! -f "$key_file" ]]; then
    echo "ERROR: key file not found" >&2
    exit 1
  fi
  # shellcheck source=/dev/null
  source "$key_file"
  key="${CAILAMA_CONSOLE_KEY:-}"
  if [[ -z "$key" ]]; then
    echo "ERROR: CAILAMA_CONSOLE_KEY missing in key file" >&2
    exit 2
  fi
  key_prefix="${key:0:24}"
fi

if [[ -z "$key_prefix" ]]; then
  echo "ERROR: --key-prefix or --key-file is required" >&2
  usage >&2
  exit 2
fi

safe_prefix="$(printf "%s" "$key_prefix" | sed "s/'/''/g")"
if [[ "$sql_only" == "1" ]]; then
  cat <<EOF
UPDATE cailama_console_api_keys
SET status = 'refused',
    refused_at = CURRENT_TIMESTAMP
WHERE key_prefix = '$safe_prefix';
EOF
  exit 0
fi

if [[ ! -f "$api_keys_file" ]]; then
  echo "ERROR: web API key file missing; use --sql-only for SQL output" >&2
  exit 2
fi
admin_key="$(awk -F= '$1 == "CAILAMA_DB_API_ADMIN_KEY" { sub(/^[^=]*=/, ""); print; exit }' "$api_keys_file")"
if [[ -z "$admin_key" ]]; then
  echo "ERROR: admin API key missing; use --sql-only for SQL output" >&2
  exit 2
fi
payload="$(php -r 'echo json_encode(["key_prefix" => $argv[1]], JSON_UNESCAPED_SLASHES);' "$key_prefix")"
response="$(mktemp)"
status="$(curl -sS --max-time 30 -o "$response" -w '%{http_code}' \
  -X POST \
  -H "Authorization: Bearer $admin_key" \
  -H "Content-Type: application/json" \
  --data "$payload" \
  "${public_url%/}/api/v1/admin/console-keys/refuse")"
if [[ "$status" != 2* ]]; then
  echo "ERROR: console key refuse failed with HTTP $status" >&2
  cat "$response" >&2
  rm -f "$response"
  exit 1
fi
cat "$response"
printf '\n'
rm -f "$response"
