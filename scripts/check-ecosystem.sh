#!/usr/bin/env bash
set -euo pipefail

repos=(
  "CaiLama"
  "CaiLama-LLM-Router"
  "CaiLama-Search"
)

echo "== CaiLama-Master ecosystem check =="
echo

echo "-- Master repository --"
root="$(git rev-parse --show-toplevel)"
echo "$root"
git status --short
echo

echo "-- Master index checks --"
tracked_subrepos="$(git ls-files | grep -E '^(CaiLama|CaiLama-LLM-Router|CaiLama-Search)/' || true)"
if [[ -n "$tracked_subrepos" ]]; then
  echo "ERROR: sub-repository files are tracked in master:"
  echo "$tracked_subrepos"
  exit 1
fi
echo "OK: no sub-repository files tracked by master"

tracked_env="$(git ls-files | grep -E '(^|/)\.env($|\.)' || true)"
if [[ -n "$tracked_env" ]]; then
  echo "ERROR: environment files are tracked in master:"
  echo "$tracked_env"
  exit 1
fi
echo "OK: no .env files tracked by master"

tracked_prompts="$(git ls-files | grep -Ei '(^|/).*(prompt|followup|handoff).*' || true)"
if [[ -n "$tracked_prompts" ]]; then
  echo "ERROR: prompt or handoff files are tracked in master:"
  echo "$tracked_prompts"
  exit 1
fi
echo "OK: no prompt/handoff files tracked by master"
echo

echo "-- Master content checks --"
required_files=(
  "AGENTS.md"
  "README.md"
  "TODO.md"
  "hinweise.md"
  "docs/ecosystem-map.md"
  "docs/ecosystem-reference.md"
  "docs/data/ecosystem.json"
  "docs/db-api.plan.md"
  "docs/integrations.md"
  "docs/local-setup.md"
  "docs/orchestration.md"
  "docs/quality.md"
  "docs/roadmap.md"
  "docs/runtime-projects.md"
  "docs/website.md"
  "scripts/deploy-website.sh"
  "scripts/update-runtime-projects.sh"
  "web/assets/styles.css"
  "web/.htaccess"
  "web/api/.htaccess"
  "web/api/public/index.php"
  "web/api_app/.htaccess"
  "web/api_app/bootstrap.php"
  "web/api_app/config.php"
  "web/api_app/Controllers/StatusController.php"
  "web/api_app/Http/Request.php"
  "web/api_app/Response.php"
  "web/api_app/Router.php"
  "web/architecture.php"
  "web/data/ecosystem.json"
  "web/ecosystem-reference.md"
  "web/index.php"
  "web/llms.txt"
  "web/operations.php"
  "web/projects.php"
  "web/reference.php"
  "web/roadmap.php"
)

for path in "${required_files[@]}"; do
  if [[ ! -f "$path" ]]; then
    echo "ERROR: required master file is missing: $path"
    exit 1
  fi
done
echo "OK: required master files exist"

if cmp -s "docs/ecosystem-reference.md" "web/ecosystem-reference.md"; then
  echo "OK: LLM markdown reference is synced between docs/ and web/"
else
  echo "ERROR: docs/ecosystem-reference.md differs from web/ecosystem-reference.md"
  exit 1
fi

if cmp -s "docs/data/ecosystem.json" "web/data/ecosystem.json"; then
  echo "OK: machine-readable JSON is synced between docs/ and web/"
else
  echo "ERROR: docs/data/ecosystem.json differs from web/data/ecosystem.json"
  exit 1
fi

web_target="/srv/cailama-web/public"
if [[ -d "$web_target" ]]; then
  deployed_mismatch=0
  while IFS= read -r -d '' source; do
    relative="${source#web/}"
    deployed="$web_target/$relative"
    if [[ ! -f "$deployed" ]] || ! cmp -s "$source" "$deployed"; then
      echo "WARN: deployed website differs for $relative"
      deployed_mismatch=1
    fi
  done < <(find web -type f -print0)
  if [[ "$deployed_mismatch" -eq 0 ]]; then
    echo "OK: deployed website matches web/"
  fi
else
  echo "WARN: web target does not exist: $web_target"
fi
echo

echo "-- Ignore checks --"
for repo in "${repos[@]}"; do
  if git check-ignore -q "$repo"; then
    echo "OK: $repo is ignored by master .gitignore"
    git check-ignore -v "$repo" || true
  else
    echo "WARN: $repo is NOT ignored by master .gitignore"
  fi
done
echo

echo "-- Sub-repository checks --"
for repo in "${repos[@]}"; do
  echo
  echo "## $repo"

  if [[ ! -d "$repo" ]]; then
    echo "MISSING: directory does not exist"
    continue
  fi

  if [[ ! -d "$repo/.git" ]]; then
    echo "WARN: directory exists but has no .git directory"
    continue
  fi

  echo "OK: own .git directory found"
  git -C "$repo" status --short
done

echo
echo "Done."
