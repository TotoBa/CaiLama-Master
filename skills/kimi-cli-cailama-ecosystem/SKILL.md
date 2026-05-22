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
begonnen wird.
```
