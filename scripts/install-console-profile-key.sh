#!/usr/bin/env bash
set -euo pipefail

key_file=""
public_url="${CAILAMA_PUBLIC_URL:-https://cailama.org}"
private_dir="${CAILAMA_PRIVATE_CONFIG_DIR:-$HOME/.config/cailama}"
api_keys_file="${CAILAMA_WEB_API_KEYS_FILE:-$private_dir/web-api.keys}"

usage() {
  cat <<'USAGE'
Usage: scripts/install-console-profile-key.sh --key-file PRIVATE_ENV_FILE

Installs a generated console profile key through the protected Webspace admin
API. The cleartext key is never sent; only prefix and sha256 hash are sent.
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --key-file) key_file="${2:-}"; shift ;;
    -h|--help) usage; exit 0 ;;
    *) echo "ERROR: unknown argument: $1" >&2; usage >&2; exit 2 ;;
  esac
  shift
done

if [[ -z "$key_file" || ! -f "$key_file" ]]; then
  echo "ERROR: --key-file is required" >&2
  exit 2
fi
if [[ ! -f "$api_keys_file" ]]; then
  echo "ERROR: web API key file missing" >&2
  exit 2
fi

read_key() {
  awk -F= -v key="$1" '$1 == key { sub(/^[^=]*=/, ""); print; exit }' "$api_keys_file"
}

# shellcheck source=/dev/null
source "$key_file"
admin_key="$(read_key CAILAMA_DB_API_ADMIN_KEY)"
token="${CAILAMA_CONSOLE_KEY:-}"
profile_key="${CAILAMA_CONSOLE_PROFILE_KEY:-torsten-baublies-totomanie}"
training_name="${CAILAMA_CONSOLE_TRAINING_NAME:-totomanie}"
if [[ -z "$admin_key" || -z "$token" ]]; then
  echo "ERROR: required key material missing" >&2
  exit 2
fi

payload="$(php -r '
  echo json_encode([
    "profile_key" => $argv[1],
    "display_name" => "Torsten Baublies",
    "training_name" => $argv[2],
    "label" => "console-default",
    "key_prefix" => substr($argv[3], 0, 24),
    "key_hash" => "sha256:" . hash("sha256", $argv[3]),
    "scopes" => ["console:all", "llm:chat", "search:query", "jobs:write"],
  ], JSON_UNESCAPED_SLASHES);
' "$profile_key" "$training_name" "$token")"

response="$(mktemp)"
status="$(curl -sS --max-time 30 -o "$response" -w '%{http_code}' \
  -X POST \
  -H "Authorization: Bearer $admin_key" \
  -H "Content-Type: application/json" \
  --data "$payload" \
  "${public_url%/}/api/v1/admin/console-keys/upsert")"
if [[ "$status" != 2* ]]; then
  echo "ERROR: console key install failed with HTTP $status" >&2
  cat "$response" >&2
  rm -f "$response"
  exit 1
fi
cat "$response"
printf '\n'
rm -f "$response"
