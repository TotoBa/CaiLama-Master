#!/usr/bin/env bash
set -euo pipefail

root="$(git rev-parse --show-toplevel)"
deploy_config="${CAILAMA_WEB_DEPLOY_CONFIG:-$HOME/.config/cailama/web-deploy.env}"
configurable_vars=(
  CAILAMA_WEB_DEPLOY_METHOD
  CAILAMA_WEB_LOCAL_TARGET
  CAILAMA_WEB_SFTP_TARGET
  CAILAMA_WEB_SFTP_REMOTE_DIR
  CAILAMA_WEB_SFTP_IP_VERSION
  CAILAMA_WEB_SFTP_PORT
  CAILAMA_WEB_SFTP_IDENTITY_FILE
  CAILAMA_WEB_SFTP_CONFIG
  CAILAMA_WEB_SFTP_KNOWN_HOSTS_FILE
  CAILAMA_WEB_SFTP_STRICT_HOST_KEY_CHECKING
  CAILAMA_WEB_SFTP_CONNECT_TIMEOUT
  CAILAMA_WEB_SFTP_PASSWORD_FILE
  CAILAMA_PUBLIC_URL
  CAILAMA_DEPLOY_VERIFY
)
saved_env_names=()
deploy_method_from_env=0

for name in "${configurable_vars[@]}"; do
  if [[ -v "$name" ]]; then
    saved_env_names+=("$name")
    printf -v "saved_env_$name" "%s" "${!name}"
  fi
done
if [[ -v CAILAMA_WEB_DEPLOY_METHOD ]]; then
  deploy_method_from_env=1
fi

if [[ -f "$deploy_config" ]]; then
  # Local operator config only; this file must never be committed.
  # shellcheck source=/dev/null
  source "$deploy_config"
fi

for name in "${saved_env_names[@]}"; do
  saved_name="saved_env_$name"
  printf -v "$name" "%s" "${!saved_name}"
done

target_arg="${1:-}"
deploy_method="${CAILAMA_WEB_DEPLOY_METHOD:-}"
public_url="${CAILAMA_PUBLIC_URL:-https://cailama.org}"
public_url="${public_url%/}"
verify_mode="${CAILAMA_DEPLOY_VERIFY:-}"

cd "$root"

if [[ ! -d "web" ]]; then
  echo "ERROR: web/ directory missing" >&2
  exit 1
fi

if [[ -n "$target_arg" && "$deploy_method_from_env" != "1" ]]; then
  deploy_method="local"
fi

if [[ -z "$deploy_method" ]]; then
  if [[ -n "${CAILAMA_WEB_SFTP_TARGET:-}" && -n "${CAILAMA_WEB_SFTP_REMOTE_DIR:-}" ]]; then
    deploy_method="sftp"
  else
    echo "ERROR: no deployment target configured" >&2
    echo "Set CAILAMA_WEB_SFTP_TARGET and CAILAMA_WEB_SFTP_REMOTE_DIR, or pass a local target path." >&2
    exit 2
  fi
fi

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

write_manifest() {
  local manifest="$1"
  find web -type f \
    ! -path "web/api_app/config.local.php" \
    ! -path "web/api_app/config.local.*.php" \
    -printf "%P\n" | LC_ALL=C sort > "$manifest"
}

sftp_quote() {
  local value="$1"
  value="${value//\\/\\\\}"
  value="${value//\"/\\\"}"
  printf '"%s"' "$value"
}

