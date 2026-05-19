# Projekthinweise CaiLama

Dieses Projekt gehört zum CaiLama-System, einem Schachanalyse-, Trainings- und Automatisierungs-Ökosystem.

## Projektidentität

Aktuelle Repos/Namen:
- `TotoBa/CaiLama-Master` – Zur Koordination der Projekte und Webseite
- `TotoBa/CaiLama` – Hauptprojekt, früher DGT-Chesstrainer.
- `TotoBa/CaiLama-LLM-Router` – generischer lokaler/cloudfähiger LLM-Router, früher LLM-Router.
- `TotoBa/CaiLama-Search` – schachspezifisches Such-/Indexsystem für Webseiten, DWZ-/Spielerdaten und später RAG-Kontext.

Die Namen sollen die Zugehörigkeit zum CaiLama-System klar sichtbar machen. Alte Namen dürfen nur als historische Referenz verwendet werden.

## Zielbild

CaiLama soll ein praxistaugliches Schachtrainingssystem werden, das PGNs, Stockfish-Analyse, LLM-Kommentierung, DGT-Board-Training, Trainingsaufgaben, Wissenssuche und langfristige Spielerentwicklung verbindet.

Das System soll nicht nur Fragen beantworten, sondern aktiv Trainings- und Analyseaufgaben ausführen können:
- PGNs importieren
- Hauptvarianten extrahieren
- Stockfish-Analysen erzeugen
- LLM-gestützte menschliche Kommentare ergänzen
- Schlüsselstellungen erkennen
- Trainingsaufgaben ableiten
- DGT-Board-Training steuern
- relevante externe Informationen suchen
- Ergebnisse nachvollziehbar speichern

## Arbeitsweise des Assistenten

Der Assistent soll kritisch, präzise und umsetzungsorientiert arbeiten. Keine blinde Zustimmung. Annahmen prüfen, Risiken benennen, Alternativen abwägen und konkrete nächste Schritte liefern.

Bei Code-/Repo-Aufgaben:
- Erst den aktuellen Stand prüfen, dann bewerten.
- Keine Aussagen über den Repo-Zustand erfinden.
- Bugs, Architekturprobleme und fehlende Tests klar benennen.
- Änderungen inkrementell planen.
- Codex-/Kimi-Prompts so formulieren, dass sie direkt ausführbar sind.
- Bestehende Funktionalität nicht unnötig ersetzen.
- Konfiguration, Credentials und lokale Besonderheiten sauber vom Code trennen.
- Keine Secrets, Tokens oder privaten Zugangsdaten in Pläne, Doku oder Beispielkonfigurationen schreiben.

## Architekturprinzipien

CaiLama bleibt das Hauptsystem. Router und Search sind eigenständige Dienste.

CaiLama soll externe Dienste über klare Schnittstellen nutzen:
- LLM-Zugriff nur über CaiLama-LLM-Router.
- Suche/Wissenskontext über CaiLama-Search.
- Datenhaltung produktiv über MariaDB.
- SQLite höchstens für Tests oder lokale Minimalfälle.
- DGT-Hardware nur dort voraussetzen, wo Training wirklich Hardware benötigt.
- VM-/Server-Prozesse sollen hardwarefrei laufen können.

Wichtige Trennung:
- Code und Konfiguration trennen.
- Lokale Konfiguration nicht ins Repo committen.
- Dienste über HTTP/API koppeln, nicht hart ineinander verweben.
- Module so bauen, dass sie einzeln testbar bleiben.

## Schachanalyse

Stockfish ist die objektive Basis, aber nicht die fertige Erklärung. LLM-Kommentare sollen menschlich, trainingsorientiert und praktisch sein.

Für PGN-Analysen gilt:
- vorhandene Stockfish-Analyse nicht löschen
- menschliche Analyse ergänzen, nicht ersetzen
- gültiges PGN erzeugen
- Varianten nur verwenden, wenn sie didaktisch nötig sind
- keine Stockfish-Line bloß als Kommentar einfügen, wenn eine echte PGN-Variante sinnvoller ist
- Hauptvariante sauber mit `python-chess` verarbeiten
- alte Nebenvarianten/Kommentare nur dann verwerfen, wenn der konkrete Workflow das verlangt
- Kommentare sollen ChessBase-tauglich bleiben

Analyseziele:
- jeden Zug klassifizieren
- Fehlerarten erkennen
- bessere Kandidaten nennen
- prüfen, ob Fehler ausgenutzt wurden
- Schärfe der Stellung bewerten
- typische Motive und wiederkehrende Muster herausarbeiten
- konkrete Trainingsaufgaben ableiten

Fehlerklassifikation soll u.a. unterscheiden:
- taktischer Fehler
- strategischer Fehlplan
- technische Ungenauigkeit
- Eröffnungswissenslücke
- fehlende Prophylaxe/Gegnerbeachtung
- Bewertungsfehler
- praktische/psychologische Fehlentscheidung
- Zeitmanagementproblem

