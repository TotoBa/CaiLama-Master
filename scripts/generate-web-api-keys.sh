#!/usr/bin/env bash
set -euo pipefail

private_dir="${CAILAMA_PRIVATE_CONFIG_DIR:-$HOME/.config/cailama}"
key_file="${CAILAMA_WEB_API_KEYS_FILE:-$private_dir/web-api.keys}"

usage() {
  cat <<'USAGE'
Usage: scripts/generate-web-api-keys.sh [--force]

Creates or preserves CaiLama Webspace API keys in a private local file.
The script never prints key material.

Environment:
  CAILAMA_PRIVATE_CONFIG_DIR   default: ~/.config/cailama
  CAILAMA_WEB_API_KEYS_FILE    default: $CAILAMA_PRIVATE_CONFIG_DIR/web-api.keys
USAGE
}

force=0
while [[ $# -gt 0 ]]; do
  case "$1" in
    --force)
      force=1
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "ERROR: unknown argument: $1" >&2
      usage >&2
      exit 2
      ;;
  esac
  shift
done

random_token() {
  openssl rand -base64 48 | tr '+/' '-_' | tr -d '=\n'
}

read_existing() {
  local name="$1"
  [[ -f "$key_file" ]] || return 0
  awk -F= -v key="$name" '$1 == key { sub(/^[^=]*=/, ""); print; exit }' "$key_file"
}

ensure_key() {
  local name="$1" value
  value=""
  if [[ "$force" != "1" ]]; then
    value="$(read_existing "$name")"
  fi
  if [[ -z "$value" ]]; then
    value="$(random_token)"
  fi
  printf '%s=%s\n' "$name" "$value"
}

umask 077
mkdir -p "$private_dir"
tmp="$(mktemp "$private_dir/.web-api.keys.XXXXXX")"
{
  echo "# CaiLama Webspace API keys. Do not commit or publish."
  ensure_key CAILAMA_DB_API_STATUS_KEY
  ensure_key CAILAMA_DB_API_IMPORT_KEY
  ensure_key CAILAMA_DB_API_RESET_KEY
  ensure_key CAILAMA_DB_API_ADMIN_KEY
} > "$tmp"
chmod 600 "$tmp" 2>/dev/null || true
mv "$tmp" "$key_file"
chmod 600 "$key_file" 2>/dev/null || true
echo "OK: Webspace API keys available in private key file."
