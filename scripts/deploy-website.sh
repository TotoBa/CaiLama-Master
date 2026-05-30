#!/usr/bin/env bash
set -euo pipefail

root="$(git rev-parse --show-toplevel)"
deploy_config="${CAILAMA_WEB_DEPLOY_CONFIG:-$HOME/.config/cailama/web-deploy.env}"
configurable_vars=(
  CAILAMA_WEB_DEPLOY_METHOD
  CAILAMA_WEB_LOCAL_TARGET
  CAILAMA_WEB_LOCAL_API_TARGET
  CAILAMA_WEB_LOCAL_API_APP_TARGET
  CAILAMA_WEB_LOCAL_SMARTY_TARGET
  CAILAMA_WEB_SFTP_TARGET
  CAILAMA_WEB_SFTP_REMOTE_DIR
  CAILAMA_WEB_SFTP_REMOTE_ROOT
  CAILAMA_WEB_SFTP_REMOTE_API_DIR
  CAILAMA_WEB_SFTP_REMOTE_API_APP_DIR
  CAILAMA_WEB_SFTP_REMOTE_SMARTY_DIR
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
  CAILAMA_DEPLOY_ALLOW_MISSING_VENDOR
  CAILAMA_DEPLOY_CREATE_DIRS
  CAILAMA_DEPLOY_INCLUDE_VENDOR
  CAILAMA_DEPLOY_SMARTY
  CAILAMA_DEPLOY_RESET_SMARTY_CACHE
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

target_arg=""
create_dirs="${CAILAMA_DEPLOY_CREATE_DIRS:-0}"
include_vendor="${CAILAMA_DEPLOY_INCLUDE_VENDOR:-0}"
deploy_smarty="${CAILAMA_DEPLOY_SMARTY:-0}"
reset_smarty_cache="${CAILAMA_DEPLOY_RESET_SMARTY_CACHE:-1}"
deploy_method="${CAILAMA_WEB_DEPLOY_METHOD:-}"
public_url="${CAILAMA_PUBLIC_URL:-https://cailama.org}"
public_url="${public_url%/}"
verify_mode="${CAILAMA_DEPLOY_VERIFY:-}"

usage() {
  cat <<'USAGE'
Usage: scripts/deploy-website.sh [options] [local-target]

Options:
  --create-dirs    Create remote directories before uploading files.
  --with-vendor    Include web-smarty/vendor in the private upload.
  --with-smarty    Upload private web-smarty app files.
  --skip-smarty    Upload public web/ only.
  --no-cache-reset Do not clear remote Smarty/opcache after upload.
  --full           Equivalent to --create-dirs --with-vendor --with-smarty.
  -h, --help       Show this help.

Default SFTP mode uploads public web/ code only, assumes directories already
exist, and skips private Smarty app files and third-party vendor libraries.
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --create-dirs)
      create_dirs=1
      ;;
    --with-vendor|--include-vendor)
      include_vendor=1
      deploy_smarty=1
      ;;
    --with-smarty)
      deploy_smarty=1
      ;;
    --skip-smarty)
      deploy_smarty=0
      ;;
    --no-cache-reset)
      reset_smarty_cache=0
      ;;
    --full)
      create_dirs=1
      include_vendor=1
      deploy_smarty=1
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    --)
      shift
      if [[ $# -gt 0 ]]; then
        target_arg="${1:-}"
        shift
      fi
      break
      ;;
    -*)
      echo "ERROR: unknown deploy option: $1" >&2
      usage >&2
      exit 2
      ;;
    *)
      if [[ -n "$target_arg" ]]; then
        echo "ERROR: only one local target path can be supplied" >&2
        exit 2
      fi
      target_arg="$1"
      ;;
  esac
  shift
done

if [[ "$create_dirs" != "0" && "$create_dirs" != "1" ]]; then
  echo "ERROR: CAILAMA_DEPLOY_CREATE_DIRS must be 0 or 1" >&2
  exit 2
fi
if [[ "$include_vendor" != "0" && "$include_vendor" != "1" ]]; then
  echo "ERROR: CAILAMA_DEPLOY_INCLUDE_VENDOR must be 0 or 1" >&2
  exit 2
