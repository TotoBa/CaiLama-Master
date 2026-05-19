# Kimi-Folgeplan - CaiLama-Master

Dieser Plan ist ein versionierbarer Handoff fuer weitere Kimi-Arbeit. Er ist
keine Prompt-Datei und enthaelt keine Secrets.

## Vor Arbeitsbeginn lesen

1. `AGENTS.md`
2. `README.md`
3. `TODO.md`
4. `docs/ecosystem-map.md`
5. `docs/orchestration.md`
6. `status.plan.cailama.md`
7. `master-repo-orchestration.plan.md`

## Arbeitsbereich

Nur Master-Repo-Dateien bearbeiten:

- `.gitignore`
- `AGENTS.md`
- `README.md`
- `TODO.md`
- `docs/`
- `scripts/`
- `*.plan.md`

Die Ordner `CaiLama/`, `CaiLama-LLM-Router/` und `CaiLama-Search/` sind eigene
Repos und bleiben im Master ignoriert.

## Naechste Aufgaben

- [ ] `scripts/check-ecosystem.sh` ausfuehren, wenn sich lokale Repo-Staende
  geaendert haben.
- [ ] `TODO.md` aktualisieren, wenn neue Cross-Repo-Arbeit sichtbar wird.
- [ ] `docs/ecosystem-map.md` nur aktualisieren, wenn sich Repo-Rollen oder
  Schnittstellen aendern.
- [ ] `docs/orchestration.md` nur aktualisieren, wenn sich Arbeitsregeln oder
  Pruefablauf aendern.
- [ ] Keine Prompt-Dateien versionieren; lokale Prompts bleiben ignoriert.

## Abschlusspruefung

```bash
git status --short
git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search
bash scripts/check-ecosystem.sh
git diff --check
```
