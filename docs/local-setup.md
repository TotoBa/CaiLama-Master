# Local Setup

Dieses Dokument beschreibt den lokalen Checkout ohne Secrets. Es ersetzt keine
Runtime-Konfiguration der Unter-Repositories.

## Repository-Layout

Empfohlene lokale Struktur:

```text
CaiLama-Master/
├── CaiLama/
├── CaiLama-LLM-Router/
├── CaiLama-Search/
├── docs/
├── scripts/
└── web/
```

Die drei Unterordner sind eigenstaendige Git-Repositories. Sie werden im
Master-Repo ignoriert und duerfen nicht als Submodule eingetragen werden.

## Webspace

Die statische Webseite liegt versioniert unter:

```text
web/
```

Der lokale Webspace fuer die oeffentliche Seite ist:

```text
/srv/cailama-web/public/
```

Deployment:

```bash
scripts/deploy-website.sh
```

Das Skript synchronisiert den kompletten Inhalt von `web/`, also HTML,
Stylesheet, `llms.txt`, `ecosystem-reference.md` und
`data/ecosystem.json`.

Die URL `https://cailama.org/` wurde am 2026-05-19 per `curl -I -L` mit
`HTTP/2 200` verifiziert.

## Konfiguration

- Keine lokalen `.env`-Dateien im Master-Repo versionieren.
- Keine Tokens, API-Keys, Passwoerter, Zertifikate oder private Pfade
  dokumentieren.
- `.env.example` ist nur erlaubt, wenn es keine echten Secrets enthaelt.
- Produktive Runtime-Konfiguration gehoert in die jeweiligen Unter-Repos oder
  in lokale Betriebsumgebungen, nicht in `CaiLama-Master`.

## Lokale Pruefung

```bash
git status --short
git check-ignore -v CaiLama CaiLama-LLM-Router CaiLama-Search prompt.md
bash scripts/check-ecosystem.sh
```