fi
if [[ "$deploy_smarty" != "0" && "$deploy_smarty" != "1" ]]; then
  echo "ERROR: CAILAMA_DEPLOY_SMARTY must be 0 or 1" >&2
  exit 2
fi
if [[ "$reset_smarty_cache" != "0" && "$reset_smarty_cache" != "1" ]]; then
  echo "ERROR: CAILAMA_DEPLOY_RESET_SMARTY_CACHE must be 0 or 1" >&2
  exit 2
fi

cd "$root"

if [[ ! -d "web" ]]; then
  echo "ERROR: web/ directory missing" >&2
  exit 1
fi
if [[ ! -d "web-smarty" ]]; then
  echo "ERROR: web-smarty/ directory missing" >&2
  exit 1
fi
if [[ ! -f "web-smarty/bootstrap.php" ]]; then
  echo "ERROR: web-smarty/bootstrap.php missing" >&2
  exit 1
fi
mkdir -p web-smarty/cache/smarty web-smarty/cache/templates_c
if [[ ! -f "web-smarty/vendor/autoload.php" && "${CAILAMA_DEPLOY_ALLOW_MISSING_VENDOR:-0}" != "1" ]]; then
  echo "ERROR: web-smarty/vendor/autoload.php missing" >&2
  echo "Run: cd web-smarty && composer install --no-dev --optimize-autoloader" >&2
  exit 2
fi

if [[ -n "$target_arg" && "$deploy_method_from_env" != "1" ]]; then
  deploy_method="local"
fi

if [[ -z "$deploy_method" ]]; then
  if [[ -n "${CAILAMA_WEB_SFTP_REMOTE_ROOT:-}" ]]; then
    CAILAMA_WEB_SFTP_REMOTE_DIR="${CAILAMA_WEB_SFTP_REMOTE_ROOT%/}/public"
    CAILAMA_WEB_SFTP_REMOTE_API_DIR="${CAILAMA_WEB_SFTP_REMOTE_ROOT%/}/api"
    CAILAMA_WEB_SFTP_REMOTE_SMARTY_DIR="${CAILAMA_WEB_SFTP_REMOTE_ROOT%/}/smarty"
  fi
  if [[ -n "${CAILAMA_WEB_SFTP_TARGET:-}" && -n "${CAILAMA_WEB_SFTP_REMOTE_DIR:-}" ]]; then
    deploy_method="sftp"
  else
    echo "ERROR: no deployment target configured" >&2
    echo "Set CAILAMA_WEB_SFTP_TARGET and CAILAMA_WEB_SFTP_REMOTE_DIR, or pass a local target path." >&2
    exit 2
  fi
fi

if [[ "$deploy_method" == "sftp" ]]; then
  if [[ -n "${CAILAMA_WEB_SFTP_REMOTE_ROOT:-}" ]]; then
    CAILAMA_WEB_SFTP_REMOTE_DIR="${CAILAMA_WEB_SFTP_REMOTE_ROOT%/}/public"
    CAILAMA_WEB_SFTP_REMOTE_API_DIR="${CAILAMA_WEB_SFTP_REMOTE_ROOT%/}/api"
    CAILAMA_WEB_SFTP_REMOTE_SMARTY_DIR="${CAILAMA_WEB_SFTP_REMOTE_ROOT%/}/smarty"
  elif [[ -z "${CAILAMA_WEB_SFTP_REMOTE_SMARTY_DIR:-}" ]]; then
    public_dir="${CAILAMA_WEB_SFTP_REMOTE_DIR%/}"
    if [[ -z "${CAILAMA_WEB_SFTP_REMOTE_API_DIR:-}" ]]; then
      CAILAMA_WEB_SFTP_REMOTE_API_DIR="${public_dir%/*}/api"
    fi
    CAILAMA_WEB_SFTP_REMOTE_SMARTY_DIR="${public_dir%/*}/smarty"
  elif [[ -z "${CAILAMA_WEB_SFTP_REMOTE_API_DIR:-}" ]]; then
    public_dir="${CAILAMA_WEB_SFTP_REMOTE_DIR%/}"
    CAILAMA_WEB_SFTP_REMOTE_API_DIR="${public_dir%/*}/api"
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

