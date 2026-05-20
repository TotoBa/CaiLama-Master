# Integrationen

Dieses Dokument sammelt die Master-Sicht auf Cross-Repo-Schnittstellen. Es
beschreibt keine Implementierung in den Unter-Repositories.

## Schnittstellen

### CaiLama -> CaiLama-LLM-Router

Ziel: CaiLama nutzt den Router fuer LLM-Zugriff ueber OpenAI-kompatible
Endpunkte.

Relevante Modellrollen:

- `router`
- `small`
- `large`
- `task`
- `coach`
- `analyst`
- `critic`
- `vision`
- `scribe`
- `researcher`

Zu klaerende bzw. laufend zu pruefende Punkte:

- Streaming-Fehlerbehandlung fuer `stream: true`-Flows nachvollziehbar machen.
- Optionales Config-Hot-Reload mit Tests klaeren.
- Backend-spezifisches Modell-Mapping per Alias absichern.
- `/health`, `/v1/models`, `/v1/chat/completions` und `/metrics` als Smoke-
  und Observability-Pfade dokumentieren.
- Keine Provider-Secrets im Master speichern.

### CaiLama -> CaiLama-Search

Ziel: CaiLama nutzt CaiLama-Search fuer kontrollierte Suche, RAG-Kontext und
DWZ-Daten.

Relevante Endpunkte:

- `POST /v1/search` als kanonischer Suchvertrag; `GET /v1/search?q=...` bleibt
  fuer einfache Clients kompatibel.
- `POST /v1/context`
- `/v1/dwz/search`
- `/v1/dwz/player/{pkz}`

Zu klaerende bzw. laufend zu pruefende Punkte:

- SearchAdapter in CaiLama als Standardpfad vor browserbasierter Websuche.
- Normalisierte `items`/`results` aus `/v1/search` und `context`/`sources` aus
  `/v1/context` synchron halten.
- Browserbasierter Webpfad nur als expliziter Fallback.
- Quellenprovenienz bei RAG-Antworten sichtbar halten.
- DWZ-Identity-Linking mit Ambiguitaetsbehandlung und PII-Minimierung.

### CaiLama -> Webspace-DB-API

Ziel: CaiLama soll kuenftig zwischen nativem MariaDB/MySQL-Zugriff,
fachlicher Webspace-API und Hybridbetrieb waehlen koennen.

Zu klaerende bzw. laufend zu pruefende Punkte:

- Konfiguration in CaiLama fuer `native`, `api` und `hybrid` definieren.
- Lokale DB als Aufbau- und Backup-Pfad erhalten.
- Provider-Datenbank nur ueber fachliche PHP-Fassade anbinden.
- Keine SQL-over-HTTP-API und keine DB-Secrets im Master.

### CaiLama-Master -> Unter-Repos

Der Master koppelt keine Runtime-Komponenten. Er dokumentiert:

- Ziel-Repo,
- betroffene Schnittstelle,
- erwartetes Ergebnis,
- Akzeptanzkriterien,
- Pruefstatus.

## Runtime-Konfiguration ohne Secrets

Der Master darf nur Variablennamen, Rollen, Endpunkte und Betriebsannahmen
dokumentieren. Echte Werte bleiben lokal.

Beispiele fuer erlaubte Angaben:

- Name einer Env-Variable.
- lokaler Standard-Port ohne Zugangsdaten.
- erwarteter Endpunktpfad.
- Beschreibung eines Service-Keys ohne Wert.

Nicht erlaubt:

- echte API-Keys,
- Tokens,
- Passwoerter,
- Zertifikate,
- produktive Host-spezifische Zugangsdaten.

## Smoke-Tests

Master-dokumentierte Smoke-Tests duerfen nur pruefen, was ohne Secrets und ohne
destruktive Aktionen moeglich ist:

- Existenz und Git-Status der Unter-Repos.
- Ignore-Status im Master.
- statische Webseite und Webspace-Datei.
- dokumentierte API-Pfade als Vertrag, nicht als Live-Aufruf mit Credentials.
