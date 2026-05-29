#!/usr/bin/env bash
set -euo pipefail

root="$(git rev-parse --show-toplevel)"
private_dir="${CAILAMA_PRIVATE_CONFIG_DIR:-$HOME/.config/cailama}"
database_ini="${CAILAMA_DATABASES_INI:-$private_dir/databases.ini}"
key_file="${CAILAMA_WEB_API_KEYS_FILE:-$private_dir/web-api.keys}"
server_config="${CAILAMA_WEB_API_PRIVATE_CONFIG:-$private_dir/web-api/config.local.php}"
client_env="${CAILAMA_DB_API_CLIENT_ENV:-$private_dir/cailama-db-api.env}"
mysql_dir="$private_dir/mysql"
deploy_config="${CAILAMA_WEB_DEPLOY_CONFIG:-$HOME/.config/cailama/web-deploy.env}"
allow_reset=0
source_config=""
do_normalize=0
do_generate_keys=0
do_write_configs=0
do_deploy_private=0
do_setup_databases=0
setup_target="all"
do_retire_source=0
set_provider_database=""
set_provider_host=""
set_provider_user=""
set_provider_password_file=""

usage() {
  cat <<'USAGE'
Usage:
  scripts/setup-webspace-db-api.sh --source PATH --all [--allow-reset] [--retire-source]
  scripts/setup-webspace-db-api.sh --source PATH --normalize
  scripts/setup-webspace-db-api.sh --generate-keys --write-configs
  scripts/setup-webspace-db-api.sh --deploy-private
  scripts/setup-webspace-db-api.sh --setup-databases [local|provider|all]
  scripts/setup-webspace-db-api.sh --set-provider-database NAME
  scripts/setup-webspace-db-api.sh --set-provider-host HOST
  scripts/setup-webspace-db-api.sh --set-provider-user USER
  scripts/setup-webspace-db-api.sh --set-provider-password-file PATH

Single-database mode (v0.6.0+):
  One DB per PHP process. Login/users tables live in the same database as
  application data. The --setup-databases option no longer has provider-auth
  or provider-cailama sub-targets.

The script keeps secrets out of the repository. Real config files are written
below ~/.config/cailama by default and the server config is uploaded to a
non-public webspace directory.

Local schemas are applied with the local MySQL client. Provider schemas are
applied through protected POST endpoints in the PHP API on the webspace,
because the provider database is only reachable from there.

Environment:
  CAILAMA_PRIVATE_CONFIG_DIR      default: ~/.config/cailama
  CAILAMA_DATABASES_INI           default: $CAILAMA_PRIVATE_CONFIG_DIR/databases.ini
  CAILAMA_WEB_API_KEYS_FILE       default: $CAILAMA_PRIVATE_CONFIG_DIR/web-api.keys
  CAILAMA_WEB_API_PRIVATE_CONFIG  default: $CAILAMA_PRIVATE_CONFIG_DIR/web-api/config.local.php
  CAILAMA_ORIGIN_BASE_URL         default: https://server.cailama.org
  CAILAMA_ORIGIN_PROXY_KEY        required for console proxy config
  CAILAMA_ORIGIN_HMAC_SECRET      required for console proxy config
  CAILAMA_WEB_DEPLOY_CONFIG       default: ~/.config/cailama/web-deploy.env
  CAILAMA_WEB_PRIVATE_REMOTE_DIR  default: /cailama-private
  CAILAMA_WEB_IMPORT_REMOTE_DIR   default: /cailama-imports
  CAILAMA_PUBLIC_URL              default: https://cailama.org
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --source)
      source_config="${2:-}"
      shift
      ;;
    --normalize)
      do_normalize=1
      ;;
    --generate-keys)
      do_generate_keys=1
      ;;
    --write-configs)
      do_write_configs=1
      ;;
    --deploy-private)
      do_deploy_private=1
      ;;
    --setup-databases)
      do_setup_databases=1
      if [[ "${2:-}" =~ ^(local|provider|all)$ ]]; then
        setup_target="$2"
        shift
      fi
      ;;
    --allow-reset)
      allow_reset=1
      ;;
    --retire-source)
      do_retire_source=1
      ;;
    --set-provider-database)
      set_provider_database="${2:-}"
      shift
      ;;
    --set-provider-host)
      set_provider_host="${2:-}"
      shift
      ;;
    --set-provider-user)
      set_provider_user="${2:-}"
      shift
      ;;
    --set-provider-password-file)
      set_provider_password_file="${2:-}"
      shift
      ;;
    --all)
      do_normalize=1
      do_generate_keys=1
      do_write_configs=1
      do_deploy_private=1
      do_setup_databases=1
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

