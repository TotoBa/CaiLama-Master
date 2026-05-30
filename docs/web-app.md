# Web-App Betrieb

Die login-geschützte interne CaiLama-Web-App liegt unter `/app.php`.

## Architektur

```text
Browser (cailama.org/app.php)
  -> app-api.php (Session + Profil)
  -> cailama-web ASGI (Runtime)
  -> AgentController / VirtualBoard / Jobs
```

PHP bleibt dünn: Login, CSRF, Profil-Bridge, Proxy. Schachlogik nur in `TotoBa/CaiLama`.

## Konfiguration (privat)

In der privaten Webspace-Konfiguration (`web_api`):

- `base_url`: HTTPS-Basis-URL des `cailama-web`-Dienstes
- `session_token`: Bearer-Token für serverseitige Proxy-Aufrufe
- `timeout_seconds`: Request-Timeout

Website-User werden über `web_users.player_profile_id` mit
`cailama_player_profiles` verknüpft (z. B. `testuser` → `totomanie`).

## Deploy

1. Master-Website deployen (`~/bin/cailama-deploy-website`)
2. Schema anwenden (`POST /api/v1/admin/schema/cailama`)
3. Runtime deployen; der Stack baut `cailama-web` aus `CaiLama/Dockerfile.web`
   und startet den ASGI-Origin im internen Docker-Netz.
4. Private `web_api`-Werte setzen (gleiche Origin-Basis oder dedizierter Web-Port)

`cailama-web` ist kein manuell gestarteter Orphan-Container. Der Dienst gehoert
zur Runtime-Compose, laeuft ohne Root-Rechte, mit Read-only-RootFS,
expliziten Schreibpfaden fuer Home, Jobs und Artefakte sowie lokaler
Stockfish-Anbindung fuer Stellungsanalysen. Secrets bleiben in privater
Operator-Konfiguration, nicht im Repository.

## Smoke-Test

1. Mit `testuser` einloggen
2. `/app.php` öffnen
3. Neue Session anlegen, Nachricht senden
4. PGN-Analyse starten

## UI-Stand

- Die App rendert in einem eigenen Layout ohne öffentliche Website-Navigation.
- Das Eingabefeld sendet mit `Enter`; `Shift+Enter` fuegt einen Zeilenumbruch ein.
- Die App laedt jQuery UI fuer Slash-Command-Hinweise, aber kein jQuery Mobile.
- Das erste leere Chatfenster zeigt einen neutralen Startzustand; echte Antworten und Statusmeldungen kommen aus der Web-API.
- Modell- und Tool-Statusmeldungen werden direkt im Chatverlauf angezeigt,
  an derselben Stelle wie die spaetere Antwort. Der Debug-Modus ergaenzt nur
  die Rohmetadaten, versteckt aber keine normalen Aktivitaetsmeldungen.
- Das Browserbrett ist als Eingabegeraet verdrahtet: Quadrat anklicken,
  Zielfeld anklicken, danach validiert die CaiLama-Web-API den Zug und liefert
  aktualisierte FEN/SVG-Daten. Undo, Reset und Drehen laufen ebenfalls ueber
  Board-Endpunkte.
- Die Kopfzeile laedt die Modellliste aus der CaiLama-Web-API (`GET /models`).
  Nutzer koennen eine CaiLama-Rolle und einen Router-Modellalias waehlen; die
  Auswahl wird pro Nachricht oder Slash-Command an die Session uebergeben.

Siehe auch [`web-app.plan.md`](web-app.plan.md) und [`integrations.md`](integrations.md).
