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
3. Runtime: `cailama-web` aus CaiLama-Source starten
4. Private `web_api`-Werte setzen (gleiche Origin-Basis oder dedizierter Web-Port)

## Smoke-Test

1. Mit `testuser` einloggen
2. `/app.php` öffnen
3. Neue Session anlegen, Nachricht senden
4. PGN-Analyse starten

Siehe auch [`web-app.plan.md`](web-app.plan.md) und [`integrations.md`](integrations.md).
