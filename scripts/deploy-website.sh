#!/usr/bin/env bash
set -euo pipefail

target="${1:-/srv/cailama-web/public}"
root="$(git rev-parse --show-toplevel)"

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
  if ! cmp -s "$source" "$deployed"; then
    echo "ERROR: deployed file differs: $relative" >&2
    exit 1
  fi
done < <(find web -type f -print0)

echo "Deployed web/ to $target"
