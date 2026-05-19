# Qualitaetssicherung

Dieses Dokument definiert die Master-Pruefungen fuer Koordination,
Dokumentation und lokale Orchestrierung.

## Pflichtchecks bei Master-Aenderungen

```bash
pwd
git rev-parse --show-toplevel
git status --short
find . -maxdepth 2 -name .git -type d
git ls-files | grep -E '^(CaiLama|CaiLama-LLM-Router|CaiLama-Search)/' || true
git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search prompt.md
bash scripts/check-ecosystem.sh
git diff --check
```

## Master-Index-Regeln

Der Master-Index darf nicht enthalten:

- Dateien aus `CaiLama/`, `CaiLama-LLM-Router/` oder `CaiLama-Search/`,
- `.env` oder `.env.*`,
- Prompt-/Handoff-/Followup-Dateien,
- Secrets, Tokens, Zertifikate oder lokale Credential-Dateien.

## Statusdateien

Statusdateien duerfen analysieren und planen, muessen aber:

- offene Punkte klar als offen markieren,
- Ziel-Repos eindeutig nennen,
- keine Implementierungsdetails erfinden,
- keine Secrets enthalten,
- lokale Annahmen als Annahmen kennzeichnen.

## Inhaltspruefung

Vor einem Commit im Master soll eine einfache Textsuche ueber versionierte
Master-Dateien laufen:

```bash
git grep -nE '(api[_-]?key|token|secret|password|passwd|BEGIN (RSA|OPENSSH|PRIVATE)|MEILI|OPENAI|ANTHROPIC|GITHUB_TOKEN)' -- . ':!status.plan.cailama.md' ':!master-repo-orchestration.plan.md' || true
```

Treffer in `.gitignore`, Plananalysen oder rein beschreibenden Sicherheitsregeln
sind manuell zu bewerten. Echte Credential-Werte duerfen nicht committed werden.

## TODO-Konsistenz

Master-`TODO.md` ist die abgeschlossene Koordinationsliste fuer den Master.
Operative Folgearbeit in Unter-Repos gehoert in deren jeweilige `TODO.md`.

Bei neuer Cross-Repo-Arbeit muessen mindestens diese Angaben vorhanden sein:

- Ziel-Repo,
- Schnittstelle,
- erwartetes Ergebnis,
- Akzeptanzkriterien,
- geeigneter Check.
