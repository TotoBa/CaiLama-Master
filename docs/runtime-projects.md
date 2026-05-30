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

**Konfiguration:** Echte Betriebs-, Server- und Operator-Konfigurationen gehoeren
nicht ins Repository. Sie sind gitignored und nur lokal verfuegbar. Versionierte
Dateien im Repo sind Muster, Beispiele oder secretfreie Defaults – keine
produktiven Pfade, Hostnamen oder Zugangsdaten.

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
Es ermittelt den Master-Root aus dem eigenen Skriptpfad. Dadurch kann es auch
aus einem Unter-Repository heraus mit absolutem Pfad gestartet werden, ohne
versehentlich `CaiLama/CaiLama` als Quellpfad zu bauen.

Mit `--install` werden die Runtime-Umgebungen inklusive Test-/Dev-Extras
installiert:

- CaiLama: `.[test]`
- Router: `.[dev]`
- Search: `.[api,dev]`

Damit sind nach einem Runtime-Deploy gezielte Pytest-Smokes ohne manuelle
Nachinstallation möglich.

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

Stand 2026-05-24: `scripts/update-runtime-projects.sh --install --restart all`
hat CaiLama, Router und Search aus den lokalen Source-Repos in die
gitfreien Runtime-Kopien synchronisiert, die Runtime-Abhängigkeiten neu
installiert und Router/Search über die vorhandene Dienstlogik neu gestartet.
Lokale Health-Checks antworteten mit Router `/health` und Search `/healthz`;
beide User-Services waren aktiv.

Stand 2026-05-28: Der Search-Live-Dienst wurde gezielt fuer den aktuellen
SVW/DSB-DWZ-v2-Import genutzt, ohne Router oder laufende Benchmarks zu
unterbrechen. Der Import schrieb `dwz_players` neu und verifizierte
Namens-/Vereinssuchen. Solche Live-Importe bleiben bewusste
Operator-Aktionen und werden nicht durch Standard-Smokes ausgeloest.

## Secretfreie Runtime-Smokes

Nach einem Deploy sind nur secretfreie Checks Standard:

```bash
scripts/update-runtime-projects.sh --dry-run all
scripts/update-runtime-projects.sh --install --restart all
```

Geeignete Smokes sind Help-/Version-Befehle, offline Pytest-Teilsets und
FakeLLM-Console-Befehle. Live-LLMs, echte DBs, DGT-Hardware,
Webspace-DB-API, Crawls, DWZ-Importe und produktive Search-/Router-Lastläufe
bleiben bewusste Operator-Aktionen und dürfen nicht durch Agenten aus
Neugier gestartet werden.

## Container-Haertung

Produktive Runtime-Compose-Konfigurationen bleiben lokale Operator-Dateien und
werden nicht mit echten Secrets versioniert. Fuer den Docker-Betrieb gilt als
Baseline:

- Nur der Reverse Proxy bindet oeffentliche Ports; Router, Search, SearXNG,
  Meilisearch, `cailama-web`, Datenbank und Modell-Backends bleiben im
  internen Docker-Netz.
- Der interne Origin-Hop fuer Webspace-Konsole und Konsolenjobs wird aus der
  versionierten Master-Komponente `CaiLama-Origin-API/` gebaut. Die Runtime-
  Kopie unter dem Compose-Build-Kontext ist generiert und keine Quelle fuer
  manuelle Entwicklung.
- Dienste laufen nicht als Root, wenn das Image dies sauber unterstuetzt.
- `read_only: true` ist fuer zustandslose Dienste und Dienste mit expliziten
  Datenvolumes zu setzen. Schreibpfade werden als benannte Volumes, Bind-Mounts
  oder tmpfs ausdruecklich modelliert.
- `security_opt: ["no-new-privileges:true"]` und `cap_drop: ["ALL"]` sind der
  Standard. Zusaetzliche Capabilities muessen pro Dienst begruendet sein; der
  Reverse Proxy braucht nur die Bind-Capability fuer niedrige Ports.
- TLS-Terminierung erfolgt ueber den Reverse Proxy mit automatischer
  Zertifikatsverwaltung. Zertifikatsdaten liegen in einem persistenten
  Proxy-Volume, nicht im Git-Checkout.
- Persistente Datenvolumes muessen zur Container-UID passen. Besonders
  betroffen sind Suchindex-, Modell-Cache-, Job-, Web-Artefakt- und
  Zertifikatsvolumes.
- Der Origin-/API-Gateway-Audit-Logger schreibt JSONL auf stderr und in
  gehaerteter Runtime zusaetzlich in einen expliziten Schreibpfad wie
  `/var/log/cailama/audit.log`. Bei `read_only: true` muss dieser Pfad als
  eigenes Volume modelliert und fuer die Container-UID beschreibbar sein.
- Web-Origin-Dienste muessen compose-gefuert sein. Manuell gestartete
  Orphan-Container sind nach der Uebernahme in Compose mit
  `--remove-orphans` zu entfernen, damit Haertungsregeln und Deploys
  tatsaechlich auf alle laufenden Dienste greifen.
- Startfehler nach Haertung sind zuerst gegen drei Punkte zu pruefen:
  UID/GID des Volumes, notwendige Schreibpfade bei Read-only-RootFS und
  Lesbarkeit lokal gemounteter Runtime-Konfiguration.

Die Mindestpruefung nach Aenderungen ist:

```bash
docker compose config --quiet
docker compose up -d
docker compose ps
```

Danach muessen die Runtime-Smokes laufen. Wenn ein Dienst wegen
Read-only-RootFS ausfaellt, wird nicht pauschal auf beschreibbare RootFS
zurueckgestellt; stattdessen wird der konkrete Schreibpfad als tmpfs oder
dediziertes Volume modelliert.