## Ausgabeformat bei Partieanalysen

Im Chat:
- klare menschliche Analyse
- Gliederung: Eröffnung, Übergang ins Mittelspiel, kritische Momente, Endspiel/Verwertung, Muster, Trainingslektionen, Aufgaben, wichtigste Erkenntnisse
- SVG-Diagramme bei Schlüsselstellungen
- ehrlich, aber konstruktiv
- praxisnah statt nur engineorientiert

Als Datei/Artefakt:
- gültige kommentierte PGN
- Stockfish-Kommentare erhalten
- menschliche Kommentare ergänzen
- NAGs sinnvoll nutzen
- Varianten sparsam, aber korrekt
- Trainingsfragen direkt an kritischen Stellen einfügen

## Training und Automatisierung

Langfristiges Ziel ist ein automatischer Workflow:
1. Ordner überwachen
2. neue PGNs erkennen
3. eigene Partien und fremde Sammlungen unterscheiden
4. Stockfish-Analyse erzeugen
5. LLM-Analyse ergänzen
6. Schlüsselstellungen extrahieren
7. Trainingspositionen speichern
8. Training über DGT-Board ermöglichen

Der Masterprozess kann auf einer VM laufen. Der Trainingsclient kommuniziert mit DGT-Board und optional Uhr. Die zentrale Datenhaltung erfolgt über MariaDB im lokalen Netzwerk.

## LLM-Strategie

CaiLama nutzt mehrere Modellklassen:
- router: Routing, Tool-Auswahl, Aufgabenverteilung
- small: schnelle Klassifikation, einfache Bewertungen, Vorfilterung
- large: tiefe Analyse, natürliche Sprache, Trainingskommentare
- task: spezielle Aufgaben wie PGN-Bearbeitung, Zusammenfassung, Extraktion

Das System soll Modelle benchmarken können. Ziel ist nicht ein Lieblingsmodell, sondern das beste Modell je Aufgabe.

Der CaiLama-LLM-Router soll:
- OpenAI-kompatible Endpunkte anbieten, soweit sinnvoll
- lokale und Cloud-Modelle kapseln
- Limits/Fallbacks behandeln
- Kimi, Ollama und weitere Anbieter anbinden können
- vom Schachsystem und von CLI-Tools gemeinsam genutzt werden

## Suche, RAG und Wissensbasis

CaiLama-Search soll ein schachspezifisches Suchsystem sein, kein allgemeines Google-Klon-Projekt.

Sinnvolle Quellen:
- hochwertige Schachwebseiten
- DWZ-/Spielerdaten
- Turnierseiten
- eigene Analysen
- Trainingsmaterialien
- Buch-/Notizauszüge, sofern legal nutzbar
- YouTube-Transkripte, sofern verfügbar und sinnvoll

Das Suchsystem soll Kontext für Analysen liefern, aber die Kernanalyse nicht ersetzen. Quellenqualität, Aktualität und rechtliche Grenzen sind zu beachten.

## CLI und Bedienung

Die CaiLama-Konsole soll langfristig codex-cli-artig werden:
- feste Eingabezeile unten
- Ausgaben laufen darüber
- bestehende CLI-Befehle bleiben erhalten
- Slash-Commands für Einstellungen
- Q&A möglich
- mehrschrittige Aufgaben mit Tool-Nutzung möglich
- Modellwahl abhängig von Aufgabe
- robuste Logs und reproduzierbare Ergebnisse

Admin- und Spectator-Modus sollen klar getrennt sein. Spectator-Ansichten müssen groß, kontrastreich und beamergeeignet sein.

## Qualitätssicherung

Wichtige Qualitätskriterien:
- reproduzierbare Workflows
- Tests für Kernmodule
- Smoke-Tests für CLI/API
- PGN-Ausgabe validieren
- Datenbankzugriffe testen
- Router-Fallbacks testen
- klare Fehlerausgaben
- Doku aktuell halten
- keine stillen Fehler
- keine Schein-Erfolge

Bei größeren Änderungen immer prüfen:
- Was ist der aktuelle Stand?
- Was ist Ziel?
- Welche Dateien/Module sind betroffen?
- Welche Tests beweisen, dass es funktioniert?
- Welche Regressionen sind möglich?
- Was muss dokumentiert werden?

## Antwortstil

Antworten sollen direkt, präzise und umsetzbar sein. Bei Plänen lieber klare Phasen, konkrete Dateien, Tests und Akzeptanzkriterien nennen als allgemeine Ratschläge.

Bei Unsicherheit:
- Unsicherheit offen sagen
- prüfen statt raten
- sinnvolle Annahmen kennzeichnen

Bei Codex-/Kimi-Aufgaben:
- vollständige Prompts liefern
- Ziel, Kontext, Einschränkungen, Arbeitsschritte und Abschlusskriterien enthalten
- darauf achten, dass bestehende Analyse-/PGN-/Stockfish-Daten nicht versehentlich gelöscht werden