if [[ "$do_normalize$do_generate_keys$do_write_configs$do_deploy_private$do_setup_databases$do_retire_source" == "000000" && -z "$set_provider_database" && -z "$set_provider_host" && -z "$set_provider_user" && -z "$set_provider_password_file" ]]; then
  usage >&2
  exit 2
fi

umask 077
mkdir -p "$private_dir" "$mysql_dir" "$(dirname "$server_config")"

sha256_token() {
  printf '%s' "$1" | sha256sum | awk '{print $1}'
}

read_key() {
  local name="$1"
  awk -F= -v key="$name" '$1 == key { sub(/^[^=]*=/, ""); print; exit }' "$key_file"
}

require_file() {
  local path="$1" label="$2"
  if [[ ! -f "$path" ]]; then
    echo "ERROR: missing $label: $path" >&2
    exit 2
  fi
}

normalize_config() {
  if [[ -z "$source_config" ]]; then
    if [[ -f "$database_ini" ]]; then
      echo "OK: using existing private normalized database config."
      return 0
    fi
    echo "ERROR: --source is required for first --normalize run" >&2
    exit 2
  fi
  require_file "$source_config" "source database config"
  if grep -q '^\[lokal\]' "$source_config" && grep -q '^\[provider\]' "$source_config"; then
    if [[ "$source_config" != "$database_ini" ]]; then
      cp "$source_config" "$database_ini"
      chmod 600 "$database_ini" 2>/dev/null || true
    fi
    echo "OK: normalized database config already available in private config file."
    return 0
  fi
  php -d detect_unicode=0 -r '
    function fail_msg(string $message): void {
        fwrite(STDERR, "ERROR: " . $message . PHP_EOL);
        exit(2);
    }
    function clean_value(string $value): string {
        $value = trim($value);
        if ((str_starts_with($value, "\"") && str_ends_with($value, "\"")) ||
            (str_starts_with($value, "\x27") && str_ends_with($value, "\x27"))) {
            $value = substr($value, 1, -1);
        }
        return $value;
    }
    function ini_value(string $value): string {
        return "\"" . str_replace(["\\", "\""], ["\\\\", "\\\""], $value) . "\"";
    }
    $source = $argv[1];
    $target = $argv[2];
    $sections = [];
    $current = null;
    foreach (file($source, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim(str_replace("\r", "", $line));
        if ($line === "" || str_starts_with($line, "#") || str_starts_with($line, ";")) {
            continue;
        }
        if (preg_match("/^([A-Za-z0-9_-]+)\s*:\s*$/", $line, $matches)) {
            $current = str_replace("-", "_", strtolower($matches[1]));
            $sections[$current] ??= [];
            continue;
        }
        if (preg_match("/^([A-Za-z0-9_-]+)\s*:\s*(.*)$/", $line, $matches)) {
            if ($current === null) {
                fail_msg("key-value pair before first section");
            }
            $key = str_replace("-", "_", strtolower($matches[1]));
            if ($key === "passwd") {
                $key = "password";
            }
            if ($key === "db") {
                $key = "database";
            }
            $sections[$current][$key] = clean_value($matches[2]);
            continue;
        }
        fail_msg("cannot parse database config line");
    }
    $required = [
        "lokal" => ["host", "user", "password", "database"],
        "provider" => ["host", "user", "password", "database"],
    ];
    foreach ($required as $section => $keys) {
        if (!isset($sections[$section])) {
            fail_msg("missing section " . $section);
        }
        foreach ($keys as $key) {
            if (($sections[$section][$key] ?? "") === "") {
                fail_msg("missing " . $section . "." . $key);
            }
        }
    }
    $out = ["# CaiLama database config. Do not commit or publish.", ""];
    foreach (["lokal", "provider"] as $section) {
        $out[] = "[" . $section . "]";
        foreach ($sections[$section] as $key => $value) {
            $out[] = $key . " = " . ini_value($value);
        }
        $out[] = "";
    }
    $tmp = $target . ".tmp";
    file_put_contents($tmp, implode(PHP_EOL, $out));
    chmod($tmp, 0600);
    rename($tmp, $target);
  ' "$source_config" "$database_ini"
  chmod 600 "$database_ini" 2>/dev/null || true
  echo "OK: normalized database config written to private config file."
}

generate_keys() {
  "$root/scripts/generate-web-api-keys.sh" >/dev/null
  echo "OK: API keys present in private key file."
}

set_database_overrides() {
  require_file "$database_ini" "normalized database config"
  if [[ -z "$set_provider_database" && -z "$set_provider_host" && -z "$set_provider_user" && -z "$set_provider_password_file" ]]; then
    return 0
  fi
  [[ -n "$set_provider_password_file" ]] && require_file "$set_provider_password_file" "provider password file"
  php -r '
    function quote_ini(string $value): string {
        return "\"" . str_replace(["\\", "\""], ["\\\\", "\\\""], $value) . "\"";
    }
    $path = $argv[1];
    $db  = $argv[2];
    $host = $argv[3];
    $user = $argv[4];
    $pwFile = $argv[5];
    $ini = parse_ini_file($path, true, INI_SCANNER_RAW);
    if (!is_array($ini)) {
        fwrite(STDERR, "ERROR: cannot read normalized database config\n");
        exit(2);
    }
    if ($db !== "") {
        $ini["provider"]["database"] = $db;
    }
    if ($host !== "") {
        $ini["provider"]["host"] = $host;
    }
    if ($user !== "") {
        $ini["provider"]["user"] = $user;
    }
    if ($pwFile !== "") {
        $password = trim((string) file_get_contents($pwFile));
        if ($password === "") {
            fwrite(STDERR, "ERROR: provider password file is empty\n");
            exit(2);
        }
        $ini["provider"]["password"] = $password;
    }
    $out = ["# CaiLama database config. Do not commit or publish.", ""];
    foreach ($ini as $section => $values) {
        $out[] = "[" . $section . "]";
        foreach ($values as $key => $value) {
            $out[] = $key . " = " . quote_ini((string) $value);
        }
        $out[] = "";
    }
    $tmp = $path . ".tmp";
    file_put_contents($tmp, implode(PHP_EOL, $out));
    chmod($tmp, 0600);
    rename($tmp, $path);
  ' "$database_ini" "$set_provider_database" "$set_provider_host" "$set_provider_user" "$set_provider_password_file"
  chmod 600 "$database_ini" 2>/dev/null || true
  echo "OK: private database config overrides applied."
}

write_mysql_cnf() {
  local name="$1" host="$2" user="$3" password="$4" file
  file="$mysql_dir/$name.cnf"
  {
    echo "[client]"
    printf 'host=%s\n' "$host"
    printf 'user=%s\n' "$user"
    printf 'password=%s\n' "$password"
    echo "default-character-set=utf8mb4"
  } > "$file"
  chmod 600 "$file" 2>/dev/null || true
}

ini_get() {
  local section="$1" key="$2"
  php -r '
    $ini = parse_ini_file($argv[1], true, INI_SCANNER_RAW);
    echo $ini[$argv[2]][$argv[3]] ?? "";
  ' "$database_ini" "$section" "$key"
}

write_configs() {
  require_file "$database_ini" "normalized database config"
  require_file "$key_file" "API key file"

  local status_key import_key reset_key admin_key
  status_key="$(read_key CAILAMA_DB_API_STATUS_KEY)"
  import_key="$(read_key CAILAMA_DB_API_IMPORT_KEY)"
  reset_key="$(read_key CAILAMA_DB_API_RESET_KEY)"
  admin_key="$(read_key CAILAMA_DB_API_ADMIN_KEY)"
  if [[ -z "$status_key" || -z "$import_key" || -z "$reset_key" || -z "$admin_key" ]]; then
    echo "ERROR: key file is incomplete; run --generate-keys" >&2
    exit 2
  fi

  local local_host local_user local_pass local_db
  local provider_host provider_user provider_pass provider_db
  local origin_base_url origin_proxy_key origin_hmac_secret
  local_host="$(ini_get lokal host)"
  local_user="$(ini_get lokal user)"
  local_pass="$(ini_get lokal password)"
  local_db="$(ini_get lokal database)"
  provider_host="$(ini_get provider host)"
  provider_user="$(ini_get provider user)"
  provider_pass="$(ini_get provider password)"
  provider_db="$(ini_get provider database)"
  if [[ -n "${CAILAMA_PROVIDER_DATABASE:-}" ]]; then
    provider_db="$CAILAMA_PROVIDER_DATABASE"
  fi
  if [[ -n "${CAILAMA_PROVIDER_PHP_HOST:-}" ]]; then
    provider_host="$CAILAMA_PROVIDER_PHP_HOST"
  fi
  origin_base_url="${CAILAMA_ORIGIN_BASE_URL:-https://server.cailama.org}"
  origin_proxy_key="${CAILAMA_ORIGIN_PROXY_KEY:-}"
  origin_hmac_secret="${CAILAMA_ORIGIN_HMAC_SECRET:-}"

  write_mysql_cnf local-main "$local_host" "$local_user" "$local_pass"
  write_mysql_cnf provider-main "$provider_host" "$provider_user" "$provider_pass"

  php -r '
    $target = $argv[1];
    $allowReset = $argv[2] === "1";
    $values = json_decode($argv[3], true, flags: JSON_THROW_ON_ERROR);
    $export = static fn($value) => var_export($value, true);
    $config = [
        "api_tokens" => [
            ["name" => "status", "hash" => "sha256:" . $values["status_hash"], "scopes" => ["status:read"]],
            ["name" => "import", "hash" => "sha256:" . $values["import_hash"], "scopes" => ["db_import:write"]],
            ["name" => "reset", "hash" => "sha256:" . $values["reset_hash"], "scopes" => ["db_import:reset"]],
            ["name" => "admin", "hash" => "sha256:" . $values["admin_hash"], "scopes" => ["status:read", "db_import:write", "db_import:reset", "admin"]],
        ],
        "imports" => [
            "enabled" => true,
            "drop_dir" => "../../cailama-imports",
            "filename" => "cailama-import.sql.gz",
            "allowed_extensions" => ["sql", "sql.gz"],
            "max_file_bytes" => 2147483648,
            "allow_reset" => $allowReset,
            "max_execution_seconds" => 1800,
        ],
        "auth" => ["enabled" => true],
        "origin" => [
            "base_url" => $values["origin_base_url"],
            "proxy_key" => $values["origin_proxy_key"],
            "hmac_secret" => $values["origin_hmac_secret"],
            "timeout_seconds" => 20,
        ],
        "databases" => [
            "cailama" => [
                "enabled" => true,
                "dsn" => "mysql:host=" . $values["provider_host"] . ";dbname=" . $values["provider_db"] . ";charset=utf8mb4",
                "user" => $values["provider_user"],
                "password" => $values["provider_pass"],
            ],
        ],
    ];
    $body = "<?php\n";
    $body .= "declare(strict_types=1);\n\n";
    $body .= "# Single-database mode: login/users and app data share one DB.\n";
    $body .= "return " . $export($config) . ";\n";
    $tmp = $target . ".tmp";
    file_put_contents($tmp, $body);
    chmod($tmp, 0600);
    rename($tmp, $target);
  ' "$server_config" "$allow_reset" "$(
    php -r 'echo json_encode([
      "status_hash" => $argv[1],
      "import_hash" => $argv[2],
      "reset_hash" => $argv[3],
      "admin_hash" => $argv[4],
      "provider_host" => $argv[5],
      "provider_user" => $argv[6],
      "provider_pass" => $argv[7],
      "provider_db" => $argv[8],
      "origin_base_url" => $argv[9],
      "origin_proxy_key" => $argv[10],
      "origin_hmac_secret" => $argv[11],
    ], JSON_UNESCAPED_SLASHES);' \
      "$(sha256_token "$status_key")" \
      "$(sha256_token "$import_key")" \
      "$(sha256_token "$reset_key")" \
      "$(sha256_token "$admin_key")" \
      "$provider_host" "$provider_user" "$provider_pass" "$provider_db" \
      "$origin_base_url" "$origin_proxy_key" "$origin_hmac_secret"
  )"
  chmod 600 "$server_config" 2>/dev/null || true

  {
    echo "# CaiLama DB API client keys. Do not commit or publish."
    printf 'CAILAMA_DB_API_KEY=%s\n' "$status_key"
    printf 'CAILAMA_DB_API_IMPORT_KEY=%s\n' "$import_key"
    printf 'CAILAMA_DB_API_RESET_KEY=%s\n' "$reset_key"
    printf 'CAILAMA_DB_API_ADMIN_KEY=%s\n' "$admin_key"
  } > "$client_env"
  chmod 600 "$client_env" 2>/dev/null || true
  echo "OK: private PHP config, client key env and MySQL defaults files written."
}

