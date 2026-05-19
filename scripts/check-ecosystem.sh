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
  "docs/integrations.md"
  "docs/local-setup.md"
  "docs/orchestration.md"
  "docs/quality.md"
  "docs/roadmap.md"
  "docs/website.md"
  "web/index.html"
)

for path in "${required_files[@]}"; do
  if [[ ! -f "$path" ]]; then
    echo "ERROR: required master file is missing: $path"
    exit 1
  fi
done
echo "OK: required master files exist"

if cmp -s "web/index.html" "/srv/cailama-web/public/index.html"; then
  echo "OK: deployed website matches web/index.html"
else
  echo "WARN: deployed website differs from web/index.html"
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