verify_http_render_hash() {
  local relative="$1" local_hash remote_hash
  local_hash="$(php "web/$relative" | sha256sum | awk '{print $1}')"
  remote_hash="$(curl -fsS --max-time 12 "$public_url/$relative" | sha256sum | awk '{print $1}')"
  if [[ "$local_hash" != "$remote_hash" ]]; then
    echo "ERROR: deployed HTTP render hash differs: $relative" >&2
    exit 1
  fi
}

write_manifest() {
  local manifest="$1"
  find web -type f \
    ! -path "web/api_app/*" \
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

remote_path_from_base() {
  local base="${1%/}" relative="${2:-}"
  if [[ -z "$relative" ]]; then
    printf "%s" "$base"
  else
    printf "%s/%s" "$base" "$relative"
  fi
}

write_smarty_manifest() {
  local manifest="$1"
  local find_args=(
    web-smarty
    -path "web-smarty/cache/smarty" -prune -o
    -path "web-smarty/cache/templates_c" -prune -o
    -name ".git*" -prune -o
  )
  if [[ "$include_vendor" != "1" ]]; then
    find_args+=(-path "web-smarty/vendor" -prune -o)
  fi
  find "${find_args[@]}" \
    -type f \
    ! -path "web-smarty/composer.lock" \
    -printf "%P\n" | LC_ALL=C sort > "$manifest"
}

filter_previous_smarty_manifest() {
  local source="$1" target="$2"
  if [[ "$include_vendor" == "1" ]]; then
    LC_ALL=C sort -u "$source" > "$target"
  else
    grep -vE '^(vendor/|vendor$)' "$source" | LC_ALL=C sort -u > "$target" || true
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
  local api_target="${CAILAMA_WEB_LOCAL_API_TARGET:-$(dirname "$target")/api}"
  local api_app_target="${CAILAMA_WEB_LOCAL_API_APP_TARGET:-$(dirname "$target")/api-app}"
  local smarty_target="${CAILAMA_WEB_LOCAL_SMARTY_TARGET:-$(dirname "$target")/smarty}"

  mkdir -p "$target"
  mkdir -p "$api_target"
  mkdir -p "$api_app_target"
  mkdir -p "$smarty_target/cache/smarty" "$smarty_target/cache/templates_c"
  rsync -a --delete \
    --exclude "/api_app/" \
    --exclude "/api_app/config.local.php" \
    --exclude "/api_app/config.local.*.php" \
    "web/" "$target/"
  rsync -a --delete "web/api/" "$api_target/"
  rsync -a --delete \
    --exclude "/config.local.php" \
    --exclude "/config.local.*.php" \
    "web/api_app/" "$api_app_target/"
  if [[ "$deploy_smarty" == "1" ]]; then
    local smarty_rsync_args=(
      -a
      --delete
      --exclude "/cache/smarty/*"
      --exclude "/cache/templates_c/*"
      --exclude "/composer.lock"
      --exclude ".git*/"
      --exclude ".git*"
    )
    if [[ "$include_vendor" != "1" ]]; then
      smarty_rsync_args+=(--exclude "/vendor/")
    fi
    rsync "${smarty_rsync_args[@]}" "web-smarty/" "$smarty_target/"
    if [[ "$reset_smarty_cache" == "1" ]]; then
      find "$smarty_target/cache/smarty" "$smarty_target/cache/templates_c" \
        -type f ! -name ".gitkeep" -delete 2>/dev/null || true
      echo "Reset local Smarty cache in $smarty_target/cache"
    fi
  fi

  echo "Deployed web/ to $target"
  echo "Deployed API app to $api_target"
  echo "Deployed private API implementation to $api_app_target"
  if [[ "$deploy_smarty" == "1" ]]; then
    if [[ "$include_vendor" == "1" ]]; then
      echo "Deployed web-smarty/ including vendor to $smarty_target"
    else
      echo "Deployed web-smarty/ app files to $smarty_target (vendor skipped)"
    fi
  else
    echo "SKIP: private web-smarty deployment disabled"
  fi
}

write_api_manifest() {
  local manifest="$1"
  find web/api -type f -printf "api:%P\n" | LC_ALL=C sort > "$manifest"
}

api_remote_path() {
  local relative="${1:-}"
  local base="${CAILAMA_WEB_SFTP_REMOTE_API_DIR%/}"
  if [[ -z "$relative" ]]; then
    printf "%s" "$base"
  else
    printf "%s/%s" "$base" "$relative"
  fi
}

deploy_api_sftp() {
  if [[ -z "${CAILAMA_WEB_SFTP_TARGET:-}" || -z "${CAILAMA_WEB_SFTP_REMOTE_API_DIR:-}" ]]; then
    echo "ERROR: SFTP API deployment requires CAILAMA_WEB_SFTP_REMOTE_API_DIR" >&2
    exit 2
  fi

  local tmp_dir manifest batch
  tmp_dir="$(mktemp -d)"
  manifest="$tmp_dir/api-manifest"
  batch="$tmp_dir/upload-api.sftp"
  write_api_manifest "$manifest"

  {
    if [[ "$create_dirs" == "1" ]]; then
      printf -- "-mkdir %s\n" "$(sftp_quote "$(api_remote_path)")"
      printf -- "-mkdir %s\n" "$(sftp_quote "$(api_remote_path "public")")"
      while IFS= read -r directory; do
        printf -- "-mkdir %s\n" "$(sftp_quote "$(api_remote_path "$directory")")"
      done < <(
        awk -F: '
          $1 == "api" && index($2, "/") {
            path = "public"
            n = split($2, parts, "/")
            for (i = 1; i < n; i++) {
              path = path "/" parts[i]
              print path
            }
          }
        ' "$manifest" | LC_ALL=C sort -u
      )
    fi
    while IFS=: read -r group relative; do
      if [[ "$group" == "api" ]]; then
        printf "put -p %s %s\n" \
          "$(sftp_quote "web/api/$relative")" \
          "$(sftp_quote "$(api_remote_path "$relative")")"
      fi
    done < "$manifest"
  } > "$batch"

  sftp_batch "$batch" >/dev/null
  rm -rf "$tmp_dir"
  echo "Deployed API public dispatcher to SFTP target $CAILAMA_WEB_SFTP_REMOTE_API_DIR"
}

deploy_api_app_sftp() {
  if [[ -z "${CAILAMA_WEB_SFTP_TARGET:-}" ]]; then
    echo "ERROR: SFTP private API deployment requires CAILAMA_WEB_SFTP_TARGET" >&2
    exit 2
  fi
  local public_dir api_app_dir tmp_dir manifest batch
  public_dir="${CAILAMA_WEB_SFTP_REMOTE_DIR%/}"
  api_app_dir="${CAILAMA_WEB_SFTP_REMOTE_API_APP_DIR:-${public_dir%/*}/api-app}"
  tmp_dir="$(mktemp -d)"
  manifest="$tmp_dir/api-app-manifest"
  batch="$tmp_dir/upload-api-app.sftp"
  find web/api_app -type f \
    ! -path "web/api_app/config.local.php" \
    ! -path "web/api_app/config.local.*.php" \
    -printf "%P\n" | LC_ALL=C sort > "$manifest"
  {
    if [[ "$create_dirs" == "1" ]]; then
      printf -- "-mkdir %s\n" "$(sftp_quote "$api_app_dir")"
      while IFS= read -r directory; do
        printf -- "-mkdir %s\n" "$(sftp_quote "$(remote_path_from_base "$api_app_dir" "$directory")")"
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
    fi
    while IFS= read -r relative; do
      printf "put -p %s %s\n" \
        "$(sftp_quote "web/api_app/$relative")" \
        "$(sftp_quote "$(remote_path_from_base "$api_app_dir" "$relative")")"
    done < "$manifest"
  } > "$batch"
  sftp_batch "$batch" >/dev/null
  rm -rf "$tmp_dir"
  echo "Deployed private API implementation to SFTP target"
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
    if [[ "$create_dirs" == "1" ]]; then
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
    fi
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

deploy_smarty_sftp() {
  if [[ -z "${CAILAMA_WEB_SFTP_TARGET:-}" || -z "${CAILAMA_WEB_SFTP_REMOTE_SMARTY_DIR:-}" ]]; then
    echo "ERROR: SFTP private Smarty deployment requires CAILAMA_WEB_SFTP_REMOTE_SMARTY_DIR" >&2
    exit 2
  fi

  local tmp_dir manifest previous_manifest stale_files batch upload_batch delete_batch remote_base
  tmp_dir="$(mktemp -d)"
  manifest="$tmp_dir/smarty-manifest"
  previous_manifest="$tmp_dir/previous-smarty-manifest"
  stale_files="$tmp_dir/stale-smarty-files"
  batch="$tmp_dir/download-smarty-manifest.sftp"
  upload_batch="$tmp_dir/upload-smarty.sftp"
  delete_batch="$tmp_dir/delete-stale-smarty.sftp"
  remote_base="${CAILAMA_WEB_SFTP_REMOTE_SMARTY_DIR%/}"

  write_smarty_manifest "$manifest"

  {
    printf -- "-get %s %s\n" \
      "$(sftp_quote "$(remote_path_from_base "$remote_base" ".cailama-smarty-deploy-manifest")")" \
      "$(sftp_quote "$previous_manifest")"
  } > "$batch"
  sftp_batch "$batch" >/dev/null || true

  if [[ -f "$previous_manifest" ]]; then
    filter_previous_smarty_manifest "$previous_manifest" "$tmp_dir/previous-smarty-manifest.sorted"
    comm -23 "$tmp_dir/previous-smarty-manifest.sorted" "$manifest" > "$stale_files"
  else
    : > "$stale_files"
  fi

  if [[ -s "$stale_files" ]]; then
    : > "$delete_batch"
    while IFS= read -r relative; do
      if is_safe_relative_path "$relative"; then
        printf -- "-rm %s\n" "$(sftp_quote "$(remote_path_from_base "$remote_base" "$relative")")" >> "$delete_batch"
      fi
    done < "$stale_files"

    while IFS= read -r directory; do
      if is_safe_relative_path "$directory"; then
        printf -- "-rmdir %s\n" "$(sftp_quote "$(remote_path_from_base "$remote_base" "$directory")")" >> "$delete_batch"
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
    if [[ "$create_dirs" == "1" ]]; then
      printf -- "-mkdir %s\n" "$(sftp_quote "$remote_base")"
      printf -- "-mkdir %s\n" "$(sftp_quote "$(remote_path_from_base "$remote_base" "cache")")"
      printf -- "-mkdir %s\n" "$(sftp_quote "$(remote_path_from_base "$remote_base" "cache/smarty")")"
      printf -- "-mkdir %s\n" "$(sftp_quote "$(remote_path_from_base "$remote_base" "cache/templates_c")")"
      while IFS= read -r directory; do
        printf -- "-mkdir %s\n" "$(sftp_quote "$(remote_path_from_base "$remote_base" "$directory")")"
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
    fi
    while IFS= read -r relative; do
      printf "put -p %s %s\n" \
        "$(sftp_quote "web-smarty/$relative")" \
        "$(sftp_quote "$(remote_path_from_base "$remote_base" "$relative")")"
    done < "$manifest"
    printf "put -p %s %s\n" \
      "$(sftp_quote "$manifest")" \
      "$(sftp_quote "$(remote_path_from_base "$remote_base" ".cailama-smarty-deploy-manifest")")"
  } > "$upload_batch"

  sftp_batch "$upload_batch" >/dev/null
  rm -rf "$tmp_dir"
  if [[ "$include_vendor" == "1" ]]; then
    echo "Deployed web-smarty/ including vendor to private SFTP target"
  else
    echo "Deployed web-smarty/ app files to private SFTP target (vendor skipped)"
  fi
}

reset_remote_php_cache() {
  if [[ "$deploy_method" != "sftp" ]]; then
    return
  fi

  local tmp_dir reset_name reset_file upload_batch delete_batch output
  tmp_dir="$(mktemp -d)"
  reset_name=".cailama-opcache-reset-$(date +%s)-$$.php"
  reset_file="$tmp_dir/$reset_name"
  upload_batch="$tmp_dir/opcache-reset-upload.sftp"
  delete_batch="$tmp_dir/opcache-reset-delete.sftp"

  cat > "$reset_file" <<'PHP'
<?php
header('Content-Type: text/plain; charset=UTF-8');
if (function_exists('opcache_reset')) {
    echo opcache_reset() ? "opcache_reset=ok\n" : "opcache_reset=failed\n";
} else {
    echo "opcache_reset=unavailable\n";
}
PHP

  printf "put -p %s %s\n" \
    "$(sftp_quote "$reset_file")" \
    "$(sftp_quote "$(remote_path "$reset_name")")" > "$upload_batch"
  sftp_batch "$upload_batch" >/dev/null

  output="$(curl -fsS --max-time 12 "$public_url/$reset_name")"
  if [[ "$output" == "opcache_reset=failed" ]]; then
    echo "ERROR: remote PHP opcache reset failed" >&2
    exit 1
  fi

  printf -- "-rm %s\n" "$(sftp_quote "$(remote_path "$reset_name")")" > "$delete_batch"
  sftp_batch "$delete_batch" >/dev/null || true
  rm -rf "$tmp_dir"
}

reset_remote_smarty_cache() {
  if [[ "$deploy_method" != "sftp" || "$reset_smarty_cache" != "1" ]]; then
    return
  fi

  local tmp_dir reset_name reset_file upload_batch delete_batch output
  tmp_dir="$(mktemp -d)"
  reset_name=".cailama-smarty-cache-reset-$(date +%s)-$$.php"
  reset_file="$tmp_dir/$reset_name"
  upload_batch="$tmp_dir/smarty-cache-reset-upload.sftp"
  delete_batch="$tmp_dir/smarty-cache-reset-delete.sftp"

  cat > "$reset_file" <<'PHP'
<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');

$privateApp = dirname(__DIR__) . '/smarty';
$cacheDirs = [
    $privateApp . '/cache/smarty',
    $privateApp . '/cache/templates_c',
];

$deleted = 0;
foreach ($cacheDirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $entry) {
        $path = $entry->getPathname();
        if ($entry->isFile() && basename($path) !== '.gitkeep') {
            if (@unlink($path)) {
                $deleted++;
            }
        }
    }
}
echo "smarty_cache_deleted=" . $deleted . "\n";
PHP

  printf "put -p %s %s\n" \
    "$(sftp_quote "$reset_file")" \
    "$(sftp_quote "$(remote_path "$reset_name")")" > "$upload_batch"
  sftp_batch "$upload_batch" >/dev/null

  output="$(curl -fsS --max-time 20 "$public_url/$reset_name")"
  echo "$output"

  printf -- "-rm %s\n" "$(sftp_quote "$(remote_path "$reset_name")")" > "$delete_batch"
  sftp_batch "$delete_batch" >/dev/null || true
  rm -rf "$tmp_dir"
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
    reset_remote_php_cache
    reset_remote_smarty_cache
    dynamic_pages=(
      "index.php"
      "status.php"
      "projects.php"
      "architecture.php"
      "roadmap.php"
      "operations.php"
      "reference.php"
    )
    static_files=(
      "robots.txt"
      "sitemap.xml"
      "llms.txt"
      "ecosystem-reference.md"
      "data/ecosystem.json"
      "assets/styles.css"
      "favicon.ico"
    )
    for relative in "${dynamic_pages[@]}"; do
      verify_http_render_hash "$relative"
    done
    for relative in "${static_files[@]}"; do
      verify_http_hash "$relative"
    done
    for page in ""; do
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
        api_app/*)
          continue
          ;;
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
    if [[ "$deploy_smarty" == "1" ]]; then
      deploy_smarty_sftp
    else
      echo "SKIP: private web-smarty deployment disabled"
    fi
    deploy_api_app_sftp
    deploy_api_sftp
    deploy_sftp
    verify_deploy
    ;;
  *)
    echo "ERROR: unknown CAILAMA_WEB_DEPLOY_METHOD: $deploy_method" >&2
    exit 2
    ;;
esac
