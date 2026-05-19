# Webseite

Die oeffentliche CaiLama-Webseite ist fuer folgende URL vorgesehen:

```text
https://cailama.org/
```

## Quelle

Die versionierte Quelle liegt im Master-Repo:

```text
web/index.html
```

Die Seite ist bewusst eine einzelne statische HTML-Datei mit eingebettetem CSS.
Sie nutzt das vorhandene CaiLama-Logo direkt aus dem Haupt-Repository:

```text
https://raw.githubusercontent.com/TotoBa/CaiLama/main/img/logo-big.png
```

Dadurch wird die Logo-Datei nicht im Master-Repo dupliziert.

## Lokaler Webspace

Der aktuell vorgesehene lokale Webspace ist:

```text
/srv/cailama-web/public
```

Deployment der aktuellen HTML-Datei:

```bash
install -D -m 0644 web/index.html /srv/cailama-web/public/index.html
```

Die Webserver-, DNS- und TLS-Konfiguration fuer `cailama.org` liegt ausserhalb
dieses Repositories. Keine Zertifikate, Tokens oder Server-Secrets in dieses
Repo schreiben.
