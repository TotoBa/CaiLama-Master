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

tracked_local_config="$(git ls-files | grep -E '^web/api_app/config\.local\.php$' || true)"
if [[ -n "$tracked_local_config" ]]; then
  echo "ERROR: local web config is tracked in master:"
  echo "$tracked_local_config"
  exit 1
fi
echo "OK: real web local config is not tracked by master"

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
  "skills/kimi-cli-cailama-ecosystem/SKILL.md"
  "web/assets/styles.css"
  "web/.htaccess"
  "web/account.php"
  "web/api/.htaccess"
  "web/api/public/index.php"
  "web/api_app/.htaccess"
  "web/api_app/Auth/AuthService.php"
  "web/api_app/Auth/SessionManager.php"
  "web/api_app/bootstrap.php"
  "web/api_app/config.php"
  "web/api_app/config.local.sample.php"
  "web/api_app/Controllers/StatusController.php"
  "web/api_app/Db/ConnectionFactory.php"
  "web/api_app/Http/Request.php"
  "web/api_app/init.php"
  "web/api_app/Response.php"
  "web/api_app/Router.php"
  "web/api_app/schema/cailama-data.sql"
  "web/architecture.php"
  "web/data/ecosystem.json"
  "web/ecosystem-reference.md"
  "web/index.php"
  "web/login.php"
  "web/llms.txt"
  "web/logout.php"
  "web/operations.php"
  "web/projects.php"
  "web/robots.txt"
  "web/reference.php"
  "web/roadmap.php"
  "web/sitemap.xml"
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

python3 - <<'PY'
import sys
import xml.etree.ElementTree as ET

root = ET.parse("web/sitemap.xml").getroot()
namespace = {"sm": "http://www.sitemaps.org/schemas/sitemap/0.9"}
urls = [
    loc.text
    for loc in root.findall("sm:url/sm:loc", namespace)
    if loc.text is not None
]
if not urls:
    print("ERROR: web/sitemap.xml contains no URLs")
    sys.exit(1)
for url in urls:
    if not url.startswith("https://cailama.org/"):
        print(f"ERROR: sitemap URL is not canonical: {url}")
        sys.exit(1)
if "https://cailama.org/" not in urls:
    print("ERROR: sitemap misses https://cailama.org/")
    sys.exit(1)
PY
echo "OK: sitemap.xml is valid and uses canonical URLs"

if grep -Fxq "Sitemap: https://cailama.org/sitemap.xml" "web/robots.txt"; then
  echo "OK: robots.txt references the sitemap"
else
  echo "ERROR: robots.txt does not reference the sitemap"
  exit 1
fi

if [[ "${CAILAMA_CHECK_DEPLOYED_WEBSITE:-0}" == "1" ]]; then
  curl -fsS --max-time 8 "https://cailama.org/" >/dev/null
  curl -fsS --max-time 8 "https://cailama.org/robots.txt" | grep -Fxq "Sitemap: https://cailama.org/sitemap.xml"
  curl -fsS --max-time 8 "https://cailama.org/sitemap.xml" | grep -Fq "https://cailama.org/"
  curl -fsS --max-time 8 "https://cailama.org/data/ecosystem.json" | python3 -m json.tool >/dev/null
  echo "OK: deployed website responds over HTTPS with robots, sitemap and JSON"
else
  echo "SKIP: live website HTTP check disabled; set CAILAMA_CHECK_DEPLOYED_WEBSITE=1 to enable"
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
