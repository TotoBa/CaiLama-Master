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
git rev-parse --show-toplevel
git status --short
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
