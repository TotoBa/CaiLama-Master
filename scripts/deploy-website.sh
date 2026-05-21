#!/usr/bin/env bash
set -euo pipefail

target="${1:-/srv/cailama-web/public}"
root="$(git rev-parse --show-toplevel)"
public_url="${CAILAMA_PUBLIC_URL:-https://cailama.org}"
public_url="${public_url%/}"
verify_mode="${CAILAMA_DEPLOY_VERIFY:-}"

if [[ -z "$verify_mode" ]]; then
  if [[ "$target" == "/srv/cailama-web/public" ]]; then
    verify_mode="http-hash"
  else
    verify_mode="none"
  fi
fi

cd "$root"

if [[ ! -d "web" ]]; then
  echo "ERROR: web/ directory missing" >&2
  exit 1
fi

mkdir -p "$target"
rsync -a --delete \
  --exclude "/api_app/config.local.php" \
  --exclude "/api_app/config.local.*.php" \
  "web/" "$target/"

hash_file() {
  sha256sum "$1" | awk '{print $1}'
}

verify_http_hash() {
  local relative="$1" local_hash remote_hash
  local_hash="$(hash_file "web/$relative")"
  remote_hash="$(curl -fsS --max-time 12 "$public_url/$relative" | sha256sum | awk '{print $1}')"
  if [[ "$local_hash" != "$remote_hash" ]]; then
    echo "ERROR: deployed HTTP hash differs: $relative" >&2
    exit 1
  fi
}

case "$verify_mode" in
  none)
    echo "SKIP: deploy verification disabled"
    ;;
  http-hash)
    static_files=(
      "robots.txt"
      "sitemap.xml"
      "llms.txt"
      "ecosystem-reference.md"
      "data/ecosystem.json"
      "assets/styles.css"
    )
    for relative in "${static_files[@]}"; do
      verify_http_hash "$relative"
    done
    for page in "" "projects.php" "architecture.php" "roadmap.php" "operations.php" "reference.php"; do
      curl -fsS --max-time 12 "$public_url/$page" >/dev/null
    done
    echo "OK: deployed public files verified via HTTPS hashes"
    ;;
  target-hash)
    while IFS= read -r -d '' source; do
      relative="${source#web/}"
      case "$relative" in
        api_app/config.local.php|api_app/config.local.*.php)
          continue
          ;;
      esac
      deployed="$target/$relative"
      if [[ ! -f "$deployed" ]]; then
        echo "ERROR: missing deployed file: $deployed" >&2
        exit 1
      fi
      if [[ "$(hash_file "$source")" != "$(hash_file "$deployed")" ]]; then
        echo "ERROR: deployed target hash differs: $relative" >&2
        exit 1
      fi
    done < <(find web -type f -print0)
    echo "OK: deployed target files verified via SHA-256"
    ;;
  *)
    echo "ERROR: unknown CAILAMA_DEPLOY_VERIFY mode: $verify_mode" >&2
    exit 2
    ;;
esac

echo "Deployed web/ to $target"
