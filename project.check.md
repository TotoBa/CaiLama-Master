# Project Check - CaiLama Ecosystem Consistency

Datum: 2026-05-20
Status: behoben

## Sollzustand

- Aktuelle Repositories sind `TotoBa/CaiLama`, `TotoBa/CaiLama-Master`,
  `TotoBa/CaiLama-LLM-Router` und `TotoBa/CaiLama-Search`.
- Alte Namen wie `DGT-Chesstrainer`, `LLM-Router` oder
  `TotoBa/DGT-Chesstrainer` bleiben nur in klar markierten historischen
  Kontexten stehen.
- `CaiLama-Master` bleibt ein Orchestrierungs- und Dokumentationsrepo.
- Unter-Repos bleiben eigenständige Git-Repositories und werden nicht als
  Submodules geführt.
- Secrets, Tokens, lokale `.env`-Dateien und produktive Zugangsdaten bleiben
  außerhalb der Repositories.

## Behobene Inkonsistenzen

- `CaiLama-LLM-Router`: README, Rollout-Doku, systemd-Doku und
  systemd-Beispiele verwenden den aktuellen Repository-Namen
  `TotoBa/CaiLama-LLM-Router` und den Checkout-Ordner
  `CaiLama-LLM-Router`.
- `CaiLama-LLM-Router`: Der alte Initialplan wurde nach
  `docs/archive/llm-router.initial.plan.md` verschoben und als historisch
  markiert.
- `CaiLama-Search`: README, `pyproject.toml` und `sources.md` nennen das
  aktuelle System als CaiLama und verwenden den alten Produktnamen nicht mehr
  als aktive Bezeichnung.
- `CaiLama-Search`: Der alte Initialplan wurde nach
  `docs/archive/CaiLama-Search.init.plan.md` verschoben und als historisch
  markiert.
- `CaiLama-Master`: Die Website-Favicon-Änderung ist in allen HTML-Seiten
  referenziert und `web/favicon.ico` liegt im Master-Repo.
- `CaiLama-Master`: Der öffentliche Hinweis zu `hinweise.md` in `AGENTS.md`
  ist sprachlich bereinigt.

## Verifikation

- Aktive Router-Dokumente enthalten keine alten Clone-/Checkout-Anweisungen
  auf `TotoBa/LLM-Router` oder `LLM-Router/`.
- Aktive Search-Dokumente enthalten keine aktive Produktbezeichnung
  `DGT-Chesstrainer` mehr.
- Historische Altbezeichnungen stehen nur noch in `docs/archive/` oder in
  ausdrücklich historischen Referenzen.
- Der Master-Check bleibt: `git check-ignore -v CaiLama CaiLama-LLM-Router
  CaiLama-Search` und `bash scripts/check-ecosystem.sh`.