is_safe_relative_path() {
  local relative="$1"
  [[ -n "$relative" && "$relative" != /* && "$relative" != *".."* && "$relative" != *$'\n'* ]]
}

remote_path() {
  local relative="${1:-}"
  local base="${CAILAMA_WEB_SFTP_REMOTE_DIR%/}"
  if [[ -z "$relative" ]]; then
    printf "%s" "$base"
  else
    printf "%s/%s" "$base" "$relative"
  fi
}

sftp_batch() {
  local batch_file="$1"
  local args=()

  case "${CAILAMA_WEB_SFTP_IP_VERSION:-}" in
    4)
      args+=("-4")
      ;;
    6)
      args+=("-6")
      ;;
    "")
      ;;
    *)
      echo "ERROR: CAILAMA_WEB_SFTP_IP_VERSION must be 4 or 6" >&2
      exit 2
      ;;
  esac

  if [[ -n "${CAILAMA_WEB_SFTP_PORT:-}" ]]; then
    args+=("-P" "$CAILAMA_WEB_SFTP_PORT")
  fi
  if [[ -n "${CAILAMA_WEB_SFTP_IDENTITY_FILE:-}" ]]; then
    args+=("-i" "$CAILAMA_WEB_SFTP_IDENTITY_FILE")
  fi
  if [[ -n "${CAILAMA_WEB_SFTP_CONFIG:-}" ]]; then
    args+=("-F" "$CAILAMA_WEB_SFTP_CONFIG")
  fi
  if [[ -n "${CAILAMA_WEB_SFTP_KNOWN_HOSTS_FILE:-}" ]]; then
    args+=("-o" "UserKnownHostsFile=$CAILAMA_WEB_SFTP_KNOWN_HOSTS_FILE")
  fi
  if [[ -n "${CAILAMA_WEB_SFTP_STRICT_HOST_KEY_CHECKING:-}" ]]; then
    args+=("-o" "StrictHostKeyChecking=$CAILAMA_WEB_SFTP_STRICT_HOST_KEY_CHECKING")
  fi
  if [[ -n "${CAILAMA_WEB_SFTP_PASSWORD_FILE:-}" ]]; then
    args+=("-o" "PreferredAuthentications=password,keyboard-interactive" "-o" "NumberOfPasswordPrompts=1")
  fi
  args+=(
    "-o" "ConnectTimeout=${CAILAMA_WEB_SFTP_CONNECT_TIMEOUT:-20}"
    "-o" "ServerAliveInterval=15"
    "-o" "ServerAliveCountMax=2"
  )

  if [[ -n "${CAILAMA_WEB_SFTP_PASSWORD_FILE:-}" ]]; then
    if [[ ! -r "$CAILAMA_WEB_SFTP_PASSWORD_FILE" ]]; then
      echo "ERROR: SFTP password file is not readable" >&2
      exit 2
    fi

    local askpass_dir askpass status
    askpass_dir="$(mktemp -d)"
    askpass="$askpass_dir/askpass"
    cat > "$askpass" <<'SH'
#!/usr/bin/env sh
cat "$CAILAMA_WEB_SFTP_PASSWORD_FILE"
SH
    chmod 700 "$askpass"
    if command -v setsid >/dev/null 2>&1; then
      DISPLAY="${DISPLAY:-localhost:0}" \
        SSH_ASKPASS="$askpass" \
        SSH_ASKPASS_REQUIRE=force \
        CAILAMA_WEB_SFTP_PASSWORD_FILE="$CAILAMA_WEB_SFTP_PASSWORD_FILE" \
        setsid -w sftp "${args[@]}" "$CAILAMA_WEB_SFTP_TARGET" < "$batch_file"
    else
      DISPLAY="${DISPLAY:-localhost:0}" \
        SSH_ASKPASS="$askpass" \
        SSH_ASKPASS_REQUIRE=force \
        CAILAMA_WEB_SFTP_PASSWORD_FILE="$CAILAMA_WEB_SFTP_PASSWORD_FILE" \
        sftp "${args[@]}" "$CAILAMA_WEB_SFTP_TARGET" < "$batch_file"
    fi
    status=$?
    rm -rf "$askpass_dir"
    return "$status"
  fi

  sftp "${args[@]}" -b "$batch_file" "$CAILAMA_WEB_SFTP_TARGET"
}

deploy_local() {
  local target="$1"

  mkdir -p "$target"
  rsync -a --delete \
    --exclude "/api_app/config.local.php" \
    --exclude "/api_app/config.local.*.php" \
    "web/" "$target/"

  echo "Deployed web/ to $target"
}

deploy_sftp() {
  if [[ -z "${CAILAMA_WEB_SFTP_TARGET:-}" || -z "${CAILAMA_WEB_SFTP_REMOTE_DIR:-}" ]]; then
    echo "ERROR: SFTP deployment requires CAILAMA_WEB_SFTP_TARGET and CAILAMA_WEB_SFTP_REMOTE_DIR" >&2
    exit 2
  fi

  local tmp_dir manifest previous_manifest stale_files batch upload_batch delete_batch
  tmp_dir="$(mktemp -d)"
  manifest="$tmp_dir/manifest"
  previous_manifest="$tmp_dir/previous-manifest"
  stale_files="$tmp_dir/stale-files"
  batch="$tmp_dir/download-manifest.sftp"
  upload_batch="$tmp_dir/upload.sftp"
  delete_batch="$tmp_dir/delete-stale.sftp"

  write_manifest "$manifest"

  {
    printf -- "-get %s %s\n" \
      "$(sftp_quote "$(remote_path ".cailama-deploy-manifest")")" \
      "$(sftp_quote "$previous_manifest")"
  } > "$batch"
  sftp_batch "$batch" >/dev/null || true

  if [[ -f "$previous_manifest" ]]; then
    LC_ALL=C sort -u "$previous_manifest" > "$tmp_dir/previous-manifest.sorted"
    comm -23 "$tmp_dir/previous-manifest.sorted" "$manifest" > "$stale_files"
  else
    : > "$stale_files"
  fi

  if [[ -s "$stale_files" ]]; then
    : > "$delete_batch"
    while IFS= read -r relative; do
      if is_safe_relative_path "$relative"; then
        case "$relative" in
          api_app/config.local.php|api_app/config.local.*.php)
            continue
            ;;
        esac
        printf -- "-rm %s\n" "$(sftp_quote "$(remote_path "$relative")")" >> "$delete_batch"
      fi
    done < "$stale_files"

    while IFS= read -r directory; do
      if is_safe_relative_path "$directory"; then
        printf -- "-rmdir %s\n" "$(sftp_quote "$(remote_path "$directory")")" >> "$delete_batch"
      fi
    done < <(
      awk -F/ '
        NF > 1 {
          path = ""
          for (i = 1; i < NF; i++) {
            path = path ? path "/" $i : $i
            print path
          }
        }
      ' "$stale_files" | LC_ALL=C sort -ur
    )

    sftp_batch "$delete_batch" >/dev/null
  fi

  {
    printf -- "-mkdir %s\n" "$(sftp_quote "$(remote_path)")"
    while IFS= read -r directory; do
      printf -- "-mkdir %s\n" "$(sftp_quote "$(remote_path "$directory")")"
    done < <(
      awk -F/ '
        NF > 1 {
          path = ""
          for (i = 1; i < NF; i++) {
            path = path ? path "/" $i : $i
            print path
          }
        }
      ' "$manifest" | LC_ALL=C sort -u
    )
    while IFS= read -r relative; do
      printf "put -p %s %s\n" \
        "$(sftp_quote "web/$relative")" \
        "$(sftp_quote "$(remote_path "$relative")")"
    done < "$manifest"
    printf "put -p %s %s\n" \
      "$(sftp_quote "$manifest")" \
      "$(sftp_quote "$(remote_path ".cailama-deploy-manifest")")"
  } > "$upload_batch"

  sftp_batch "$upload_batch" >/dev/null
  rm -rf "$tmp_dir"
  echo "Deployed web/ to SFTP target $CAILAMA_WEB_SFTP_TARGET"
}

verify_deploy() {
  local local_target="${1:-}"

  if [[ -z "$verify_mode" ]]; then
    case "$deploy_method" in
      sftp)
        verify_mode="http-hash"
        ;;
      local)
        verify_mode="none"
        ;;
    esac
  fi

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
      "favicon.ico"
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
    if [[ -z "$local_target" ]]; then
      echo "ERROR: target-hash verification requires a local target path" >&2
      exit 2
    fi
    while IFS= read -r -d '' source; do
      relative="${source#web/}"
      case "$relative" in
        api_app/config.local.php|api_app/config.local.*.php)
          continue
          ;;
      esac
      deployed="$local_target/$relative"
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
}

case "$deploy_method" in
  local)
    local_target="${target_arg:-${CAILAMA_WEB_LOCAL_TARGET:-}}"
    if [[ -z "$local_target" ]]; then
      echo "ERROR: local deployment requires a target path" >&2
      exit 2
    fi
    deploy_local "$local_target"
    verify_deploy "$local_target"
    ;;
  sftp)
    deploy_sftp
    verify_deploy
    ;;
  *)
    echo "ERROR: unknown CAILAMA_WEB_DEPLOY_METHOD: $deploy_method" >&2
    exit 2
    ;;
esac