load_deploy_config() {
  if [[ -f "$deploy_config" ]]; then
    # Local operator config only; this file must never be committed.
    # shellcheck source=/dev/null
    source "$deploy_config"
  fi
  if [[ -z "${CAILAMA_WEB_SFTP_TARGET:-}" || -z "${CAILAMA_WEB_SFTP_REMOTE_DIR:-}" ]]; then
    echo "ERROR: SFTP deployment requires CAILAMA_WEB_SFTP_TARGET and CAILAMA_WEB_SFTP_REMOTE_DIR" >&2
    exit 2
  fi
}

sftp_quote() {
  local value="$1"
  value="${value//\\/\\\\}"
  value="${value//\"/\\\"}"
  printf '"%s"' "$value"
}

remote_parent() {
  local path="${1%/}" parent
  parent="${path%/*}"
  if [[ -z "$parent" || "$parent" == "$path" ]]; then
    parent="/"
  fi
  printf "%s" "$parent"
}

sftp_batch() {
  local batch_file="$1"
  local args=()
  case "${CAILAMA_WEB_SFTP_IP_VERSION:-}" in
    4) args+=("-4") ;;
    6) args+=("-6") ;;
    "") ;;
    *) echo "ERROR: CAILAMA_WEB_SFTP_IP_VERSION must be 4 or 6" >&2; exit 2 ;;
  esac
  [[ -n "${CAILAMA_WEB_SFTP_PORT:-}" ]] && args+=("-P" "$CAILAMA_WEB_SFTP_PORT")
  [[ -n "${CAILAMA_WEB_SFTP_IDENTITY_FILE:-}" ]] && args+=("-i" "$CAILAMA_WEB_SFTP_IDENTITY_FILE")
  [[ -n "${CAILAMA_WEB_SFTP_CONFIG:-}" ]] && args+=("-F" "$CAILAMA_WEB_SFTP_CONFIG")
  [[ -n "${CAILAMA_WEB_SFTP_KNOWN_HOSTS_FILE:-}" ]] && args+=("-o" "UserKnownHostsFile=$CAILAMA_WEB_SFTP_KNOWN_HOSTS_FILE")
  [[ -n "${CAILAMA_WEB_SFTP_STRICT_HOST_KEY_CHECKING:-}" ]] && args+=("-o" "StrictHostKeyChecking=$CAILAMA_WEB_SFTP_STRICT_HOST_KEY_CHECKING")
  args+=("-o" "ConnectTimeout=${CAILAMA_WEB_SFTP_CONNECT_TIMEOUT:-20}")
  if [[ -n "${CAILAMA_WEB_SFTP_PASSWORD_FILE:-}" ]]; then
    if [[ ! -r "$CAILAMA_WEB_SFTP_PASSWORD_FILE" ]]; then
      echo "ERROR: SFTP password file is not readable" >&2
      exit 2
    fi
    local askpass_dir askpass status
    askpass_dir="$(mktemp -d)"
    askpass="$askpass_dir/askpass"
    printf '%s\n' '#!/usr/bin/env sh' 'cat "$CAILAMA_WEB_SFTP_PASSWORD_FILE"' > "$askpass"
    chmod 700 "$askpass"
    DISPLAY="${DISPLAY:-localhost:0}" \
      SSH_ASKPASS="$askpass" \
      SSH_ASKPASS_REQUIRE=force \
      CAILAMA_WEB_SFTP_PASSWORD_FILE="$CAILAMA_WEB_SFTP_PASSWORD_FILE" \
      setsid -w sftp "${args[@]}" "$CAILAMA_WEB_SFTP_TARGET" < "$batch_file"
    status=$?
    rm -rf "$askpass_dir"
    return "$status"
  fi
  sftp "${args[@]}" -b "$batch_file" "$CAILAMA_WEB_SFTP_TARGET"
}

