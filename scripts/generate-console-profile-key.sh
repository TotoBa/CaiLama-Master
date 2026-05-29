#!/usr/bin/env bash
set -euo pipefail

private_dir="${CAILAMA_PRIVATE_CONFIG_DIR:-$HOME/.config/cailama}"
out_dir="${CAILAMA_CONSOLE_KEY_DIR:-$private_dir/console-keys}"
profile_key="torsten-baublies-totomanie"
display_name="Torsten Baublies"
training_name="totomanie"
label="console-default"
print_key=0

usage() {
  cat <<'USAGE'
Usage: scripts/generate-console-profile-key.sh [options]

Creates a profile-bound CaiLama console key and a private SQL install file.
The key file is written below ~/.config/cailama by default and must not be
committed or published.

Options:
  --profile-key VALUE     Default: torsten-baublies-totomanie
  --display-name VALUE    Default: Torsten Baublies
  --training-name VALUE   Default: totomanie
  --label VALUE           Default: console-default
  --print-key             Also print the generated key once.
  -h, --help              Show this help.
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --profile-key) profile_key="${2:-}"; shift ;;
    --display-name) display_name="${2:-}"; shift ;;
    --training-name) training_name="${2:-}"; shift ;;
    --label) label="${2:-}"; shift ;;
    --print-key) print_key=1 ;;
    -h|--help) usage; exit 0 ;;
    *) echo "ERROR: unknown argument: $1" >&2; usage >&2; exit 2 ;;
  esac
  shift
done

if [[ -z "$profile_key" || -z "$display_name" || -z "$training_name" || -z "$label" ]]; then
  echo "ERROR: profile-key, display-name, training-name and label are required" >&2
  exit 2
fi

sql_quote() {
  printf "%s" "$1" | sed "s/'/''/g"
}

umask 077
mkdir -p "$out_dir"
token="ck_live_$(openssl rand -base64 48 | tr '+/' '-_' | tr -d '=\n')"
prefix="${token:0:24}"
hash_value="sha256:$(printf "%s" "$token" | sha256sum | awk '{print $1}')"
safe_profile="$(printf "%s" "$profile_key" | tr -c 'A-Za-z0-9_.-' '_')"
key_file="$out_dir/${safe_profile}.${label}.env"
sql_file="$out_dir/${safe_profile}.${label}.install.sql"

cat > "$key_file" <<EOF
# CaiLama console profile key. Do not commit or publish.
CAILAMA_CONSOLE_PROFILE_KEY=$profile_key
CAILAMA_CONSOLE_TRAINING_NAME=$training_name
CAILAMA_CONSOLE_KEY=$token
EOF
chmod 600 "$key_file" 2>/dev/null || true

cat > "$sql_file" <<EOF
INSERT INTO cailama_player_profiles (profile_key, display_name, training_name, status)
VALUES ('$(sql_quote "$profile_key")', '$(sql_quote "$display_name")', '$(sql_quote "$training_name")', 'active')
ON DUPLICATE KEY UPDATE
    display_name = VALUES(display_name),
    training_name = VALUES(training_name),
    status = VALUES(status);

INSERT INTO cailama_console_api_keys (profile_id, label, key_prefix, key_hash, scopes, status)
SELECT id,
       '$(sql_quote "$label")',
       '$(sql_quote "$prefix")',
       '$(sql_quote "$hash_value")',
       JSON_ARRAY('console:all', 'llm:chat', 'search:query', 'jobs:write'),
       'active'
FROM cailama_player_profiles
WHERE profile_key = '$(sql_quote "$profile_key")'
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    scopes = VALUES(scopes),
    status = 'active',
    refused_at = NULL;
EOF
chmod 600 "$sql_file" 2>/dev/null || true

echo "OK: console key file written privately."
echo "OK: SQL install file written privately."
echo "Key file: $key_file"
echo "SQL file: $sql_file"
if [[ "$print_key" == "1" ]]; then
  printf 'Console key: %s\n' "$token"
fi
