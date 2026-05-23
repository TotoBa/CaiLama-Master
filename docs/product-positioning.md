# Produktpositionierung

CaiLama ist kein Consumer-Social-Frontend und kein weiteres allgemeines
Schachportal. Das Ziel ist ein lokales, erweiterbares Trainingssystem fuer
ambitionierte Spieler, Trainer und ernsthafte Selbstlerner.

## Kernversprechen

Vom PGN zur persoenlichen Trainingsaufgabe:

1. Partie importieren.
2. Stockfish-, Brettwahrheit-, Legal-Move- und Heuristiksignale erzeugen.
3. menschlich verstaendliche, aber engine-geerdete Kommentare ergaenzen.
4. drei bis sieben Schluesselstellungen extrahieren.
5. Fehlerarten und wiederkehrende Muster klassifizieren.
6. gewichtete Trainingspositionen, Fragen und Karten erzeugen.
7. gueltige PGN-Artefakte und Trainings-JSON speichern.
8. konkrete Coach-Session erst bei Bedarf per CLI/Agent erzeugen: passende
   Position, Trainingsfrage, Unicode-Brett und bei angeschlossenem DGT-Brett
   Aufstellaufforderung.
9. Review-Ergebnisse in Prioritaet, Schwierigkeit und Wiederholung
   zurueckfuehren.

Stand 2026-05-23: Die offline/deterministische PTG-Scheibe erzeugt bereits
`source.pgn`, `annotated.pgn`, `training.json` und `quality_gates.json`.
Agent-/DGT-naher Kartenabruf, `card_id`-Durchstich, CardType/Muster und
Review-Stats in der Trainingspriorisierung sind umgesetzt. Gewichtete
Trainingspositionen, kurzlebige Coach-Sessions on demand, Review-Gate-
Console, Planmodus, Hintergrund-Agenten, Benchmark-Events, PGN-/LLM-
Resilienz und Legal-Move-/Brettwahrheit-Tags sind vorhanden. Offen bleiben
PTG-Live-Verifikation, Legal-Move-Details in Folgeartefakten, OCR/FEN-Gates,
RAG-Provenienz in allen Antwortformaten, breitere Qualitaetsgates und
Datenschutz-/Export-Regeln fuer Leistungsprofile.

## Differenzierung

- CaiLama organisiert Trainingsarbeit, statt nur eine Partie zu erklaeren.
- CaiLama bleibt modular: Hauptsystem, LLM-Router, Search, DB-API und
  Runtime-Kopien sind getrennt.
- CaiLama kann lokal bzw. self-hosted betrieben werden und muss keine privaten
  Trainingsdaten an eine Plattform binden.
- DGT-nahe Trainingspfade sind ein bewusstes Alleinstellungsmerkmal.
- Search und RAG liefern kontrollierte Quellen, ersetzen aber nicht die
  Kernanalyse.
- Benchmarks und reproduzierbare Artefakte sollen Qualitaet sichtbar machen,
  statt Modellantworten nur subjektiv zu bewerten.
- Der Coach erzeugt Trainingseinheiten situativ aus gewichteten Positionen
  statt dauerhaft offene Sessions auf Vorrat anzulegen.

## Zielgruppe

Primaere Zielgruppe sind Vereinsspieler, Trainer und ambitionierte
Selbstlerner, die langfristig besser trainieren wollen:

- eigene Partien systematisch auswerten,
- Fehler wiedererkennen,
- Schluesselstellungen wiederholen,
- eine Stellung am Brett aufbauen und mit Unicode-Brett gegenpruefen,
- Trainingsfortschritt speichern,
- Quellen und Analyseentscheidungen nachvollziehen.

Breite Social-, Feed-, Matchmaking- oder Mobile-First-Funktionen sind bewusst
kein aktueller Schwerpunkt.

## Qualitaetsgrenzen

CaiLama darf keine ungeprueften LLM-Behauptungen als Schachwahrheit ausgeben.
Alle produktnahen Analyse- und Trainingspfade brauchen:

- legale Zuege und Brettzustandspruefung,
- Legal-Move-Tags und Stockfish-Qualitaetsbaender dort, wo alle Kandidaten
  einer Stellung verglichen werden,
- PGN-Roundtrip oder explizite Fehler,
- annotierter PGN-Roundtrip fuer produktnahe Kommentare,
- Stockfish- oder Heuristik-Grounding fuer Bewertungen,
- klare Kennzeichnung, wenn kein Engineurteil vorliegt,
- Quellenprovenienz bei RAG-Kontext,
- keine Prompt-/Response-Rohdaten in globalen Metriken,
- keine geratenen FENs aus OCR-Diagrammen.

## Hebel

### Benchmarks

Benchmarks gehoeren in die Master-Koordination. Sie vergleichen nicht nur
Modelle, sondern komplette Workflows:

- Search: Recall, MRR, Zero-Hit-Rate, Latenz und Quellenqualitaet.
- Router: Latenz, Fallbacks, Fehlerverhalten, Usage-Metriken und Benchmark-
  Export.
- Analyse/PTG: PGN-Validitaet, Kartenqualitaet, Redundanz,
  Fehlerklassifikation, Zahl der tief analysierten Schluesselstellungen,
  LLM-Call-Aufwand und Review-Erfolg.
- OCR/FEN: Diagramm-Erkennung, FEN-Sicherheit und Fehlerquote.

### Spezialisiertes LLM-Training

Spezialisiertes LLM-Training ist ein spaeterer Hebel, nicht die erste
Produktbasis. Vorher muessen Daten- und Qualitaetsregeln stehen:

- nur freigegebene, synthetische oder anonymisierte Trainingsdaten,
- klare Trennung zwischen Test-, Eval- und Trainingsdaten,
- keine privaten Partien oder Kommentare ohne explizite Freigabe,
- Benchmarks vor und nach Modellanpassungen,
- Router-Kompatibilitaet als Bereitstellungsgrenze.

Der erste Produktwert entsteht durch robuste Pipeline, Validierung,
Wiederholung und Feedback. Spezialisierte Modelle koennen diese Pipeline
spaeter beschleunigen oder verbessern, duerfen sie aber nicht ersetzen.
