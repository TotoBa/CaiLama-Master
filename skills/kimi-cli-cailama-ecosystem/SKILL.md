# Kimi CLI CaiLama Ecosystem Skill

Use this skill when `kimi-cli` works in any CaiLama repository and needs the
larger ecosystem context before implementing a repo-local task.

## Purpose

Kimi should understand the four-repo system without reading local secrets or
touching live services:

- `CaiLama-Master`: coordination, website, docs, checks.
- `CaiLama`: product core for chess analysis, profiles, training and agent CLI.
- `CaiLama-LLM-Router`: OpenAI-compatible model routing, aliases and fallback.
- `CaiLama-Search`: search, DWZ and RAG API backed by Meilisearch.

## Required Reading

From the current repository, read:

- `AGENTS.md`
- `README.md`
- `TODO.md`
- Relevant module READMEs, docs and tests for the first open TODO item.

Then read the public ecosystem documentation:

- `https://cailama.org/llms.txt`
- `https://cailama.org/ecosystem-reference.md`
- `https://cailama.org/data/ecosystem.json`
- `https://cailama.org/reference.php`

If repository-local docs disagree with the public website, prefer the local
repo for implementation details and record the documentation mismatch in the
current repo `TODO.md` unless it is small enough to fix immediately.

## Boundaries

- Do not read `.env`, `*.login.toml`, local runtime configs, local database
  dumps, Meilisearch data, logs, tokens or credentials.
- Do not call live Router, Search, database, DGT hardware, LLM providers,
  crawlers or DWZ imports unless the user explicitly asks for that live action.
- Do not write outside the current repository.
- Do not create separate handoff or prompt files. Operational follow-up belongs
  in `TODO.md`.
- Completed TODO items may be marked with `[x]`, but they may only be deleted
  when the user explicitly requests a TODO cleanup. TODO is not Handoff.

## Runtime Deployment Without Secrets

Use these steps only when the user explicitly asks for a local runtime deploy
or a runtime smoke. The deployment scripts may read local operator config, but
Kimi must not print, inspect, copy, summarize or commit those secret files.

From `CaiLama-Master`, first run a dry-run from any current directory:

```bash
/path/to/CaiLama-Master/scripts/update-runtime-projects.sh --dry-run all
```

Then deploy the selected runtime copy:

```bash
scripts/update-runtime-projects.sh --install --restart all
```

Notes:

- The script resolves the Master root from its own location, not from the
  caller's current Git repository. It is safe to invoke from a sub-repo.
- `--install` installs each repo with its test/development extras so runtime
  smoke tests have `pytest` available.
- Runtime targets must be git-free. If a target contains `.git`, stop and
  report the problem instead of deleting it.
- Router/Search restarts may use local user services or runtime processes.
  Do not read their service files if they contain local secrets.
- Validate with secret-free smokes only, for example help commands, offline
  pytest subsets, `/task list` with `--fake-llm`, Router `/health`, Search
  `/healthz` or isolated Docker/goldset smokes. Do not run live imports,
  crawlers, provider LLMs, DGT hardware, real DBs or external network tasks
  unless the user explicitly asks for that live action.

## Website Deployment Without Secrets

Use these steps only when the user explicitly asks for a website deploy.
The website deploy script may read local operator config such as
`~/.config/cailama/web-deploy.env` and optional password files, but Kimi must
never `cat`, quote, summarize or commit their contents.

Before deploy:

```bash
bash scripts/check-ecosystem.sh
```

If `web-smarty/vendor/autoload.php` is missing, install the documented
dependency without committing vendor files:

```bash
(cd web-smarty && composer install --no-dev --optimize-autoloader)
```

Deploy and verify:

```bash
scripts/deploy-website.sh
CAILAMA_CHECK_DEPLOYED_WEBSITE=1 bash scripts/check-ecosystem.sh
```

Rules:

- `web/` deploys to the public document root; `web-smarty/` deploys to the
  private Smarty app directory.
- Smarty itself is not versioned. Keep `web-smarty/vendor/`, caches and local
  config ignored.
- Do not expose DB credentials, SFTP targets, passwords, API keys or local
  provider paths in logs, docs, TODOs or commits.
- If schema or DB-API deployment is needed, use the existing scripts/endpoints
  as operator actions only; report success/failure, not credentials.
- Visible German website text uses real umlauts and `ß`.

## Known Kimi Pitfalls

- `update-runtime-projects.sh` must be invoked from the Master script path or
  as `scripts/update-runtime-projects.sh` in Master. It no longer relies on
  the caller's `git rev-parse`, because calling from `CaiLama/` previously
  resolved `CaiLama/CaiLama`.
- Runtime `--install` now includes test extras; do not manually install
  `pytest` into runtime unless the script failed and the reason is documented.
- Tests that patch background-job storage should patch the imported module
  object directly, not a fragile string reference.
- Background job cancellation can be waited on with `cancel_job(..., wait=True,
  timeout=...)`; use that for deterministic tests.
- Review-gate decisions can be loaded from either a session directory or a
  direct `review_decision.json` path.
- Benchmark CSV tests should not depend on integer-vs-float spelling unless
  the export contract explicitly requires one representation.
- After parser edits, verify critical CLI flags such as `--min-count`,
  `--review-gate` and `--review-gate-decision` are still present.

## Working Loop

1. Identify the current repository with `pwd` and `git rev-parse --show-toplevel`.
2. Read its `AGENTS.md`.
3. Read `README.md`, `TODO.md` and the relevant local docs/tests.
4. Run `git status --short` and confirm the repository is clean before changes.
5. In `CaiLama-Master` confirm the three sub-repos are ignored with
   `git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search`.
6. Read the public ecosystem docs listed above.
7. Summarize the repo-local task in one or two sentences.
8. Make the smallest coherent implementation change.
9. Run targeted offline tests, `git diff --check`, and in `CaiLama-Master`
   also `bash scripts/check-ecosystem.sh`.
10. Update `TODO.md` only for the changed task and immediate follow-ups.
11. Commit and push the current repository before starting the next TODO step
    when the active handoff asks for stepwise commits.

## Standard Prompt

```text
Du arbeitest im aktuellen CaiLama-Repository. Nutze den
Kimi-CLI-CaiLama-Ecosystem-Skill:

1. Lies zuerst AGENTS.md, README.md und TODO.md im aktuellen Repository.
2. Lies danach https://cailama.org/llms.txt,
   https://cailama.org/ecosystem-reference.md,
   https://cailama.org/data/ecosystem.json und
   https://cailama.org/reference.php.
3. Lies nur die fuer den ersten offenen TODO-Punkt relevanten lokalen Module,
   Docs und Tests.

Arbeite dann die offenen Punkte in TODO.md von oben nach unten ab. Pro Schritt
genau eine kleine, testbare Aenderung machen. Keine Secrets lesen oder
ausgeben, keine Live-Dienste kontaktieren, keine separaten Handoff-Dateien
anlegen. Erledigte TODOs nur markieren, nicht loeschen, ausser der Nutzer
fordert eine Bereinigung ausdruecklich an. Nach jeder erledigten Aufgabe
gezielt offline testen, TODO.md aktualisieren, git diff --check und git status
--short ausfuehren, dann committen und pushen, bevor der naechste Schritt
begonnen wird. Runtime- und Website-Deploys nur ausfuehren, wenn der Nutzer
sie ausdruecklich beauftragt; dabei die Abschnitte "Runtime Deployment Without
Secrets" und "Website Deployment Without Secrets" dieses Skills strikt
befolgen.
```
