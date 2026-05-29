# Aktuelle Modellrollen-Matrix

Stand: 2026-05-23.

Diese Matrix ist eine messbare Arbeitshypothese fuer CaiLama-Modellrollen.
Sie ersetzt keine Benchmark-Ergebnisse. Jede Zeile muss ueber wiederholbare
Aufgaben, Router-/Client-Metriken und menschliches Feedback validiert werden.
Aktuelle gemessene Rollenwerte stehen in
`docs/benchmark-results/model-role-results.current.md`.

## Grundannahmen

- `kimi-k2.6:cloud` bleibt der lokale Default fuer Kimi-/Coding-Agentenarbeit.
- Gemma4 ist fuer Coding-Agentenarbeit nicht geeignet, bleibt aber fuer
  Schachrollen ein Kandidat.
- Deepseek-Pro-Varianten sind nicht Routine-Default; sie muessen ihren Nutzen
  gegen Kosten, Regelbefolgung und Korrekturaufwand belegen.
- Schachrollen werden getrennt von Coding-Agenten bewertet.
- Die 21 PTG-Positionen aus der Drei-Beispiel-Baseline sind nur ein
  beobachtetes Ergebnis dieses Datensatzes, keine globale Obergrenze.

## Rollen

| Bereich | Rolle | Kandidaten | Messfrage |
| --- | --- | --- | --- |
| Coding | Kimi-/Repo-Agent | `kimi-k2.6:cloud`, Vergleich gegen andere Coding-Modelle | Befolgt das Modell AGENTS/TODO, liefert saubere Patches, Tests, Doku und sauberen Arbeitsbaum? |
| Router | Alias-/Fallback-Smoke | reale Router-Aliase | Bleiben Latenz, Usage-Metriken, Streaming-Fehler und Fallbacks stabil? |
| PTG | `chess-small` | Gemma4, schnelle Flash-/Small-Modelle | Klassifiziert das Modell Zuege stabil, gueltig und weiterverarbeitbar? |
| Training | `chess-coach` | Gemma4, Kimi, weitere Kandidaten | Ist die Antwort didaktisch hilfreich, spoilerarm und passend zur Spielstaerke? |
| Analyse | `chess-analyst` | grosse Reasoning-/Allround-Modelle | Trennt das Modell Engine-/Brettfakten von Vermutung und vermeidet erfundene Varianten? |
| Kritik | `chess-critic` | Kimi, Qwen, weitere Kandidaten | Findet das Modell Logik-, PGN-, FEN- und Quellenfehler ohne neue Halluzinationen? |
| OCR/FEN | `chess-vision` | Gemma4, Vision-faehige Modelle | Gibt das Modell nur belastbare FENs aus und markiert Unsicherheit korrekt? |
| RAG | `chess-researcher` | Kimi, schnelle Search-taugliche Modelle | Nutzt das Modell Quellenprovenienz, Freshness und Unsicherheit nachvollziehbar? |

## Metriken

Pflichtfelder je Feedback:

- Modellrolle und konkretes Modell.
- Dauer in Millisekunden.
- Input-, Thinking- und Output-Tokens, soweit verfuegbar.
- Qualitaet 1 bis 5.
- Aufgabenloesung 1 bis 5.
- Logikfehler: keine, klein, schwer oder unklar.
- A/B-Praeferenz.
- Freitext fuer Fehlerbild, Nutzen und naechste Verbesserung.

Weitere sinnvolle Metriken je Benchmark-Familie:

- Coding: Korrekturrate, Regelverletzungen, fehlende Tests, Doku-Drift,
  uncommittete oder fremde Arbeitsbaum-Aenderungen.
- PTG: PGN-/JSON-Validitaet, Anteil legaler Zuege, Schluesselstellungs-
  Qualitaet, Redundanz, Review-Erfolg.
- RAG: Recall, MRR, Zero-Hit-Rate, Quellen-Diversitaet, Quellenbezug in der
  Antwort.
- OCR/FEN: Diagramm-Erkennung, false-positive FENs, Unsicherheitsmarkierung.

## Website-Feedback

Die geschuetzte Seite `benchmark-feedback.php` sammelt diese Bewertungen in
der Provider-Datenbank. Sie nutzt `cailama_model_benchmark_cases` fuer
Fallbeschreibungen und `cailama_model_feedback` fuer einzelne Feedbacks.

Nicht speichern:

- Secrets oder lokale Pfade.
- volle private Partien.
- ungekuerzte Prompt-/Response-Logs.
- produktive Zugangsdaten.

## Naechste Schritte

- CaiLama soll Benchmark-Events aus PTG, Coach-Session und Planmodus so
  exportieren, dass Dauer und Tokenwerte auf der Website eingetragen oder
  spaeter importiert werden koennen.
- Router soll seine privacy-safe Usage- und Latenzexporte weiter fuer diese
  Matrix nutzbar halten.
- Search soll RAG-Provenienz und DWZ-/Hybrid-Eval-Ergebnisse als eigene
  Benchmark-Zeilen liefern.