deploy_private() {
  require_file "$server_config" "private PHP config"
  load_deploy_config
  local public_dir root_dir private_remote import_remote batch
  public_dir="${CAILAMA_WEB_SFTP_REMOTE_DIR%/}"
  root_dir="$(remote_parent "$public_dir")"
  private_remote="${CAILAMA_WEB_PRIVATE_REMOTE_DIR:-/cailama-private}"
  import_remote="${CAILAMA_WEB_IMPORT_REMOTE_DIR:-/cailama-imports}"
  batch="$(mktemp)"
  {
    printf -- "-mkdir %s\n" "$(sftp_quote "$private_remote")"
    printf -- "-mkdir %s\n" "$(sftp_quote "$private_remote/api")"
    printf -- "-mkdir %s\n" "$(sftp_quote "$import_remote")"
    printf "put -p %s %s\n" "$(sftp_quote "$server_config")" "$(sftp_quote "$private_remote/api/config.local.php")"
    printf -- "-chmod 700 %s\n" "$(sftp_quote "$private_remote")"
    printf -- "-chmod 700 %s\n" "$(sftp_quote "$private_remote/api")"
    printf -- "-chmod 700 %s\n" "$(sftp_quote "$import_remote")"
    printf -- "-chmod 600 %s\n" "$(sftp_quote "$private_remote/api/config.local.php")"
    printf -- "-rm %s\n" "$(sftp_quote "$public_dir/api_app/config.local.php")"
  } > "$batch"
  sftp_batch "$batch" >/dev/null
  rm -f "$batch"
  echo "OK: private webspace config deployed outside public webroot."
}

