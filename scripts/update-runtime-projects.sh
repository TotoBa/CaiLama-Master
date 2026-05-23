#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
root="$(cd "$script_dir/.." && pwd)"
runtime_root="${CAILAMA_RUNTIME_ROOT:-$HOME}"
ref=""
install_deps=0
restart_services=0
dry_run=0
declare -a selected=()

usage() {
  cat <<'EOF'
Usage:
  scripts/update-runtime-projects.sh [options] [all|cailama|router|search...]

Options:
  --ref REF      Export this git ref from each selected source repo instead of
                 copying the current working tree. Useful for testing a fixed
                 CaiLama version in the runtime folder.
  --install      Create/update .venv in the runtime folder and install the
                 selected project.
  --restart      Restart runtime processes after updating. Router uses the
                 user systemd service when present; Search is started from its
                 runtime folder with uvicorn.
  --dry-run      Print rsync operations without changing files.
  -h, --help     Show this help.

Environment:
  CAILAMA_RUNTIME_ROOT            Base folder for runtime copies. Default: ~
  CAILAMA_RUNTIME_DIR             Runtime copy for CaiLama. Default: ~/CaiLama
  CAILAMA_ROUTER_RUNTIME_DIR      Runtime copy for Router. Default: ~/CaiLama-LLM-Router
  CAILAMA_SEARCH_RUNTIME_DIR      Runtime copy for Search. Default: ~/CaiLama-Search
  CAILAMA_SEARCH_HOST             Bind host for the Search API. Default: 127.0.0.1
  CAILAMA_SEARCH_PORT             Bind port for the Search API. Default: 8080

Runtime folders must not contain .git directories.
EOF
}

display_path() {
  local path="$1"
  if [[ -n "${HOME:-}" && "$path" == "$HOME" ]]; then
    printf '~\n'
  elif [[ -n "${HOME:-}" && "$path" == "$HOME/"* ]]; then
    printf '~/%s\n' "${path#"$HOME/"}"
  else
    printf '%s\n' "$path"
  fi
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --ref)
      if [[ $# -lt 2 ]]; then
        echo "ERROR: --ref needs an argument" >&2
        exit 2
      fi
      ref="$2"
      shift 2
      ;;
    --install)
      install_deps=1
      shift
      ;;
    --restart)
      restart_services=1
      shift
      ;;
    --dry-run)
      dry_run=1
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    all|cailama|router|search)
      selected+=("$1")
      shift
      ;;
    *)
      echo "ERROR: unknown argument: $1" >&2
      usage >&2
      exit 2
      ;;
  esac
done

if [[ "${#selected[@]}" -eq 0 ]]; then
  selected=(all)
fi

declare -a projects=()
for item in "${selected[@]}"; do
  if [[ "$item" == "all" ]]; then
    projects=(cailama router search)
    break
  fi
  projects+=("$item")
done

runtime_dir() {
  case "$1" in
    cailama) printf '%s\n' "${CAILAMA_RUNTIME_DIR:-$runtime_root/CaiLama}" ;;
    router) printf '%s\n' "${CAILAMA_ROUTER_RUNTIME_DIR:-$runtime_root/CaiLama-LLM-Router}" ;;
    search) printf '%s\n' "${CAILAMA_SEARCH_RUNTIME_DIR:-$runtime_root/CaiLama-Search}" ;;
    *) return 1 ;;
  esac
}

source_dir() {
  case "$1" in
    cailama) printf '%s\n' "$root/CaiLama" ;;
    router) printf '%s\n' "$root/CaiLama-LLM-Router" ;;
    search) printf '%s\n' "$root/CaiLama-Search" ;;
    *) return 1 ;;
  esac
}

require_source_repo() {
  local project="$1"
  local source
  source="$(source_dir "$project")"
  if [[ ! -d "$source/.git" ]]; then
    echo "ERROR: source repo missing or not a git repo: $(display_path "$source")" >&2
    exit 1
  fi
}

prepare_source() {
  local project="$1"
  local source
  source="$(source_dir "$project")"

  if [[ -z "$ref" ]]; then
    printf '%s\n' "$source"
    return
  fi

  local tmp
  tmp="$(mktemp -d)"
  git -C "$source" archive "$ref" | tar -x -C "$tmp"
  printf '%s\n' "$tmp"
}

