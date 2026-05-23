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
python3 -m json.tool docs/data/ecosystem.json >/dev/null
python3 -m json.tool web/data/ecosystem.json >/dev/null
cmp -s docs/ecosystem-reference.md web/ecosystem-reference.md
cmp -s docs/data/ecosystem.json web/data/ecosystem.json
git diff --check
```

Bei Trainingsfokus- oder Website-Aenderungen zusaetzlich pruefen, dass
externe Vergleichsnotizen nur als abgeleitete Anforderungen einfliessen.
Konkrete Fremdproduktnamen aus solchen Notizen werden nicht in Repository,
Website, Sitemap, JSON oder LLM-Referenz uebernommen.

Der Live-Website-Check ist im Standardlauf deaktiviert, damit der reine
Repo-Check keine Runtime- oder Webspace-Abhaengigkeit hat. Bewusster
HTTPS-Live-Check ohne Mount-Zugriff:

```bash
CAILAMA_CHECK_DEPLOYED_WEBSITE=1 bash scripts/check-ecosystem.sh
```

Das Website-Deploy-Skript nutzt fuer das Live-Ziel natives SFTP und prueft
danach statische oeffentliche Dateien per HTTPS-Hash. Es liest keinen
Webspace-Mount.

## Master-Index-Regeln

Der Master-Index darf nicht enthalten:

- Dateien aus `CaiLama/`, `CaiLama-LLM-Router/` oder `CaiLama-Search/`,
- `.env` oder `.env.*`,
- lose Prompt-/Handoff-/Followup-Dateien ausserhalb von `TODO.md` oder
  klar benannten Skills,
- Secrets, Tokens, Zertifikate oder lokale Credential-Dateien.

Ausnahme fuer maschinenlesbare Doku: `docs/data/ecosystem.json` und
`web/data/ecosystem.json` sind trotz globaler `data/`-Ignore-Regel bewusst
versioniert.

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

Master-`TODO.md` ist die offene Koordinationsliste fuer den Master.
Operative Folgearbeit in Unter-Repos gehoert in deren jeweilige `TODO.md`.
Erledigte TODO-Punkte werden nur geloescht, wenn der Nutzer das ausdruecklich
verlangt; TODO ist nicht gleich Handoff.

Die Unterprojekt-READMEs verweisen auf die gemeinsame Master-Doku:

- `https://cailama.org/reference.php`
- `https://cailama.org/llms.txt`
- `https://cailama.org/ecosystem-reference.md`
- `https://cailama.org/data/ecosystem.json`

Bei neuer Cross-Repo-Arbeit muessen mindestens diese Angaben vorhanden sein:

- Ziel-Repo,
- Schnittstelle,
- erwartetes Ergebnis,
- Akzeptanzkriterien,
- geeigneter Check.

Fuer produktnahe Analyse-, Training-, Search- oder Router-Arbeit gehoert
zusaetzlich ein messbarer Bezug in den Master-Benchmark-Rahmen: betroffene
Benchmark-Familie, Dataset-Regel, Datenschutzgrenze und erwartete Metrik.
Bei Modellrollen gehoeren mindestens Dauer, Input-/Thinking-/Output-Tokens,
Qualitaet, Aufgabenloesung, Logikfehler und A/B-Praeferenz in den Feedback-
Rahmen. Das geschuetzte Website-Feedback darf keine Rohprompts, vollstaendige
private Partien, lokalen Pfade oder Secrets speichern.
