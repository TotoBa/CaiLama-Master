# Runtime-Projekte

Dieses Dokument beschreibt die Trennung zwischen Git-Codebases und
Live-Betrieb. Konkrete lokale Pfade gehoeren nicht in die offizielle Doku,
sondern nur in lokale Service-, Shell- oder Env-Konfigurationen.

## Prinzip

- Git-Repos bleiben im lokalen Master-Checkout:
  - `CaiLama/`
  - `CaiLama-LLM-Router/`
  - `CaiLama-Search/`
- Live-Betrieb und testbare Kopien liegen in separaten Runtime-Ordnern
  ausserhalb dieser Git-Codebases.
- Runtime-Ordner duerfen keine `.git`-Verzeichnisse enthalten.
- Lokale `.env`, `configs/*.local.yaml`, `.venv`, Logs, Daten- und
  Meilisearch-Verzeichnisse werden beim Aktualisieren nicht ueberschrieben.

Der Router laeuft aus einer separaten Runtime-Kopie, nicht aus dem Git-Repo.
Das ist Absicht: Der laufende Dienst soll stabil bleiben, auch wenn im Repo
gerade gearbeitet, getestet oder reviewed wird.

Search soll analog aus einer separaten Runtime-Kopie gestartet werden. CaiLama
selbst bekommt ebenfalls eine gitfreie Testkopie, in die bei Bedarf ein
bestimmter Git-Ref exportiert werden kann.

## Aktualisieren

Alle Runtime-Ordner aus den aktuellen lokalen Repos aktualisieren:

```bash
scripts/update-runtime-projects.sh all
```

Nur Router aktualisieren:

```bash
scripts/update-runtime-projects.sh router
```

Nur Search aktualisieren, Abhaengigkeiten installieren und danach aus dem
Runtime-Ordner starten:

```bash
scripts/update-runtime-projects.sh --install --restart search
```

CaiLama auf einen bestimmten Ref fuer Tests exportieren:

```bash
scripts/update-runtime-projects.sh --ref main cailama
```

Das Script verweigert Runtime-Ziele, die ein `.git`-Verzeichnis enthalten.

## Dienste

Router wird ueber den User-Service `llm-router.service` betrieben. Die
Service-Datei zeigt auf die lokale Runtime-Kopie und verwendet dort die lokale
Runtime-Konfiguration.

Search wird ohne Git-Repo aus seiner Runtime-Kopie gestartet. Der Startbefehl
liegt im Script und nutzt standardmaessig den Search-API-Entry-Point:

```text
uvicorn cailama.search_backend.api:app --host 127.0.0.1 --port 8080
```

Ports koennen fuer Search ueber `CAILAMA_SEARCH_HOST` und
`CAILAMA_SEARCH_PORT` ueberschrieben werden.

## Status der Runtime-Nutzung

- Router: Wird aus der Runtime-Kopie betrieben (entweder ueber
  `llm-router.service` oder manuell aus dem Runtime-Ordner).
- Search: Wird bei Bedarf aus der Runtime-Kopie gestartet.
- CaiLama: Runtime-Kopie wird fuer isolierte Tests und Entwicklungslaeufe
  verwendet, nicht als dauerhaft laufender Dienst.

Die Runtime-Ordner enthalten keine `.git`-Verzeichnisse; das wird beim
Synchronisieren durch `update-runtime-projects.sh` erzwungen.