sync_project() {
  local project="$1"
  local target source tmp_source
  target="$(runtime_dir "$project")"

  require_source_repo "$project"
  if [[ -d "$target/.git" ]]; then
    echo "ERROR: runtime folder must not be a git repo: $(display_path "$target")" >&2
    exit 1
  fi

  tmp_source="$(prepare_source "$project")"

  echo "== Update $project =="
  echo "source: $(display_path "$tmp_source")"
  echo "target: $(display_path "$target")"

  if [[ "$dry_run" -eq 1 && ! -d "$target" ]]; then
    echo "DRY-RUN: would create runtime folder: $(display_path "$target")"
    if [[ "$tmp_source" != "$(source_dir "$project")" ]]; then
      rm -r -- "$tmp_source"
    fi
    return
  fi

  mkdir -p "$target"

  local -a rsync_args=(
    -a
    --delete
    --exclude=.git/
    --exclude=.venv/
    --exclude=venv/
    --exclude=env/
    --include=.env.example
    --exclude=.env
    --exclude=.env.*
    --exclude='configs/*.local.yaml'
    --exclude='*.local.toml'
    --exclude='*.login.toml'
    --exclude=logs/
    --exclude=runtime/
    --exclude=storage/
    --exclude=staging/
    --exclude=backups/
    --exclude=meili_data/
    --exclude=meilisearch_data/
    --exclude=__pycache__/
    --exclude=.pytest_cache/
    --exclude=.mypy_cache/
    --exclude=.ruff_cache/
    --exclude=node_modules/
  )
  if [[ "$dry_run" -eq 1 ]]; then
    rsync_args+=(--dry-run --itemize-changes)
  fi

  rsync "${rsync_args[@]}" "$tmp_source/" "$target/"

  if [[ "$tmp_source" != "$(source_dir "$project")" ]]; then
    rm -r -- "$tmp_source"
  fi

  if [[ -d "$target/.git" ]]; then
    echo "ERROR: runtime folder contains .git after sync: $(display_path "$target")" >&2
    exit 1
  fi
}

install_project() {
  local project="$1"
  local target
  target="$(runtime_dir "$project")"
  if [[ "$dry_run" -eq 1 ]]; then
    echo "DRY-RUN: would install $project in $(display_path "$target")/.venv"
    return
  fi

  echo "== Install $project =="
  python3 -m venv --clear "$target/.venv"
  "$target/.venv/bin/python" -m pip install --upgrade pip
  case "$project" in
    cailama)
      (cd "$target" && .venv/bin/python -m pip install -e '.[test]')
      ;;
    router)
      (cd "$target" && .venv/bin/python -m pip install -e '.[dev]')
      ;;
    search)
      (cd "$target" && .venv/bin/python -m pip install -e '.[api,dev]')
      ;;
  esac
}

restart_router() {
  local target
  target="$(runtime_dir router)"
  if [[ "$dry_run" -eq 1 ]]; then
    echo "DRY-RUN: would restart router from $(display_path "$target")"
    return
  fi

  if systemctl --user cat llm-router.service >/dev/null 2>&1; then
    systemctl --user restart llm-router.service
  else
    mkdir -p "$target/logs"
    (cd "$target" && nohup .venv/bin/llm-router serve --config configs/router.local.yaml --host 0.0.0.0 --port 18080 > logs/router.out 2>&1 &)
  fi
}

restart_search() {
  local target host port pids
  target="$(runtime_dir search)"
  host="${CAILAMA_SEARCH_HOST:-127.0.0.1}"
  port="${CAILAMA_SEARCH_PORT:-8080}"

  if [[ "$dry_run" -eq 1 ]]; then
    echo "DRY-RUN: would restart Search API from $(display_path "$target") on $host:$port"
    return
  fi

  if systemctl --user cat cailama-search.service >/dev/null 2>&1; then
    systemctl --user restart cailama-search.service
    return
  fi

  pids="$(ps -eo pid=,args= | awk -v target="$target" '$0 ~ target && $0 ~ /uvicorn/ && $0 ~ /cailama.search_backend.api/ {print $1}')"
  if [[ -n "$pids" ]]; then
    while IFS= read -r pid; do
      [[ -n "$pid" ]] && kill "$pid" 2>/dev/null || true
    done <<< "$pids"
  fi

  mkdir -p "$target/logs"
  (
    cd "$target"
    if [[ -f ".env" ]]; then
      set -a
      # shellcheck disable=SC1091
      source ".env"
      set +a
    fi
    setsid .venv/bin/uvicorn cailama.search_backend.api:app --host "$host" --port "$port" > logs/search-api.out 2>&1 < /dev/null &
    echo "Search API started with PID $!"
  )
}

for project in "${projects[@]}"; do
  sync_project "$project"
  if [[ "$install_deps" -eq 1 ]]; then
    install_project "$project"
  fi
done

if [[ "$restart_services" -eq 1 ]]; then
  for project in "${projects[@]}"; do
    case "$project" in
      router) restart_router ;;
      search) restart_search ;;
      cailama) echo "CaiLama runtime updated; start the desired test command from $(display_path "$(runtime_dir cailama)")." ;;
    esac
  done
fi
