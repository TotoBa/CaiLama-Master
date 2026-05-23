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
  "docs/benchmark-results/README.md"
  "docs/benchmarks.md"
  "docs/integrations.md"
  "docs/local-setup.md"
  "docs/orchestration.md"
  "docs/product-positioning.md"
  "docs/quality.md"
  "docs/roadmap.md"
  "docs/runtime-projects.md"
  "docs/website.md"
  "scripts/deploy-website.sh"
  "scripts/update-runtime-projects.sh"
  "skills/kimi-cli-cailama-ecosystem/SKILL.md"
  "web/_private_app.php"
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
  "web/status.php"
  "web-smarty/README.md"
  "web-smarty/composer.json"
  "web-smarty/bootstrap.php"
  "web-smarty/content/site.php"
  "web-smarty/content/nav.php"
  "web-smarty/content/pages/home.php"
  "web-smarty/content/pages/status.php"
  "web-smarty/content/pages/projects.php"
  "web-smarty/content/pages/architecture.php"
  "web-smarty/content/pages/roadmap.php"
  "web-smarty/content/pages/operations.php"
  "web-smarty/content/pages/reference.php"
  "web-smarty/templates/layouts/base.tpl"
  "web-smarty/templates/partials/head.tpl"
  "web-smarty/templates/partials/nav.tpl"
  "web-smarty/templates/partials/footer.tpl"
  "web-smarty/templates/partials/hero.tpl"
  "web-smarty/templates/pages/home.tpl"
  "web-smarty/templates/pages/status.tpl"
  "web-smarty/templates/pages/projects.tpl"
  "web-smarty/templates/pages/architecture.tpl"
  "web-smarty/templates/pages/roadmap.tpl"
  "web-smarty/templates/pages/operations.tpl"
  "web-smarty/templates/pages/reference.tpl"
  "web-smarty/cache/smarty/.gitkeep"
  "web-smarty/cache/templates_c/.gitkeep"
)

for path in "${required_files[@]}"; do
  if [[ ! -f "$path" ]]; then
    echo "ERROR: required master file is missing: $path"
    exit 1
  fi
done
echo "OK: required master files exist"

footer_contact_sources=(
  "web/account.php"
  "web/login.php"
  "web-smarty/templates/partials/footer.tpl"
)
for path in "${footer_contact_sources[@]}"; do
  if ! grep -Fq 'mailto:' "$path"; then
    echo "ERROR: footer contact link is missing in $path"
    exit 1
  fi
done
echo "OK: public page footers include the contact link"

if find web -name '*.tpl' -print | grep -q .; then
  echo "ERROR: Smarty templates must not live under public web/"
  exit 1
fi
echo "OK: no Smarty templates under public web/"

if ! grep -Fq '"smarty/smarty": "^5.0"' web-smarty/composer.json; then
  echo "ERROR: web-smarty/composer.json must document required dependency smarty/smarty ^5.0"
  exit 1
fi
echo "OK: Smarty dependency version is documented"

if ! find web-smarty/templates -name '*.tpl' -print | grep -q .; then
  echo "ERROR: no Smarty templates found under web-smarty/templates"
  exit 1
fi
echo "OK: Smarty templates exist"

find web web-smarty \
  -path 'web-smarty/vendor' -prune -o \
  -path 'web-smarty/cache/templates_c' -prune -o \
  -path 'web-smarty/cache/smarty' -prune -o \
  -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
echo "OK: public and private website PHP files pass lint"

if [[ -f "web-smarty/vendor/autoload.php" ]]; then
  tmp_dir="$(mktemp -d)"
  php web/index.php > "$tmp_dir/index.html"
  php web/projects.php > "$tmp_dir/projects.html"
  php web/status.php > "$tmp_dir/status.html"
  grep -qi '<!doctype html>' "$tmp_dir/index.html"
  grep -qi 'Vom PGN zur Trainingsaufgabe' "$tmp_dir/index.html"
  grep -qi 'Getrennte Repos, gemeinsame Richtung' "$tmp_dir/projects.html"
  grep -qi 'Vier Repos, ein Trainingssystem' "$tmp_dir/status.html"
  rm -rf "$tmp_dir"
  echo "OK: local Smarty render smokes passed"
else
  echo "SKIP: local Smarty render smokes require untracked web-smarty/vendor/ (smarty/smarty ^5.0)"
fi

blocked_product_page="positioning"".php"
if [[ -e "web/$blocked_product_page" ]]; then
  echo "ERROR: web/$blocked_product_page must not exist; product focus belongs to web/index.php"
  exit 1
fi
if grep -RIn "$blocked_product_page" README.md TODO.md docs scripts web >/dev/null; then
  echo "ERROR: references to $blocked_product_page found; use index.php or status.php"
  exit 1
fi
echo "OK: product focus is the start page, no separate product-focus page references"

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