mysql_run() {
  local cnf="$1" database="$2" sql_file="$3" disable_ssl="${4:-0}" args=()
  args+=(--defaults-extra-file="$cnf")
  [[ "$disable_ssl" == "1" ]] && args+=(--skip-ssl)
  mysql "${args[@]}" --database="$database" < "$sql_file"
}

mysql_create_database_if_possible() {
  local cnf="$1" database="$2" disable_ssl="${3:-0}" args=()
  args+=(--defaults-extra-file="$cnf")
  [[ "$disable_ssl" == "1" ]] && args+=(--skip-ssl)
  mysql "${args[@]}" -e "CREATE DATABASE IF NOT EXISTS \`$database\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" >/dev/null 2>&1 || true
}

setup_databases() {
  require_file "$database_ini" "normalized database config"
  local local_db
  local_db="$(ini_get lokal database)"

  case "$setup_target" in
    local|all)
      mysql_create_database_if_possible "$mysql_dir/local-main.cnf" "$local_db" 1
      mysql_run "$mysql_dir/local-main.cnf" "$local_db" "$root/web/api_app/schema/cailama-data.sql" 1
      echo "OK: local database schema applied."
      ;;
  esac
  case "$setup_target" in
    provider|all)
      apply_provider_schema_api all
      echo "OK: provider database schema applied through Webspace API."
      ;;
  esac
}

apply_provider_schema_api() {
  local target="$1" admin_key public_url response status url
  require_file "$key_file" "API key file"
  admin_key="$(read_key CAILAMA_DB_API_ADMIN_KEY)"
  if [[ -z "$admin_key" ]]; then
    echo "ERROR: admin API key missing; run --generate-keys" >&2
    exit 2
  fi
  load_deploy_config
  public_url="${CAILAMA_PUBLIC_URL:-https://cailama.org}"
  public_url="${public_url%/}"
  url="$public_url/api/v1/admin/schema/$target"
  response="$(mktemp)"
  status="$(curl -sS --max-time 30 -o "$response" -w '%{http_code}' -X POST -H "Authorization: Bearer $admin_key" "$url")"
  if [[ "$status" != 2* ]]; then
    echo "ERROR: provider schema setup API failed with HTTP $status" >&2
    cat "$response" >&2
    rm -f "$response"
    exit 1
  fi
  rm -f "$response"
  echo "OK: provider schema target '$target' applied through Webspace API."
}

retire_source() {
  [[ "$do_retire_source" == "1" ]] || return 0
  if [[ -z "$source_config" || ! -f "$source_config" ]]; then
    return 0
  fi
  {
    echo "# CaiLama database secrets were moved to the private local config store."
    echo "# This file intentionally contains no credentials."
  } > "$source_config"
  echo "OK: original source config retired without credentials."
}

[[ "$do_normalize" == "1" ]] && normalize_config
set_database_overrides
[[ "$do_generate_keys" == "1" ]] && generate_keys
[[ "$do_write_configs" == "1" ]] && write_configs
[[ "$do_setup_databases" == "1" ]] && setup_databases
[[ "$do_deploy_private" == "1" ]] && deploy_private
retire_source
