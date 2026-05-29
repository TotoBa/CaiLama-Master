# Aktuelle Modellrollen-Ergebnisse

Stand: 2026-05-29 (Router/Task-Semantik-Lauf ausgewertet).

Neuester semantischer Teillauf (nur `router` + `task`):
`docs/benchmark-results/2026-05-29.router-task-semantic.md`
(Run `ptg-three-games-20260529T145606Z`, 64 Modelle, Struktur-Pass Router 55,5 % /
Task 34,7 %). Vollständiger 11-Rollen-Stand unten stammt noch aus
`ptg-three-games-20260526T092135Z`.

---

Stand (11-Rollen-Lauf): 2026-05-29T10:11:02Z.
Quelle: Website-Feedback-Summary fuer Run `ptg-three-games-20260526T092135Z`.
Testlauf erzeugt: 2026-05-28T21:49:20+00:00.
Umfang: 63 Modelle, 11 Rollen, 110 Rollenaufgaben, PTG-Teil uebersprungen.

Die folgenden Tabellen sind nicht kumuliert: Jedes getestete Modell
hat eine eigene Tabelle. Scores stammen aus dem geschuetzten
Website-Feedback und bleiben secretfrei.

Wichtige Einordnung: Der abgeschlossene Lauf nutzte vor den aktuellen
Fixes noch eine zu strenge Task-/Tool-Strukturpruefung. Besonders
`chess-task` und Teile von `chess-router` werden deshalb im naechsten
Re-Test neu bewertet.

## Naechster Re-Test

Ziel: nur die 10 ausgewaehlten bereits starken Kandidaten plus ein
Mistral-API-Modell testen.

| Typ | Modell | Grund |
| --- | --- | --- |
| Auswahl | `gpt-oss:20b-cloud:think-medium` | bester Analyst-Kandidat im abgeschlossenen Lauf |
| Auswahl | `gemini-3-flash-preview:cloud:think-on` | bester Coach-Kandidat im abgeschlossenen Lauf |
| Auswahl | `qwen3-next:80b-cloud:think-on` | bester Critic-Kandidat im abgeschlossenen Lauf |
| Auswahl | `gemini-3-flash-preview:cloud:think-off` | bester Large-/Scribe-Kandidat im abgeschlossenen Lauf |
| Auswahl | `qwen3-coder:480b-cloud` | bester Researcher-Kandidat und Task-faehiger Coder-Kandidat |
| Auswahl | `gpt-oss:120b-cloud:think-medium` | bester Small-/Klassifikationskandidat im abgeschlossenen Lauf |
| Auswahl | `devstral-small-2:24b-cloud` | starker kompakter Translator-/Task-Kandidat |
| Auswahl | `minimax-m2.7:cloud:think-off` | bester Vision/OCR-FEN-Kandidat im abgeschlossenen Lauf |
| Auswahl | `ministral-3:3b-cloud` | guenstiger Translator-/Small-Kandidat aus dem aktuellen Lauf |
| Auswahl | `kimi-k2.6:cloud:think-on` | aktueller Kimi-Cloud-Default und wichtiger Router-/Agent-Vergleich |
| Zusatz | `mistral-small-latest` | Mistral-API-Free-/Experiment-Plan-Smoke; direkter Provider, nicht Ollama-Cloud |

## Ergebnisse Pro Modell

### `deepseek-v4-flash:cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 34585 | 2498 | 691.698 |
| `chess-small` | -2.05 | 10 | 1 | 1 | 4 | 10 | 0 | 0 | 44658 | 6670 | 893.152 |
| `chess-large` | 0.252 | 10 | 2.1 | 2.1 | 3.11 | 4 | 4 | 1 | 98133 | 8476 | 1962.652 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 23846 | 10704 | 476.92 |
| `chess-translator` | 3.04 | 10 | 2.9 | 2.9 | 4.5 | 0 | 1 | 0 | 17484 | 449 | 349.682 |
| `chess-coach` | 3.66 | 10 | 3.6 | 3.6 | 4 | 0 | 0 | 0 | 33785 | 5790 | 675.692 |
| `chess-analyst` | 2.64 | 10 | 3.3 | 3.3 | 3.9 | 1 | 4 | 0 | 42860 | 6870 | 857.19 |
| `chess-critic` | 3.006 | 10 | 3.4 | 3.4 | 4.44 | 1 | 0 | 1 | 19431 | 1693 | 388.616 |
| `chess-vision` | 2.044 | 10 | 2.7 | 2.7 | 4.33 | 2 | 0 | 1 | 21027 | 695 | 420.536 |
| `chess-scribe` | 3.195 | 10 | 3 | 3 | 4.3 | 0 | 0 | 0 | 21398 | 5720 | 427.952 |
| `chess-researcher` | 0.371 | 10 | 2.2 | 2.2 | 4.67 | 4 | 0 | 4 | 14856 | 781 | 297.118 |

### `deepseek-v4-flash:cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 28011 | 2643 | 560.218 |
| `chess-small` | -2.11 | 10 | 1 | 1 | 3.6 | 10 | 0 | 0 | 47048 | 6643 | 940.956 |
| `chess-large` | 1.16 | 10 | 2.6 | 2.6 | 3 | 4 | 1 | 0 | 103164 | 7803 | 2063.284 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 33484 | 10550 | 669.688 |
| `chess-translator` | 3.07 | 10 | 2.9 | 2.9 | 4.7 | 0 | 1 | 0 | 12029 | 426 | 240.57 |
| `chess-coach` | 3.73 | 10 | 3.7 | 3.7 | 3.9 | 0 | 0 | 0 | 48327 | 5977 | 966.534 |
| `chess-analyst` | 2.935 | 10 | 3.5 | 3.5 | 3.4 | 1 | 2 | 0 | 77638 | 6743 | 1552.756 |
| `chess-critic` | 1.864 | 10 | 2.9 | 2.9 | 4.33 | 3 | 0 | 1 | 23641 | 1642 | 472.828 |
| `chess-vision` | 2.547 | 10 | 2.8 | 2.8 | 4.78 | 1 | 0 | 1 | 12026 | 657 | 240.514 |
| `chess-scribe` | 3.255 | 10 | 3 | 3 | 4.7 | 0 | 0 | 0 | 13487 | 5530 | 269.744 |
| `chess-researcher` | -0.33 | 10 | 2 | 2 | 4.8 | 5 | 0 | 5 | 7637 | 827 | 152.746 |

### `deepseek-v4-flash:cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 22710 | 2468 | 454.206 |
| `chess-small` | -2.05 | 10 | 1 | 1 | 4 | 10 | 0 | 0 | 32912 | 6486 | 658.242 |
| `chess-large` | 2.075 | 10 | 3 | 3 | 3.5 | 2 | 3 | 0 | 55762 | 8287 | 1115.23 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 25468 | 10536 | 509.35 |
| `chess-translator` | 3.07 | 10 | 2.9 | 2.9 | 4.7 | 0 | 1 | 0 | 8728 | 428 | 174.568 |
| `chess-coach` | 3.505 | 10 | 3.4 | 3.4 | 4.1 | 0 | 0 | 0 | 83678 | 6309 | 1673.554 |
| `chess-analyst` | 1.842 | 10 | 3.1 | 3.1 | 3.38 | 2 | 2 | 2 | 65321 | 7243 | 1306.418 |
| `chess-critic` | 2.875 | 10 | 3.5 | 3.5 | 3 | 1 | 0 | 1 | 85642 | 1768 | 1712.838 |
| `chess-vision` | 2.061 | 10 | 2.7 | 2.7 | 4.44 | 2 | 0 | 1 | 29953 | 673 | 599.058 |
| `chess-scribe` | 3.15 | 10 | 3 | 3 | 4 | 0 | 0 | 0 | 35633 | 5483 | 712.666 |
| `chess-researcher` | -0.36 | 10 | 2 | 2 | 4.6 | 5 | 0 | 5 | 15899 | 776 | 317.972 |

### `deepseek-v4-flash:cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -2.44 | 10 | 1.6 | 1.6 | 4 | 8 | 0 | 8 | 49078 | 2426 | 981.556 |
| `chess-small` | -2.215 | 10 | 1 | 1 | 2.9 | 10 | 0 | 0 | 92987 | 6687 | 1859.744 |
| `chess-large` | 0.74 | 10 | 2.4 | 2.4 | 2 | 4 | 2 | 0 | 189185 | 8362 | 3783.692 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 44524 | 10521 | 890.486 |
| `chess-translator` | 3.025 | 10 | 2.9 | 2.9 | 4.4 | 0 | 1 | 0 | 22067 | 452 | 441.33 |
| `chess-coach` | 3.455 | 10 | 3.5 | 3.5 | 3.2 | 0 | 0 | 0 | 74860 | 6394 | 1497.194 |
| `chess-analyst` | 2.45 | 10 | 3.2 | 3.2 | 3.2 | 1 | 4 | 0 | 90457 | 6961 | 1809.132 |
| `chess-critic` | 2.874 | 10 | 3.4 | 3.4 | 3.56 | 1 | 0 | 1 | 54876 | 1467 | 1097.52 |
| `chess-vision` | 2.011 | 10 | 2.7 | 2.7 | 4.11 | 2 | 0 | 1 | 31278 | 651 | 625.552 |
| `chess-scribe` | 3.105 | 10 | 3 | 3 | 3.7 | 0 | 0 | 0 | 38077 | 5633 | 761.534 |
| `chess-researcher` | 0.221 | 10 | 2.2 | 2.2 | 3.67 | 4 | 0 | 4 | 35799 | 834 | 715.97 |

### `deepseek-v4-flash:cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 54026 | 2670 | 1080.524 |
| `chess-small` | -2.155 | 10 | 1 | 1 | 3.3 | 10 | 0 | 0 | 64336 | 6343 | 1286.72 |
| `chess-large` | 1.445 | 10 | 2.8 | 2.8 | 2.1 | 3 | 2 | 0 | 226906 | 8906 | 4538.124 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 68335 | 12191 | 1366.696 |
| `chess-translator` | 3.025 | 10 | 2.9 | 2.9 | 4.4 | 0 | 1 | 0 | 26006 | 455 | 520.124 |
| `chess-coach` | 2.907 | 10 | 3.4 | 3.4 | 3.78 | 1 | 0 | 1 | 48425 | 6194 | 968.494 |
| `chess-analyst` | 2.45 | 10 | 3.2 | 3.2 | 3.2 | 1 | 4 | 0 | 74234 | 6784 | 1484.676 |
| `chess-critic` | 3.044 | 10 | 3.6 | 3.6 | 3.56 | 1 | 0 | 1 | 43141 | 1693 | 862.81 |
| `chess-vision` | 0.576 | 10 | 2.3 | 2.3 | 4.14 | 4 | 0 | 3 | 21179 | 673 | 423.576 |
| `chess-scribe` | 2.775 | 10 | 2.9 | 2.9 | 4.4 | 1 | 0 | 0 | 28928 | 5624 | 578.566 |
| `chess-researcher` | 1.076 | 10 | 2.4 | 2.4 | 4.57 | 3 | 0 | 3 | 14082 | 808 | 281.64 |

### `deepseek-v4-pro:cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 14621 | 2289 | 584.84 |
| `chess-small` | -2.02 | 10 | 1 | 1 | 4.2 | 10 | 0 | 0 | 28687 | 6596 | 1147.496 |
| `chess-large` | 0.885 | 10 | 2.3 | 2.3 | 3.2 | 3 | 5 | 0 | 64124 | 8459 | 2564.952 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 20090 | 10978 | 803.608 |
| `chess-translator` | 3.1 | 10 | 2.9 | 2.9 | 4.9 | 0 | 1 | 0 | 7708 | 489 | 308.32 |
| `chess-coach` | 3.945 | 10 | 3.9 | 3.9 | 4.2 | 0 | 0 | 0 | 22948 | 6047 | 917.9 |
| `chess-analyst` | 2.515 | 10 | 3.3 | 3.3 | 3.4 | 2 | 1 | 0 | 45181 | 7808 | 1807.248 |
| `chess-critic` | 2.99 | 10 | 3.4 | 3.4 | 4.33 | 1 | 0 | 1 | 25972 | 2074 | 1038.884 |
| `chess-vision` | 1.805 | 10 | 2.6 | 2.6 | 4.63 | 2 | 0 | 2 | 14040 | 892 | 561.616 |
| `chess-scribe` | 3.18 | 10 | 3 | 3 | 4.2 | 0 | 0 | 0 | 32168 | 6331 | 1286.7 |
| `chess-researcher` | 1.119 | 10 | 2.4 | 2.4 | 4.86 | 3 | 0 | 3 | 6303 | 799 | 252.108 |

### `deepseek-v4-pro:cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 20814 | 2467 | 832.576 |
| `chess-small` | -2.065 | 10 | 1 | 1 | 3.9 | 10 | 0 | 0 | 28007 | 6442 | 1120.268 |
| `chess-large` | 1.84 | 10 | 2.9 | 2.9 | 3.5 | 3 | 1 | 0 | 44470 | 7446 | 1778.784 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 13392 | 10661 | 535.696 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 7407 | 499 | 296.296 |
| `chess-coach` | 3.93 | 10 | 3.9 | 3.9 | 4.1 | 0 | 0 | 0 | 26535 | 6143 | 1061.384 |
| `chess-analyst` | 2.88 | 10 | 3.4 | 3.4 | 3.6 | 1 | 2 | 0 | 43396 | 7464 | 1735.84 |
| `chess-critic` | 2.923 | 10 | 3.4 | 3.4 | 3.89 | 1 | 0 | 1 | 38668 | 2568 | 1546.7 |
| `chess-vision` | 2.53 | 10 | 2.8 | 2.8 | 4.67 | 1 | 0 | 1 | 17284 | 873 | 691.368 |
| `chess-scribe` | 3.195 | 10 | 3 | 3 | 4.3 | 0 | 0 | 0 | 20595 | 5703 | 823.804 |
| `chess-researcher` | 0.371 | 10 | 2.2 | 2.2 | 4.67 | 4 | 0 | 4 | 10620 | 924 | 424.78 |

### `deepseek-v4-pro:cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 15140 | 2361 | 605.592 |
| `chess-small` | -1.975 | 10 | 1 | 1 | 4.5 | 10 | 0 | 0 | 18989 | 5919 | 759.544 |
| `chess-large` | 1.692 | 10 | 3.1 | 3.1 | 3.38 | 3 | 0 | 2 | 62401 | 8262 | 2496.056 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 22027 | 10893 | 881.096 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 8208 | 489 | 328.308 |
| `chess-coach` | 3.405 | 10 | 3.4 | 3.4 | 4.1 | 0 | 1 | 0 | 35823 | 6277 | 1432.916 |
| `chess-analyst` | 2.437 | 10 | 3.2 | 3.2 | 3.78 | 1 | 3 | 1 | 35419 | 7434 | 1416.748 |
| `chess-critic` | 2.874 | 10 | 3.4 | 3.4 | 3.56 | 1 | 0 | 1 | 58425 | 2553 | 2337.008 |
| `chess-vision` | 2.79 | 10 | 2.9 | 2.9 | 4.5 | 1 | 0 | 0 | 18705 | 830 | 748.212 |
| `chess-scribe` | 3.15 | 10 | 3 | 3 | 4 | 0 | 0 | 0 | 40153 | 6320 | 1606.108 |
| `chess-researcher` | -0.36 | 10 | 2 | 2 | 4.6 | 5 | 0 | 5 | 10167 | 848 | 406.676 |

### `deepseek-v4-pro:cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 13585 | 2244 | 543.38 |
| `chess-small` | -2.08 | 10 | 1 | 1 | 3.8 | 10 | 0 | 0 | 31196 | 6508 | 1247.828 |
| `chess-large` | 1.534 | 10 | 2.9 | 2.9 | 3.13 | 2 | 3 | 2 | 83570 | 9544 | 3342.788 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 21557 | 10705 | 862.272 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 7943 | 493 | 317.704 |
| `chess-coach` | 3.79 | 10 | 3.7 | 3.7 | 4.3 | 0 | 0 | 0 | 19340 | 5937 | 773.58 |
| `chess-analyst` | 3.515 | 10 | 3.7 | 3.7 | 3.8 | 0 | 2 | 0 | 35819 | 7299 | 1432.752 |
| `chess-critic` | 3.11 | 10 | 3.6 | 3.6 | 4 | 1 | 0 | 1 | 33291 | 2599 | 1331.62 |
| `chess-vision` | 2.547 | 10 | 2.8 | 2.8 | 4.78 | 1 | 0 | 1 | 9611 | 643 | 384.432 |
| `chess-scribe` | 3.21 | 10 | 3 | 3 | 4.4 | 0 | 0 | 0 | 24961 | 6284 | 998.428 |
| `chess-researcher` | 1.097 | 10 | 2.4 | 2.4 | 4.71 | 3 | 0 | 3 | 8051 | 800 | 322.056 |

### `deepseek-v4-pro:cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 14608 | 2216 | 584.328 |
| `chess-small` | -2.035 | 10 | 1 | 1 | 4.1 | 10 | 0 | 0 | 26527 | 6123 | 1061.072 |
| `chess-large` | 3.005 | 10 | 3.5 | 3.5 | 3.2 | 1 | 1 | 0 | 73175 | 8584 | 2927.016 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 15964 | 10786 | 638.548 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 6951 | 506 | 278.036 |
| `chess-coach` | 3.775 | 10 | 3.7 | 3.7 | 4.2 | 0 | 0 | 0 | 24795 | 6184 | 991.792 |
| `chess-analyst` | 2.95 | 10 | 3.5 | 3.5 | 3.5 | 1 | 2 | 0 | 49080 | 7528 | 1963.188 |
| `chess-critic` | 2.803 | 10 | 3.2 | 3.2 | 4.22 | 1 | 0 | 1 | 25243 | 1982 | 1009.724 |
| `chess-vision` | 1.823 | 10 | 2.6 | 2.6 | 4.75 | 2 | 0 | 2 | 12291 | 876 | 491.648 |
| `chess-scribe` | 3.21 | 10 | 3 | 3 | 4.4 | 0 | 0 | 0 | 14472 | 5649 | 578.884 |
| `chess-researcher` | 0.395 | 10 | 2.2 | 2.2 | 4.83 | 4 | 0 | 4 | 5561 | 787 | 222.424 |

### `devstral-small-2:24b-cloud`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.9 | 10 | 1 | 1 | 5 | 10 | 0 | 10 | 2726 | 1557 | 27.262 |
| `chess-small` | -1.9 | 10 | 1 | 1 | 5 | 10 | 0 | 0 | 3690 | 5305 | 36.897 |
| `chess-large` | 1.915 | 10 | 2.9 | 2.9 | 4 | 3 | 1 | 0 | 26121 | 6445 | 261.207 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3376 | 10645 | 33.759 |
| `chess-translator` | 3.3 | 10 | 3 | 3 | 5 | 0 | 0 | 0 | 1539 | 147 | 15.387 |
| `chess-coach` | 3.545 | 10 | 3.7 | 3.7 | 5 | 1 | 0 | 0 | 7101 | 5494 | 71.008 |
| `chess-analyst` | 3.72 | 10 | 3.8 | 3.8 | 4.6 | 0 | 2 | 0 | 17939 | 6320 | 179.388 |
| `chess-critic` | 2.955 | 10 | 3.3 | 3.3 | 4.67 | 1 | 0 | 1 | 11083 | 1371 | 110.833 |
| `chess-vision` | 1.14 | 10 | 2.4 | 2.4 | 5 | 3 | 0 | 3 | 7912 | 544 | 79.124 |
| `chess-scribe` | 3.3 | 10 | 3 | 3 | 5 | 0 | 0 | 0 | 5637 | 5396 | 56.373 |
| `chess-researcher` | -1.74 | 10 | 1.6 | 1.6 | 5 | 7 | 0 | 7 | 1868 | 549 | 18.684 |

### `gemini-3-flash-preview:cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 5381 | 2346 | 215.232 |
| `chess-small` | 4.685 | 10 | 4.4 | 5 | 4.7 | 0 | 0 | 0 | 31617 | 6764 | 1264.66 |
| `chess-large` | 5 | 10 | 5 | 5 | 5 | 0 | 0 | 0 | 14942 | 7076 | 597.668 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 6156 | 11424 | 246.228 |
| `chess-translator` | 0.72 | 10 | 3 | 2.8 | 5 | 6 | 4 | 0 | 3832 | 626 | 153.288 |
| `chess-coach` | 4.91 | 10 | 4.8 | 5 | 5 | 0 | 0 | 0 | 6591 | 6038 | 263.628 |
| `chess-analyst` | 4.065 | 10 | 4.5 | 4.6 | 5 | 1 | 0 | 1 | 12720 | 7140 | 508.784 |
| `chess-critic` | 3.135 | 10 | 4.1 | 4.1 | 5 | 2 | 0 | 2 | 11014 | 2113 | 440.576 |
| `chess-vision` | 3.46 | 10 | 4.2 | 4.3 | 5 | 2 | 0 | 1 | 7181 | 949 | 287.236 |
| `chess-scribe` | 5 | 10 | 5 | 5 | 5 | 0 | 0 | 0 | 7314 | 6050 | 292.564 |
| `chess-researcher` | 0.466 | 10 | 3.2 | 2.6 | 4.57 | 6 | 0 | 3 | 31097 | 1236 | 1243.884 |

### `gemini-3-flash-preview:cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 5228 | 2295 | 209.108 |
| `chess-small` | 4.73 | 10 | 4.4 | 5 | 5 | 0 | 0 | 0 | 7920 | 6472 | 316.788 |
| `chess-large` | 4.955 | 10 | 4.9 | 5 | 5 | 0 | 0 | 0 | 14342 | 7144 | 573.688 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 6903 | 11395 | 276.116 |
| `chess-translator` | 0.72 | 10 | 3 | 2.8 | 5 | 6 | 4 | 0 | 3506 | 599 | 140.244 |
| `chess-coach` | 4.915 | 10 | 4.9 | 4.9 | 5 | 0 | 0 | 0 | 6431 | 6026 | 257.248 |
| `chess-analyst` | 3.975 | 10 | 4.3 | 4.6 | 5 | 1 | 0 | 1 | 10819 | 7036 | 432.768 |
| `chess-critic` | 3.885 | 10 | 4.5 | 4.4 | 5 | 1 | 1 | 1 | 10759 | 2191 | 430.352 |
| `chess-vision` | 4.265 | 10 | 4.5 | 4.6 | 5 | 1 | 0 | 0 | 6128 | 937 | 245.108 |
| `chess-scribe` | 5 | 10 | 5 | 5 | 5 | 0 | 0 | 0 | 7455 | 6113 | 298.18 |
| `chess-researcher` | 0.42 | 10 | 3.1 | 2.5 | 4.5 | 5 | 1 | 4 | 31781 | 1144 | 1271.244 |

### `gemma4:31b-cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 7485 | 1661 | 149.706 |
| `chess-small` | -1.9 | 10 | 1 | 1 | 5 | 10 | 0 | 0 | 5456 | 5326 | 109.124 |
| `chess-large` | 3.515 | 10 | 3.7 | 3.7 | 4.8 | 1 | 0 | 0 | 14277 | 5950 | 285.542 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3267 | 10710 | 65.33 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 1090 | 148 | 21.794 |
| `chess-coach` | 2.395 | 10 | 2.7 | 2.7 | 5 | 1 | 1 | 1 | 4277 | 5373 | 85.546 |
| `chess-analyst` | 2.321 | 10 | 3.2 | 3.2 | 4.67 | 2 | 2 | 1 | 12682 | 6054 | 253.644 |
| `chess-critic` | 2.955 | 10 | 3.3 | 3.3 | 4.67 | 1 | 0 | 1 | 14031 | 1318 | 280.618 |
| `chess-vision` | 2.128 | 10 | 2.7 | 2.7 | 4.89 | 2 | 0 | 1 | 5748 | 409 | 114.966 |
| `chess-scribe` | 1.305 | 10 | 2.3 | 2.3 | 5 | 4 | 0 | 0 | 4490 | 5437 | 89.79 |
| `chess-researcher` | -1.02 | 10 | 1.8 | 1.8 | 5 | 6 | 0 | 6 | 2010 | 524 | 40.204 |

### `gemma4:31b-cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 13977 | 1659 | 279.532 |
| `chess-small` | -1.9 | 10 | 1 | 1 | 5 | 10 | 0 | 0 | 3923 | 5319 | 78.458 |
| `chess-large` | 2.71 | 10 | 3.3 | 3.3 | 4.7 | 2 | 1 | 0 | 13639 | 5965 | 272.772 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 6326 | 10712 | 126.52 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 1507 | 148 | 30.132 |
| `chess-coach` | 3.54 | 10 | 3.4 | 3.4 | 5 | 0 | 1 | 0 | 4533 | 5387 | 90.652 |
| `chess-analyst` | 2.895 | 10 | 3.5 | 3.5 | 4.8 | 2 | 1 | 0 | 13274 | 6106 | 265.48 |
| `chess-critic` | 3.345 | 10 | 3.7 | 3.7 | 5 | 1 | 0 | 1 | 6662 | 1313 | 133.232 |
| `chess-vision` | 3.285 | 10 | 3 | 3 | 4.9 | 0 | 0 | 0 | 7600 | 395 | 151.994 |
| `chess-scribe` | 2.245 | 10 | 2.6 | 2.6 | 4.9 | 2 | 0 | 0 | 6626 | 5502 | 132.52 |
| `chess-researcher` | -1.02 | 10 | 1.8 | 1.8 | 5 | 6 | 0 | 6 | 1725 | 520 | 34.504 |

### `gemma4:31b-cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 2245 | 1654 | 44.908 |
| `chess-small` | -1.9 | 10 | 1 | 1 | 5 | 10 | 0 | 0 | 3568 | 5322 | 71.368 |
| `chess-large` | 2.61 | 10 | 3.2 | 3.2 | 4.6 | 2 | 1 | 0 | 16697 | 5947 | 333.938 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 2544 | 10703 | 50.886 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 1551 | 148 | 31.02 |
| `chess-coach` | 3.27 | 10 | 3 | 3 | 4.8 | 0 | 0 | 0 | 11327 | 5386 | 226.544 |
| `chess-analyst` | 2.219 | 10 | 3.1 | 3.1 | 4.56 | 2 | 2 | 1 | 16040 | 6056 | 320.804 |
| `chess-critic` | 2.691 | 10 | 3.4 | 3.4 | 4.67 | 2 | 0 | 1 | 17253 | 1359 | 345.05 |
| `chess-vision` | 3.27 | 10 | 3 | 3 | 4.8 | 0 | 0 | 0 | 6733 | 426 | 134.656 |
| `chess-scribe` | 2.75 | 10 | 2.8 | 2.8 | 4.8 | 1 | 0 | 0 | 8985 | 5430 | 179.696 |
| `chess-researcher` | -0.3 | 10 | 2 | 2 | 5 | 5 | 0 | 5 | 1090 | 520 | 21.798 |

### `gemma4:31b-cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 8635 | 1657 | 172.692 |
| `chess-small` | -1.9 | 10 | 1 | 1 | 5 | 10 | 0 | 0 | 3135 | 5327 | 62.706 |
| `chess-large` | 4.075 | 10 | 4 | 4 | 4.5 | 0 | 0 | 0 | 23228 | 5998 | 464.568 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3649 | 10710 | 72.986 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 2094 | 148 | 41.886 |
| `chess-coach` | 3.455 | 10 | 3.3 | 3.3 | 5 | 0 | 1 | 0 | 4310 | 5381 | 86.204 |
| `chess-analyst` | 2.104 | 10 | 3.2 | 3.2 | 4.89 | 3 | 1 | 1 | 10027 | 6090 | 200.534 |
| `chess-critic` | 3.142 | 10 | 3.5 | 3.5 | 4.78 | 1 | 0 | 1 | 11728 | 1301 | 234.554 |
| `chess-vision` | 2.547 | 10 | 2.8 | 2.8 | 4.78 | 1 | 0 | 1 | 7991 | 413 | 159.826 |
| `chess-scribe` | 2.75 | 10 | 2.8 | 2.8 | 4.8 | 1 | 0 | 0 | 8350 | 5411 | 166.996 |
| `chess-researcher` | -1.02 | 10 | 1.8 | 1.8 | 5 | 6 | 0 | 6 | 1155 | 524 | 23.104 |

### `gemma4:31b-cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 1212 | 1653 | 24.232 |
| `chess-small` | -1.9 | 10 | 1 | 1 | 5 | 10 | 0 | 0 | 3487 | 5319 | 69.734 |
| `chess-large` | 2.974 | 10 | 3.5 | 3.5 | 4.33 | 1 | 1 | 1 | 19831 | 5973 | 396.62 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4978 | 10714 | 99.566 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 1579 | 148 | 31.584 |
| `chess-coach` | 3.44 | 10 | 3.2 | 3.2 | 4.8 | 0 | 0 | 0 | 6893 | 5384 | 137.868 |
| `chess-analyst` | 2.29 | 10 | 3.2 | 3.2 | 4.8 | 3 | 1 | 0 | 11903 | 6082 | 238.066 |
| `chess-critic` | 3.158 | 10 | 3.5 | 3.5 | 4.89 | 1 | 0 | 1 | 9622 | 1307 | 192.434 |
| `chess-vision` | 3.285 | 10 | 3 | 3 | 4.9 | 0 | 0 | 0 | 6769 | 417 | 135.372 |
| `chess-scribe` | 3.285 | 10 | 3 | 3 | 4.9 | 0 | 0 | 0 | 5451 | 5391 | 109.022 |
| `chess-researcher` | -0.485 | 10 | 1.9 | 1.9 | 5 | 5 | 1 | 5 | 1971 | 536 | 39.416 |

### `glm-5.1:cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 7503 | 1778 | 225.081 |
| `chess-small` | 4.775 | 10 | 4.5 | 5 | 5 | 0 | 0 | 0 | 10515 | 5578 | 315.438 |
| `chess-large` | 4.925 | 10 | 5 | 5 | 4.5 | 0 | 0 | 0 | 33959 | 7818 | 1018.782 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4570 | 10105 | 137.091 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 8100 | 780 | 242.991 |
| `chess-coach` | 2.285 | 10 | 3.7 | 3.8 | 5 | 3 | 0 | 3 | 11109 | 5605 | 333.258 |
| `chess-analyst` | 4.85 | 10 | 4.8 | 5 | 4.6 | 0 | 0 | 0 | 30134 | 6733 | 904.023 |
| `chess-critic` | 3.53 | 10 | 4.6 | 3.9 | 5 | 1 | 3 | 1 | 15782 | 1899 | 473.448 |
| `chess-vision` | 4.145 | 10 | 4.4 | 4.7 | 4.9 | 1 | 1 | 0 | 10114 | 844 | 303.423 |
| `chess-scribe` | 4.51 | 10 | 4.6 | 4.8 | 4.8 | 0 | 2 | 0 | 12414 | 5448 | 372.411 |
| `chess-researcher` | -0.145 | 10 | 2.7 | 2.1 | 5 | 7 | 1 | 2 | 5773 | 831 | 173.184 |

### `glm-5.1:cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 5357 | 1794 | 160.695 |
| `chess-small` | 4.73 | 10 | 4.4 | 5 | 5 | 0 | 0 | 0 | 9686 | 5717 | 290.58 |
| `chess-large` | 4.01 | 10 | 4.6 | 4.6 | 4.33 | 1 | 0 | 1 | 45688 | 8208 | 1370.634 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 6320 | 10074 | 189.6 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 8411 | 755 | 252.315 |
| `chess-coach` | 3.157 | 10 | 4.1 | 4.2 | 4.88 | 2 | 0 | 2 | 19470 | 5797 | 584.091 |
| `chess-analyst` | 4.865 | 10 | 4.8 | 5 | 4.7 | 0 | 0 | 0 | 25145 | 6942 | 754.335 |
| `chess-critic` | 3.44 | 10 | 4.4 | 3.9 | 5 | 1 | 3 | 1 | 15463 | 1884 | 463.878 |
| `chess-vision` | 4.5 | 10 | 4.5 | 4.6 | 4.9 | 0 | 1 | 0 | 13645 | 924 | 409.347 |
| `chess-scribe` | 4.165 | 10 | 4.2 | 4.6 | 4.9 | 0 | 3 | 0 | 10415 | 5394 | 312.435 |
| `chess-researcher` | 1.455 | 10 | 3.3 | 2.8 | 5 | 4 | 3 | 1 | 5704 | 849 | 171.12 |

### `glm-5.1:cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 4166 | 1765 | 124.983 |
| `chess-small` | 4.67 | 10 | 4.3 | 5 | 4.9 | 0 | 0 | 0 | 13413 | 5569 | 402.396 |
| `chess-large` | 4.925 | 10 | 5 | 5 | 4.5 | 0 | 0 | 0 | 32627 | 7267 | 978.819 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4725 | 10120 | 141.759 |
| `chess-translator` | 1.035 | 10 | 3 | 3 | 4.9 | 5 | 5 | 0 | 9807 | 757 | 294.219 |
| `chess-coach` | 3.22 | 10 | 4.2 | 4.2 | 5 | 2 | 0 | 2 | 12547 | 5569 | 376.413 |
| `chess-analyst` | 4.88 | 10 | 4.8 | 5 | 4.8 | 0 | 0 | 0 | 22368 | 6687 | 671.04 |
| `chess-critic` | 3.749 | 10 | 4.5 | 4.1 | 4.89 | 1 | 1 | 1 | 19363 | 1852 | 580.881 |
| `chess-vision` | 4.825 | 10 | 4.8 | 4.9 | 4.7 | 0 | 0 | 0 | 28803 | 997 | 864.096 |
| `chess-scribe` | 4.165 | 10 | 4.2 | 4.6 | 4.9 | 0 | 3 | 0 | 13172 | 5453 | 395.16 |
| `chess-researcher` | 0.745 | 10 | 2.9 | 2.6 | 5 | 5 | 2 | 2 | 4517 | 813 | 135.519 |

### `glm-5.1:cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 5410 | 1795 | 162.306 |
| `chess-small` | 4.67 | 10 | 4.3 | 5 | 4.9 | 0 | 0 | 0 | 10874 | 5839 | 326.223 |
| `chess-large` | 4.865 | 10 | 4.9 | 5 | 4.4 | 0 | 0 | 0 | 42149 | 7643 | 1264.476 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4446 | 10073 | 133.371 |
| `chess-translator` | 0.72 | 10 | 3 | 2.8 | 5 | 6 | 4 | 0 | 6785 | 728 | 203.535 |
| `chess-coach` | 4.093 | 10 | 4.6 | 4.6 | 4.89 | 1 | 0 | 1 | 14833 | 5610 | 444.978 |
| `chess-analyst` | 2.309 | 10 | 3.8 | 3.8 | 4.86 | 3 | 0 | 3 | 51001 | 9999 | 1530.03 |
| `chess-critic` | 3.852 | 10 | 4.5 | 4.4 | 4.78 | 1 | 1 | 1 | 16858 | 2055 | 505.743 |
| `chess-vision` | 4.855 | 10 | 4.8 | 4.9 | 4.9 | 0 | 0 | 0 | 12387 | 1099 | 371.613 |
| `chess-scribe` | 4.55 | 10 | 4.4 | 4.8 | 5 | 0 | 1 | 0 | 7977 | 5241 | 239.298 |
| `chess-researcher` | 1.805 | 10 | 3.3 | 3.3 | 5 | 3 | 5 | 1 | 4917 | 831 | 147.51 |

### `glm-5.1:cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 9014 | 1819 | 270.417 |
| `chess-small` | 4.655 | 10 | 4.3 | 5 | 4.8 | 0 | 0 | 0 | 15442 | 5636 | 463.26 |
| `chess-large` | 3.981 | 10 | 4.5 | 4.6 | 4.44 | 1 | 0 | 1 | 33020 | 7580 | 990.585 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3502 | 10097 | 105.06 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 5496 | 700 | 164.883 |
| `chess-coach` | 4.093 | 10 | 4.6 | 4.6 | 4.89 | 1 | 0 | 1 | 14729 | 5766 | 441.882 |
| `chess-analyst` | 4.85 | 10 | 4.7 | 5 | 4.9 | 0 | 0 | 0 | 20352 | 6914 | 610.563 |
| `chess-critic` | 3.145 | 10 | 4.5 | 3.8 | 4.67 | 2 | 2 | 1 | 22455 | 2572 | 673.65 |
| `chess-vision` | 4.955 | 10 | 4.9 | 5 | 5 | 0 | 0 | 0 | 7974 | 977 | 239.211 |
| `chess-scribe` | 4.18 | 10 | 4.2 | 4.6 | 5 | 0 | 3 | 0 | 8671 | 5379 | 260.127 |
| `chess-researcher` | 0.675 | 10 | 2.9 | 2.8 | 5 | 6 | 0 | 2 | 4620 | 825 | 138.585 |

### `gpt-oss:120b-cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3042 | 1701 | 60.832 |
| `chess-small` | 4.64 | 10 | 4.2 | 5 | 5 | 0 | 0 | 0 | 6863 | 5293 | 137.252 |
| `chess-large` | 4.91 | 10 | 5 | 5 | 4.4 | 0 | 0 | 0 | 36173 | 7776 | 723.464 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 5261 | 10359 | 105.21 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 3089 | 387 | 61.778 |
| `chess-coach` | 2.33 | 10 | 3.8 | 3.8 | 5 | 3 | 0 | 3 | 9662 | 5551 | 193.244 |
| `chess-analyst` | 4.925 | 10 | 4.9 | 5 | 4.8 | 0 | 0 | 0 | 23196 | 7089 | 463.92 |
| `chess-critic` | 3.852 | 10 | 4.5 | 4.4 | 4.78 | 1 | 1 | 1 | 21563 | 2752 | 431.25 |
| `chess-vision` | 3.755 | 10 | 4.3 | 4.3 | 5 | 1 | 1 | 1 | 8835 | 922 | 176.694 |
| `chess-scribe` | 4.27 | 10 | 4.4 | 4.6 | 5 | 0 | 3 | 0 | 6022 | 5170 | 120.43 |
| `chess-researcher` | -2.385 | 10 | 1.7 | 1.5 | 5 | 8 | 1 | 8 | 3848 | 822 | 76.956 |

### `gpt-oss:120b-cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3349 | 1708 | 66.982 |
| `chess-small` | 4.73 | 10 | 4.4 | 5 | 5 | 0 | 0 | 0 | 8873 | 5389 | 177.454 |
| `chess-large` | 4.88 | 10 | 5 | 5 | 4.2 | 0 | 0 | 0 | 35117 | 7630 | 702.342 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 6820 | 10509 | 136.39 |
| `chess-translator` | 1.38 | 10 | 3 | 3.2 | 5 | 4 | 6 | 0 | 2633 | 368 | 52.656 |
| `chess-coach` | 3.22 | 10 | 4.2 | 4.2 | 5 | 2 | 0 | 2 | 9875 | 5522 | 197.506 |
| `chess-analyst` | 4.925 | 10 | 4.9 | 5 | 4.8 | 0 | 0 | 0 | 21051 | 7115 | 421.012 |
| `chess-critic` | 3.869 | 10 | 4.5 | 4.4 | 4.89 | 1 | 1 | 1 | 18734 | 2573 | 374.674 |
| `chess-vision` | 4.91 | 10 | 4.8 | 5 | 5 | 0 | 0 | 0 | 7748 | 857 | 154.96 |
| `chess-scribe` | 4.23 | 10 | 4.4 | 4.5 | 5 | 0 | 3 | 0 | 6823 | 5253 | 136.452 |
| `chess-researcher` | -1.055 | 10 | 2.3 | 1.9 | 5 | 6 | 3 | 6 | 5060 | 905 | 101.202 |

### `gpt-oss:120b-cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 2991 | 1669 | 59.81 |
| `chess-small` | 4.805 | 10 | 4.6 | 5 | 4.9 | 0 | 0 | 0 | 11010 | 5717 | 220.202 |
| `chess-large` | 4.94 | 10 | 5 | 5 | 4.6 | 0 | 0 | 0 | 30106 | 7610 | 602.11 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 8715 | 10691 | 174.296 |
| `chess-translator` | 1.38 | 10 | 3 | 3.2 | 5 | 4 | 6 | 0 | 2645 | 386 | 52.902 |
| `chess-coach` | 3.18 | 10 | 4.2 | 4.1 | 5 | 2 | 0 | 2 | 10002 | 5596 | 200.04 |
| `chess-analyst` | 4.032 | 10 | 4.5 | 4.6 | 4.78 | 1 | 0 | 1 | 20819 | 7014 | 416.38 |
| `chess-critic` | 3.447 | 10 | 4.4 | 4 | 4.78 | 1 | 3 | 1 | 23285 | 3022 | 465.698 |
| `chess-vision` | 4.81 | 10 | 4.8 | 5 | 5 | 0 | 1 | 0 | 6555 | 841 | 131.094 |
| `chess-scribe` | 4.27 | 10 | 4.4 | 4.6 | 5 | 0 | 3 | 0 | 7025 | 5254 | 140.492 |
| `chess-researcher` | -1.915 | 10 | 1.9 | 1.7 | 5 | 8 | 0 | 7 | 3634 | 845 | 72.686 |

### `gpt-oss:20b-cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.28 | 10 | 1.2 | 1.2 | 5 | 9 | 1 | 9 | 2547 | 1991 | 25.47 |
| `chess-small` | 4.565 | 10 | 4.1 | 5 | 4.8 | 0 | 0 | 0 | 10141 | 6826 | 101.41 |
| `chess-large` | 3.876 | 10 | 4.6 | 4.6 | 3.44 | 1 | 0 | 1 | 95745 | 25948 | 957.452 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3462 | 10561 | 34.624 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 2426 | 565 | 24.263 |
| `chess-coach` | 3.94 | 10 | 4.4 | 4.4 | 5 | 1 | 0 | 1 | 8237 | 6279 | 82.368 |
| `chess-analyst` | 4.91 | 10 | 4.8 | 5 | 5 | 0 | 0 | 0 | 13774 | 7914 | 137.736 |
| `chess-critic` | 3.66 | 10 | 4.4 | 4.2 | 5 | 1 | 2 | 1 | 11304 | 2995 | 113.037 |
| `chess-vision` | 3.92 | 10 | 4.4 | 4.6 | 5 | 1 | 1 | 1 | 4816 | 1075 | 48.16 |
| `chess-scribe` | 4.185 | 10 | 4.3 | 4.5 | 5 | 0 | 3 | 0 | 4379 | 5533 | 43.787 |
| `chess-researcher` | -3.05 | 10 | 1.4 | 1.3 | 5 | 9 | 0 | 9 | 1940 | 858 | 19.403 |

### `gpt-oss:20b-cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.28 | 10 | 1.2 | 1.2 | 5 | 9 | 1 | 9 | 3394 | 2068 | 33.936 |
| `chess-small` | 4.58 | 10 | 4.1 | 5 | 4.9 | 0 | 0 | 0 | 8819 | 6422 | 88.189 |
| `chess-large` | 2.977 | 10 | 4.2 | 4.2 | 3.38 | 2 | 0 | 2 | 164768 | 39636 | 1647.676 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3515 | 10512 | 35.145 |
| `chess-translator` | 1.365 | 10 | 3 | 3.2 | 4.9 | 4 | 6 | 0 | 5981 | 1231 | 59.806 |
| `chess-coach` | 3.66 | 10 | 4.4 | 4.2 | 5 | 1 | 2 | 1 | 5537 | 5691 | 55.373 |
| `chess-analyst` | 4.955 | 10 | 4.9 | 5 | 5 | 0 | 0 | 0 | 11843 | 7283 | 118.431 |
| `chess-critic` | 3.485 | 10 | 4.5 | 3.9 | 5 | 1 | 3 | 1 | 9679 | 2499 | 96.785 |
| `chess-vision` | 4.685 | 10 | 4.7 | 4.8 | 5 | 0 | 1 | 0 | 3195 | 735 | 31.953 |
| `chess-scribe` | 4.11 | 10 | 4.2 | 4.5 | 4.8 | 0 | 3 | 0 | 7579 | 6199 | 75.785 |
| `chess-researcher` | -2.2 | 10 | 1.8 | 1.6 | 5 | 8 | 0 | 8 | 1983 | 875 | 19.83 |

### `gpt-oss:20b-cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.28 | 10 | 1.2 | 1.2 | 5 | 9 | 1 | 9 | 4606 | 2460 | 46.056 |
| `chess-small` | 4.565 | 10 | 4.1 | 5 | 4.8 | 0 | 0 | 0 | 8609 | 6702 | 86.094 |
| `chess-large` | 4.685 | 10 | 5 | 5 | 2.9 | 0 | 0 | 0 | 125910 | 33422 | 1259.104 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4782 | 10833 | 47.816 |
| `chess-translator` | 1.38 | 10 | 3 | 3.2 | 5 | 4 | 6 | 0 | 2064 | 493 | 20.644 |
| `chess-coach` | 3.76 | 10 | 4.4 | 4.2 | 5 | 1 | 1 | 1 | 7649 | 6099 | 76.489 |
| `chess-analyst` | 5 | 10 | 5 | 5 | 5 | 0 | 0 | 0 | 9733 | 7056 | 97.331 |
| `chess-critic` | 3.01 | 10 | 4.4 | 3.7 | 5 | 2 | 3 | 1 | 7097 | 2142 | 70.971 |
| `chess-vision` | 3.715 | 10 | 4.3 | 4.2 | 5 | 1 | 1 | 1 | 3200 | 814 | 31.995 |
| `chess-scribe` | 4.185 | 10 | 4.3 | 4.5 | 5 | 0 | 3 | 0 | 2514 | 5129 | 25.136 |
| `chess-researcher` | -2.385 | 10 | 1.7 | 1.5 | 5 | 8 | 1 | 8 | 1882 | 852 | 18.819 |

### `kimi-k2.5:cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.28 | 10 | 1.2 | 1.2 | 5 | 9 | 1 | 9 | 29143 | 3416 | 874.299 |
| `chess-small` | 4.595 | 10 | 4.4 | 5 | 4.1 | 0 | 0 | 0 | 51045 | 8001 | 1531.344 |
| `chess-large` | 4.685 | 10 | 4.9 | 5 | 3.2 | 0 | 0 | 0 | 73874 | 11140 | 2216.223 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 15395 | 10963 | 461.841 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 15115 | 1366 | 453.453 |
| `chess-coach` | 4.91 | 10 | 4.9 | 5 | 4.7 | 0 | 0 | 0 | 25793 | 6761 | 773.799 |
| `chess-analyst` | 3.927 | 10 | 4.6 | 4.6 | 3.78 | 1 | 0 | 1 | 58307 | 8681 | 1749.207 |
| `chess-critic` | 3.542 | 10 | 4.3 | 4.1 | 4.78 | 1 | 2 | 1 | 22624 | 2444 | 678.723 |
| `chess-vision` | 1.369 | 10 | 3.3 | 3.4 | 4.83 | 4 | 0 | 4 | 14352 | 1426 | 430.563 |
| `chess-scribe` | 4.45 | 10 | 4.5 | 4.8 | 4.7 | 0 | 2 | 0 | 27378 | 6573 | 821.325 |
| `chess-researcher` | 0.82 | 10 | 3 | 2.8 | 5 | 6 | 1 | 1 | 6034 | 1215 | 181.008 |

### `kimi-k2.5:cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.28 | 10 | 1.2 | 1.2 | 5 | 9 | 1 | 9 | 22254 | 3411 | 667.614 |
| `chess-small` | 4.595 | 10 | 4.3 | 5 | 4.4 | 0 | 0 | 0 | 38221 | 8811 | 1146.633 |
| `chess-large` | 3.944 | 10 | 4.6 | 4.6 | 3.89 | 1 | 0 | 1 | 50154 | 9114 | 1504.629 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 9302 | 10788 | 279.051 |
| `chess-translator` | 0.72 | 10 | 3 | 2.8 | 5 | 6 | 4 | 0 | 12375 | 1277 | 371.256 |
| `chess-coach` | 3.999 | 10 | 4.5 | 4.6 | 4.56 | 1 | 0 | 1 | 31343 | 7265 | 940.281 |
| `chess-analyst` | 4.835 | 10 | 4.9 | 5 | 4.2 | 0 | 0 | 0 | 43187 | 8420 | 1295.598 |
| `chess-critic` | 4.325 | 10 | 4.7 | 4.4 | 5 | 0 | 3 | 0 | 17191 | 2231 | 515.739 |
| `chess-vision` | 2.309 | 10 | 3.8 | 3.8 | 4.86 | 3 | 0 | 3 | 20344 | 1569 | 610.308 |
| `chess-scribe` | 4.42 | 10 | 4.5 | 4.8 | 4.5 | 0 | 2 | 0 | 25297 | 6077 | 758.907 |
| `chess-researcher` | 2.07 | 10 | 3.6 | 3.5 | 5 | 4 | 3 | 0 | 12529 | 1206 | 375.873 |

### `kimi-k2.6:cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 35247 | 3598 | 1057.404 |
| `chess-small` | -2.2 | 10 | 1 | 1 | 3 | 10 | 0 | 0 | 78401 | 10456 | 2352.03 |
| `chess-large` | 1.702 | 10 | 3.1 | 3.1 | 2.11 | 3 | 0 | 1 | 156313 | 13517 | 4689.384 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 35302 | 12030 | 1059.054 |
| `chess-translator` | 2.95 | 10 | 2.9 | 2.9 | 3.9 | 0 | 1 | 0 | 28008 | 1633 | 840.234 |
| `chess-coach` | 2.686 | 10 | 3.2 | 3.2 | 3.44 | 1 | 0 | 1 | 46004 | 7240 | 1380.108 |
| `chess-analyst` | 2.295 | 10 | 3.2 | 3.2 | 2.5 | 2 | 1 | 0 | 120156 | 12316 | 3604.674 |
| `chess-critic` | 2.874 | 10 | 3.4 | 3.4 | 3.56 | 1 | 0 | 1 | 50482 | 2933 | 1514.46 |
| `chess-vision` | 2.76 | 10 | 2.9 | 2.9 | 4.3 | 1 | 0 | 0 | 22807 | 1481 | 684.222 |
| `chess-scribe` | 2.73 | 10 | 2.9 | 2.9 | 4.1 | 1 | 0 | 0 | 26752 | 6190 | 802.563 |
| `chess-researcher` | 1.785 | 10 | 2.6 | 2.6 | 4.5 | 2 | 0 | 2 | 15256 | 1545 | 457.683 |

### `kimi-k2.6:cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 25665 | 4335 | 769.947 |
| `chess-small` | -2.155 | 10 | 1 | 1 | 3.3 | 10 | 0 | 0 | 65275 | 10870 | 1958.256 |
| `chess-large` | 0.927 | 10 | 2.6 | 2.6 | 2.78 | 4 | 1 | 1 | 121444 | 15591 | 3643.32 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 36034 | 12665 | 1081.026 |
| `chess-translator` | 2.98 | 10 | 2.9 | 2.9 | 4.1 | 0 | 1 | 0 | 23812 | 1607 | 714.351 |
| `chess-coach` | 2.721 | 10 | 3.2 | 3.2 | 3.67 | 1 | 0 | 1 | 41822 | 7640 | 1254.651 |
| `chess-analyst` | 3.58 | 10 | 3.8 | 3.8 | 3 | 0 | 1 | 0 | 97675 | 10595 | 2930.259 |
| `chess-critic` | 2.839 | 10 | 3.4 | 3.4 | 3.33 | 1 | 0 | 1 | 72818 | 5043 | 2184.543 |
| `chess-vision` | 2.028 | 10 | 2.7 | 2.7 | 4.22 | 2 | 0 | 1 | 21231 | 1497 | 636.918 |
| `chess-scribe` | 2.195 | 10 | 2.7 | 2.7 | 4 | 2 | 0 | 0 | 35643 | 6387 | 1069.275 |
| `chess-researcher` | 2.514 | 10 | 2.8 | 2.8 | 4.56 | 1 | 0 | 1 | 19423 | 1258 | 582.69 |

### `kimi-k2.6:cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.245 | 10 | 1.3 | 1.3 | 4 | 9 | 0 | 9 | 41909 | 3498 | 1257.261 |
| `chess-small` | -2.29 | 10 | 1 | 1 | 2.4 | 10 | 0 | 0 | 143386 | 11306 | 4301.565 |
| `chess-large` | 1.89 | 10 | 3 | 3 | 1.6 | 2 | 2 | 0 | 249013 | 15347 | 7470.396 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 38416 | 12032 | 1152.471 |
| `chess-translator` | 2.995 | 10 | 2.9 | 2.9 | 4.2 | 0 | 1 | 0 | 22252 | 1210 | 667.548 |
| `chess-coach` | 3.385 | 10 | 3.4 | 3.4 | 3.3 | 0 | 0 | 0 | 74253 | 8522 | 2227.578 |
| `chess-analyst` | 2.391 | 10 | 3.5 | 3.5 | 2.11 | 2 | 0 | 1 | 163420 | 12912 | 4902.585 |
| `chess-critic` | 2.823 | 10 | 3.4 | 3.4 | 3.22 | 1 | 0 | 1 | 66314 | 3492 | 1989.411 |
| `chess-vision` | 1.71 | 10 | 2.6 | 2.6 | 4 | 2 | 0 | 2 | 48445 | 1856 | 1453.353 |
| `chess-scribe` | 2.7 | 10 | 2.9 | 2.9 | 3.9 | 1 | 0 | 0 | 33608 | 6289 | 1008.252 |
| `chess-researcher` | 1.054 | 10 | 2.4 | 2.4 | 4.43 | 3 | 0 | 3 | 15821 | 1129 | 474.615 |

### `kimi-k2.6:cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 17208 | 3872 | 516.225 |
| `chess-small` | -2.08 | 10 | 1 | 1 | 3.8 | 10 | 0 | 0 | 44693 | 12449 | 1340.784 |
| `chess-large` | 1.48 | 10 | 2.7 | 2.7 | 2.9 | 3 | 2 | 0 | 82705 | 15987 | 2481.159 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 13529 | 11866 | 405.861 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 7753 | 1674 | 232.599 |
| `chess-coach` | 2.871 | 10 | 3.3 | 3.3 | 4.11 | 1 | 0 | 1 | 48220 | 6906 | 1446.612 |
| `chess-analyst` | 2.021 | 10 | 3.3 | 3.3 | 3.11 | 3 | 0 | 1 | 61433 | 13385 | 1842.996 |
| `chess-critic` | 3.025 | 10 | 3.5 | 3.5 | 4 | 1 | 0 | 1 | 34025 | 5248 | 1020.753 |
| `chess-vision` | 2.128 | 10 | 2.7 | 2.7 | 4.89 | 2 | 0 | 1 | 14010 | 2464 | 420.288 |
| `chess-scribe` | 2.79 | 10 | 2.9 | 2.9 | 4.5 | 1 | 0 | 0 | 18101 | 7200 | 543.036 |
| `chess-researcher` | 2.58 | 10 | 2.8 | 2.8 | 5 | 1 | 0 | 1 | 4959 | 1344 | 148.764 |

### `kimi-k2.6:cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -2.365 | 10 | 1.6 | 1.6 | 4.5 | 8 | 0 | 8 | 21754 | 3751 | 652.605 |
| `chess-small` | -2.17 | 10 | 1 | 1 | 3.2 | 10 | 0 | 0 | 69942 | 10917 | 2098.263 |
| `chess-large` | 1.819 | 10 | 3.1 | 3.1 | 2.89 | 3 | 0 | 1 | 96949 | 14620 | 2908.479 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 25038 | 12697 | 751.14 |
| `chess-translator` | 3.04 | 10 | 2.9 | 2.9 | 4.5 | 0 | 1 | 0 | 17770 | 1423 | 533.085 |
| `chess-coach` | 3.7 | 10 | 3.7 | 3.7 | 3.7 | 0 | 0 | 0 | 48470 | 8691 | 1454.106 |
| `chess-analyst` | 2.765 | 10 | 3.6 | 3.6 | 2.7 | 2 | 0 | 0 | 86099 | 11496 | 2582.979 |
| `chess-critic` | 3.645 | 10 | 3.6 | 3.6 | 3.9 | 0 | 0 | 0 | 35418 | 3468 | 1062.552 |
| `chess-vision` | 2.775 | 10 | 2.9 | 2.9 | 4.4 | 1 | 0 | 0 | 15779 | 1785 | 473.376 |
| `chess-scribe` | 2.76 | 10 | 2.9 | 2.9 | 4.3 | 1 | 0 | 0 | 21841 | 6659 | 655.236 |
| `chess-researcher` | 2.514 | 10 | 2.8 | 2.8 | 4.56 | 1 | 0 | 1 | 13786 | 1310 | 413.574 |

### `minimax-m2.7:cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 7369 | 1730 | 147.382 |
| `chess-small` | 4.685 | 10 | 4.4 | 5 | 4.7 | 0 | 0 | 0 | 33119 | 6526 | 662.38 |
| `chess-large` | 4.775 | 10 | 5 | 5 | 3.5 | 0 | 0 | 0 | 110056 | 9489 | 2201.114 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 7631 | 10299 | 152.626 |
| `chess-translator` | 2.775 | 10 | 3.7 | 3.9 | 5 | 2 | 5 | 0 | 7352 | 454 | 147.044 |
| `chess-coach` | -0.34 | 10 | 2.6 | 2.6 | 5 | 6 | 0 | 6 | 16448 | 5625 | 328.964 |
| `chess-analyst` | 4.775 | 10 | 4.8 | 5 | 4.1 | 0 | 0 | 0 | 41289 | 7551 | 825.774 |
| `chess-critic` | 3.395 | 10 | 4.5 | 3.8 | 4.67 | 1 | 3 | 1 | 22143 | 2089 | 442.862 |
| `chess-vision` | 4.91 | 10 | 4.8 | 5 | 5 | 0 | 0 | 0 | 11630 | 905 | 232.604 |
| `chess-scribe` | 4.23 | 10 | 4.4 | 4.5 | 5 | 0 | 3 | 0 | 10013 | 5178 | 200.268 |
| `chess-researcher` | 1.182 | 10 | 3.2 | 2.9 | 4.88 | 5 | 0 | 2 | 15408 | 1213 | 308.162 |

### `minimax-m2.7:cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.01 | 10 | 1.4 | 1.4 | 5 | 9 | 0 | 9 | 9568 | 1696 | 191.35 |
| `chess-small` | 4.805 | 10 | 4.6 | 5 | 4.9 | 0 | 0 | 0 | 23221 | 5976 | 464.416 |
| `chess-large` | 4.94 | 10 | 5 | 5 | 4.6 | 0 | 0 | 0 | 30718 | 6383 | 614.354 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 5463 | 10281 | 109.258 |
| `chess-translator` | 1.14 | 10 | 3.4 | 2.9 | 5 | 6 | 2 | 0 | 6601 | 419 | 132.028 |
| `chess-coach` | 3.175 | 10 | 4.1 | 4.2 | 5 | 2 | 0 | 2 | 13791 | 5492 | 275.828 |
| `chess-analyst` | 4.044 | 10 | 4.6 | 4.6 | 4.56 | 1 | 0 | 1 | 26714 | 6369 | 534.272 |
| `chess-critic` | 3.25 | 10 | 4.4 | 3.8 | 4.67 | 1 | 4 | 1 | 29883 | 2432 | 597.656 |
| `chess-vision` | 4.485 | 10 | 4.6 | 4.6 | 4.5 | 0 | 1 | 0 | 30384 | 1788 | 607.688 |
| `chess-scribe` | 4.47 | 10 | 4.6 | 4.7 | 4.8 | 0 | 2 | 0 | 17470 | 5362 | 349.394 |
| `chess-researcher` | 0.132 | 10 | 2.9 | 2.3 | 4.71 | 6 | 1 | 3 | 17470 | 1316 | 349.4 |

### `minimax-m2.7:cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 9893 | 1738 | 197.864 |
| `chess-small` | 4.715 | 10 | 4.6 | 5 | 4.3 | 0 | 0 | 0 | 40968 | 6900 | 819.36 |
| `chess-large` | 4.85 | 10 | 5 | 5 | 4 | 0 | 0 | 0 | 111805 | 10778 | 2236.094 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 7913 | 10268 | 158.256 |
| `chess-translator` | 1.67 | 10 | 3 | 3.3 | 5 | 3 | 7 | 0 | 7322 | 388 | 146.444 |
| `chess-coach` | 2.287 | 10 | 3.8 | 3.8 | 4.71 | 3 | 0 | 3 | 24068 | 5852 | 481.36 |
| `chess-analyst` | 4.955 | 10 | 4.9 | 5 | 5 | 0 | 0 | 0 | 17490 | 6356 | 349.802 |
| `chess-critic` | 3.255 | 10 | 4.5 | 3.7 | 4.67 | 1 | 4 | 1 | 24583 | 1947 | 491.666 |
| `chess-vision` | 3.959 | 10 | 4.5 | 4.5 | 4.56 | 1 | 0 | 1 | 34024 | 1892 | 680.482 |
| `chess-scribe` | 4.755 | 10 | 4.8 | 4.9 | 4.9 | 0 | 1 | 0 | 12022 | 5255 | 240.448 |
| `chess-researcher` | 1.59 | 10 | 3.6 | 2.8 | 5 | 4 | 3 | 1 | 8345 | 925 | 166.906 |

### `minimax-m2.7:cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 7276 | 1630 | 145.526 |
| `chess-small` | 4.775 | 10 | 4.6 | 5 | 4.7 | 0 | 0 | 0 | 34808 | 6372 | 696.16 |
| `chess-large` | 3.108 | 10 | 4.2 | 4.2 | 4.25 | 2 | 0 | 2 | 41751 | 6594 | 835.014 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 7677 | 10352 | 153.546 |
| `chess-translator` | 1.61 | 10 | 3.2 | 3.3 | 5 | 4 | 5 | 0 | 7941 | 408 | 158.81 |
| `chess-coach` | 4.093 | 10 | 4.6 | 4.6 | 4.89 | 1 | 0 | 1 | 19608 | 5563 | 392.168 |
| `chess-analyst` | 4.925 | 10 | 5 | 5 | 4.5 | 0 | 0 | 0 | 37842 | 6626 | 756.836 |
| `chess-critic` | 3.279 | 10 | 4.5 | 3.8 | 4.56 | 1 | 4 | 1 | 34982 | 2431 | 699.632 |
| `chess-vision` | 4.97 | 10 | 5 | 5 | 4.8 | 0 | 0 | 0 | 19366 | 1121 | 387.31 |
| `chess-scribe` | 4.455 | 10 | 4.5 | 4.7 | 5 | 0 | 2 | 0 | 15345 | 5295 | 306.894 |
| `chess-researcher` | 1.02 | 10 | 3.2 | 2.7 | 5 | 5 | 1 | 2 | 10089 | 857 | 201.772 |

### `minimax-m2.7:cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 8255 | 1676 | 165.104 |
| `chess-small` | 4.715 | 10 | 4.5 | 5 | 4.6 | 0 | 0 | 0 | 26325 | 6114 | 526.492 |
| `chess-large` | 4.91 | 10 | 5 | 5 | 4.4 | 0 | 0 | 0 | 39844 | 6265 | 796.886 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 9245 | 10372 | 184.902 |
| `chess-translator` | 1.38 | 10 | 3 | 3.2 | 5 | 4 | 6 | 0 | 6270 | 393 | 125.396 |
| `chess-coach` | 1.35 | 10 | 3.2 | 3.4 | 5 | 4 | 0 | 4 | 21537 | 5614 | 430.732 |
| `chess-analyst` | 4.94 | 10 | 5 | 5 | 4.6 | 0 | 0 | 0 | 32125 | 6610 | 642.492 |
| `chess-critic` | 2.965 | 10 | 4.6 | 3.3 | 4.5 | 2 | 4 | 0 | 27601 | 2017 | 552.02 |
| `chess-vision` | 3.729 | 10 | 4.3 | 4.4 | 4.56 | 1 | 1 | 1 | 30623 | 1694 | 612.464 |
| `chess-scribe` | 4.445 | 10 | 4.6 | 4.6 | 4.9 | 0 | 2 | 0 | 13619 | 5382 | 272.386 |
| `chess-researcher` | 1.45 | 10 | 3.4 | 2.8 | 5 | 5 | 0 | 1 | 12167 | 1160 | 243.34 |

### `ministral-3:3b-cloud`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.9 | 10 | 1 | 1 | 5 | 10 | 0 | 10 | 1636 | 1619 | 16.364 |
| `chess-small` | -2.3 | 10 | 1 | 1 | 5 | 10 | 0 | 2 | 3143 | 5511 | 31.433 |
| `chess-large` | 0.925 | 10 | 2.4 | 2.4 | 4.9 | 5 | 1 | 0 | 12334 | 7141 | 123.335 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 2532 | 10826 | 25.317 |
| `chess-translator` | 3.3 | 10 | 3 | 3 | 5 | 0 | 0 | 0 | 1545 | 287 | 15.446 |
| `chess-coach` | 3.26 | 10 | 3.6 | 3.6 | 5 | 1 | 0 | 1 | 4175 | 5682 | 41.747 |
| `chess-analyst` | 0.941 | 10 | 2.4 | 2.4 | 4.67 | 4 | 2 | 1 | 13206 | 7757 | 132.059 |
| `chess-critic` | 3.09 | 10 | 3.4 | 3.4 | 5 | 1 | 0 | 1 | 8928 | 2156 | 89.276 |
| `chess-vision` | -0.205 | 10 | 2.1 | 2.2 | 4.8 | 5 | 0 | 5 | 5186 | 892 | 51.857 |
| `chess-scribe` | 2.075 | 10 | 2.5 | 2.5 | 5 | 2 | 1 | 0 | 6689 | 6096 | 66.889 |
| `chess-researcher` | 2.58 | 10 | 2.8 | 2.8 | 5 | 1 | 0 | 1 | 4302 | 1291 | 43.024 |

### `nemotron-3-nano:30b-cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3540 | 2120 | 35.395 |
| `chess-small` | 4.55 | 10 | 4 | 5 | 5 | 0 | 0 | 0 | 8512 | 6698 | 85.119 |
| `chess-large` | 4.94 | 10 | 5 | 5 | 4.6 | 0 | 0 | 0 | 20939 | 9378 | 209.391 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4836 | 11350 | 48.36 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 2462 | 557 | 24.621 |
| `chess-coach` | 4.84 | 10 | 5 | 4.6 | 5 | 0 | 0 | 0 | 5878 | 6273 | 58.784 |
| `chess-analyst` | 4.91 | 10 | 4.8 | 5 | 5 | 0 | 0 | 0 | 13926 | 8137 | 139.256 |
| `chess-critic` | 2.33 | 10 | 3.8 | 3.8 | 5 | 3 | 0 | 3 | 130676 | 28353 | 1306.755 |
| `chess-vision` | 4.065 | 10 | 4.5 | 4.6 | 5 | 1 | 0 | 1 | 5440 | 1163 | 54.4 |
| `chess-scribe` | 4.37 | 10 | 4.4 | 4.6 | 5 | 0 | 2 | 0 | 3870 | 5790 | 38.703 |
| `chess-researcher` | 1.71 | 10 | 3.4 | 3.2 | 5 | 3 | 2 | 3 | 3148 | 999 | 31.479 |

### `nemotron-3-nano:30b-cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.28 | 10 | 1.2 | 1.2 | 5 | 9 | 1 | 9 | 3849 | 2138 | 38.487 |
| `chess-small` | 4.55 | 10 | 4 | 5 | 5 | 0 | 0 | 0 | 8457 | 6753 | 84.572 |
| `chess-large` | 4.044 | 10 | 4.6 | 4.6 | 4.56 | 1 | 0 | 1 | 85966 | 22390 | 859.662 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 8493 | 12174 | 84.934 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 2338 | 502 | 23.379 |
| `chess-coach` | 3.14 | 10 | 4.2 | 4 | 5 | 2 | 0 | 2 | 6195 | 6301 | 61.946 |
| `chess-analyst` | 3.987 | 10 | 4.4 | 4.6 | 4.78 | 1 | 0 | 1 | 15874 | 8604 | 158.736 |
| `chess-critic` | 3.84 | 10 | 4.4 | 4.4 | 5 | 1 | 1 | 1 | 9272 | 2525 | 92.72 |
| `chess-vision` | 3.975 | 10 | 4.3 | 4.6 | 5 | 1 | 0 | 1 | 6629 | 1226 | 66.288 |
| `chess-scribe` | 4.46 | 10 | 4.6 | 4.6 | 5 | 0 | 2 | 0 | 4077 | 5745 | 40.774 |
| `chess-researcher` | -0.035 | 10 | 2.7 | 2.5 | 5 | 6 | 1 | 4 | 3450 | 1018 | 34.501 |

### `nemotron-3-super:cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3609 | 2095 | 72.178 |
| `chess-small` | 4.595 | 10 | 4.1 | 5 | 5 | 0 | 0 | 0 | 5563 | 5928 | 111.256 |
| `chess-large` | 4.94 | 10 | 5 | 5 | 4.6 | 0 | 0 | 0 | 28518 | 9294 | 570.36 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4333 | 11032 | 86.656 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 2313 | 422 | 46.256 |
| `chess-coach` | 3.554 | 10 | 4.2 | 4.2 | 4.89 | 1 | 2 | 1 | 11812 | 6669 | 236.248 |
| `chess-analyst` | 4.895 | 10 | 4.8 | 5 | 4.9 | 0 | 0 | 0 | 17312 | 7787 | 346.234 |
| `chess-critic` | 3.84 | 10 | 4.4 | 4.4 | 5 | 1 | 1 | 1 | 13190 | 2495 | 263.792 |
| `chess-vision` | 4.125 | 10 | 4.5 | 4.5 | 5 | 1 | 1 | 0 | 6332 | 1020 | 126.642 |
| `chess-scribe` | 4.185 | 10 | 4.3 | 4.5 | 5 | 0 | 3 | 0 | 4525 | 5535 | 90.5 |
| `chess-researcher` | -1.785 | 10 | 1.9 | 1.4 | 5 | 9 | 0 | 4 | 3617 | 839 | 72.33 |

### `nemotron-3-super:cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3942 | 1996 | 78.848 |
| `chess-small` | 4.595 | 10 | 4.1 | 5 | 5 | 0 | 0 | 0 | 6232 | 5999 | 124.632 |
| `chess-large` | 4.061 | 10 | 4.6 | 4.6 | 4.67 | 1 | 0 | 1 | 32200 | 9381 | 644.006 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4893 | 11017 | 97.862 |
| `chess-translator` | 1.38 | 10 | 3 | 3.2 | 5 | 4 | 6 | 0 | 2660 | 437 | 53.208 |
| `chess-coach` | 3.99 | 10 | 4.6 | 4.3 | 5 | 1 | 0 | 1 | 9369 | 6071 | 187.374 |
| `chess-analyst` | 4.955 | 10 | 4.9 | 5 | 5 | 0 | 0 | 0 | 16056 | 7860 | 321.114 |
| `chess-critic` | 3.84 | 10 | 4.4 | 4.4 | 5 | 1 | 1 | 1 | 11407 | 2348 | 228.134 |
| `chess-vision` | 4.515 | 10 | 4.5 | 4.6 | 5 | 0 | 1 | 0 | 4686 | 757 | 93.728 |
| `chess-scribe` | 4.23 | 10 | 4.4 | 4.5 | 5 | 0 | 3 | 0 | 3407 | 5538 | 68.138 |
| `chess-researcher` | -0.62 | 10 | 2.4 | 2 | 5 | 7 | 0 | 4 | 2297 | 816 | 45.94 |

### `nemotron-3-super:cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3850 | 2070 | 77.004 |
| `chess-small` | 4.55 | 10 | 4 | 5 | 5 | 0 | 0 | 0 | 9046 | 6314 | 180.916 |
| `chess-large` | 4.82 | 10 | 5 | 5 | 3.8 | 0 | 0 | 0 | 53656 | 10922 | 1073.124 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 53427 | 17570 | 1068.53 |
| `chess-translator` | 1.38 | 10 | 3 | 3.2 | 5 | 4 | 6 | 0 | 2170 | 424 | 43.404 |
| `chess-coach` | 4.86 | 10 | 4.9 | 4.8 | 4.9 | 0 | 0 | 0 | 8916 | 6251 | 178.326 |
| `chess-analyst` | 4.94 | 10 | 4.9 | 5 | 4.9 | 0 | 0 | 0 | 15205 | 7687 | 304.09 |
| `chess-critic` | 3.784 | 10 | 4.4 | 4.3 | 4.89 | 1 | 1 | 1 | 13265 | 2610 | 265.294 |
| `chess-vision` | 3.37 | 10 | 4 | 4.3 | 5 | 2 | 0 | 1 | 4829 | 855 | 96.588 |
| `chess-scribe` | 4.185 | 10 | 4.3 | 4.5 | 5 | 0 | 3 | 0 | 3413 | 5510 | 68.258 |
| `chess-researcher` | -0.165 | 10 | 2.5 | 2.4 | 5 | 6 | 1 | 4 | 2683 | 863 | 53.658 |

### `nemotron-3-super:cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4655 | 1996 | 93.108 |
| `chess-small` | 4.55 | 10 | 4 | 5 | 5 | 0 | 0 | 0 | 6926 | 5976 | 138.512 |
| `chess-large` | 4.85 | 10 | 5 | 5 | 4 | 0 | 0 | 0 | 52004 | 11390 | 1040.082 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 6044 | 11108 | 120.872 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 2222 | 394 | 44.438 |
| `chess-coach` | 3.805 | 10 | 4.5 | 4.2 | 5 | 1 | 1 | 1 | 7325 | 6092 | 146.506 |
| `chess-analyst` | 4.02 | 10 | 4.4 | 4.6 | 5 | 1 | 0 | 1 | 15409 | 7564 | 308.188 |
| `chess-critic` | 3.525 | 10 | 4.5 | 4 | 5 | 1 | 3 | 1 | 14136 | 2355 | 282.728 |
| `chess-vision` | 4.655 | 10 | 4.5 | 4.7 | 5 | 0 | 0 | 0 | 9050 | 973 | 181.006 |
| `chess-scribe` | 4.185 | 10 | 4.3 | 4.5 | 5 | 0 | 3 | 0 | 4634 | 5575 | 92.672 |
| `chess-researcher` | -1.045 | 10 | 2.1 | 1.9 | 5 | 8 | 1 | 3 | 2785 | 817 | 55.706 |

### `nemotron-3-super:cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 4786 | 2007 | 95.712 |
| `chess-small` | 4.535 | 10 | 4 | 5 | 4.9 | 0 | 0 | 0 | 10469 | 6378 | 209.386 |
| `chess-large` | 4.835 | 10 | 5 | 5 | 3.9 | 0 | 0 | 0 | 60331 | 12805 | 1206.622 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 73067 | 17527 | 1461.348 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 2004 | 375 | 40.072 |
| `chess-coach` | 3.985 | 10 | 4.5 | 4.4 | 5 | 1 | 0 | 1 | 9215 | 6334 | 184.302 |
| `chess-analyst` | 4.895 | 10 | 4.8 | 5 | 4.9 | 0 | 0 | 0 | 17839 | 7636 | 356.772 |
| `chess-critic` | 3.93 | 10 | 4.6 | 4.4 | 5 | 1 | 1 | 1 | 13936 | 2394 | 278.728 |
| `chess-vision` | 4.555 | 10 | 4.5 | 4.7 | 5 | 0 | 1 | 0 | 6658 | 939 | 133.156 |
| `chess-scribe` | 4.185 | 10 | 4.3 | 4.5 | 5 | 0 | 3 | 0 | 3326 | 5489 | 66.528 |
| `chess-researcher` | -0.2 | 10 | 2.4 | 2.3 | 5 | 7 | 1 | 2 | 3301 | 822 | 66.022 |

### `qwen3-coder-next:cloud`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 1705 | 1556 | 51.135 |
| `chess-small` | -1.9 | 10 | 1 | 1 | 5 | 10 | 0 | 0 | 3440 | 5040 | 103.185 |
| `chess-large` | 0.315 | 10 | 2.2 | 2.2 | 3.63 | 4 | 3 | 2 | 38636 | 6793 | 1159.071 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 2676 | 9994 | 80.292 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 1326 | 150 | 39.789 |
| `chess-coach` | 0.634 | 10 | 2.6 | 2.6 | 4.83 | 4 | 1 | 4 | 8889 | 5301 | 266.658 |
| `chess-analyst` | 2.066 | 10 | 3 | 3 | 4.11 | 2 | 2 | 1 | 26054 | 6592 | 781.617 |
| `chess-critic` | 2.99 | 10 | 3.4 | 3.4 | 4.33 | 1 | 0 | 1 | 22115 | 1574 | 663.441 |
| `chess-vision` | 2.57 | 10 | 2.8 | 2.9 | 4.67 | 1 | 0 | 1 | 8519 | 527 | 255.573 |
| `chess-scribe` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 6672 | 5155 | 200.16 |
| `chess-researcher` | 1.14 | 10 | 2.4 | 2.4 | 5 | 3 | 0 | 3 | 3376 | 621 | 101.28 |

### `qwen3-coder:480b-cloud`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 2715 | 1533 | 81.441 |
| `chess-small` | -1.9 | 10 | 1 | 1 | 5 | 10 | 0 | 0 | 3406 | 4999 | 102.174 |
| `chess-large` | 3.31 | 10 | 3.4 | 3.4 | 4.8 | 0 | 3 | 0 | 10122 | 5499 | 303.654 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 3339 | 9972 | 100.161 |
| `chess-translator` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 2737 | 153 | 82.104 |
| `chess-coach` | 2.54 | 10 | 3.4 | 3.4 | 5 | 2 | 0 | 2 | 6236 | 5221 | 187.077 |
| `chess-analyst` | 2.69 | 10 | 3.2 | 3.2 | 4.8 | 1 | 4 | 0 | 11293 | 5941 | 338.787 |
| `chess-critic` | 2.835 | 10 | 3.1 | 3.1 | 5 | 1 | 0 | 1 | 4898 | 978 | 146.931 |
| `chess-vision` | 3.285 | 10 | 3 | 3 | 4.9 | 0 | 0 | 0 | 4449 | 249 | 133.455 |
| `chess-scribe` | 2.865 | 10 | 2.9 | 2.9 | 5 | 1 | 0 | 0 | 4974 | 5058 | 149.211 |
| `chess-researcher` | 3.115 | 10 | 2.9 | 2.9 | 5 | 0 | 1 | 0 | 5322 | 630 | 159.651 |

### `qwen3-next:80b-cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 27188 | 3778 | 543.764 |
| `chess-small` | 4.46 | 10 | 4.2 | 5 | 3.8 | 0 | 0 | 0 | 46360 | 11100 | 927.202 |
| `chess-large` | 2.074 | 10 | 3.8 | 3.8 | 3.29 | 3 | 0 | 3 | 81106 | 14443 | 1622.114 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 65511 | 19774 | 1310.214 |
| `chess-translator` | 1.035 | 10 | 3 | 3 | 4.9 | 5 | 5 | 0 | 19897 | 2708 | 397.93 |
| `chess-coach` | 3.821 | 10 | 4.4 | 4.6 | 3.67 | 1 | 0 | 1 | 45561 | 10935 | 911.22 |
| `chess-analyst` | 2.943 | 10 | 4 | 4.2 | 3.75 | 2 | 0 | 2 | 43623 | 10689 | 872.464 |
| `chess-critic` | 3.884 | 10 | 4.6 | 4.2 | 4.56 | 1 | 0 | 1 | 29865 | 4497 | 597.294 |
| `chess-vision` | 4.575 | 10 | 4.7 | 4.6 | 4.8 | 0 | 1 | 0 | 18735 | 2328 | 374.708 |
| `chess-scribe` | 4.095 | 10 | 4.2 | 4.5 | 4.7 | 0 | 3 | 0 | 23571 | 8037 | 471.414 |
| `chess-researcher` | 1.675 | 10 | 3.5 | 3 | 5 | 3 | 4 | 2 | 7944 | 1382 | 158.876 |

### `qwen3-next:80b-cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.
Status: fuer den naechsten Re-Test ausgewaehlt.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 29945 | 5642 | 598.908 |
| `chess-small` | 4.415 | 10 | 4.1 | 5 | 3.8 | 0 | 0 | 0 | 42438 | 10716 | 848.756 |
| `chess-large` | 2.92 | 10 | 4.2 | 4.2 | 3 | 2 | 0 | 2 | 67022 | 13720 | 1340.444 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 15946 | 11737 | 318.918 |
| `chess-translator` | 1.05 | 10 | 3 | 3 | 5 | 5 | 5 | 0 | 17482 | 2591 | 349.634 |
| `chess-coach` | 4.68 | 10 | 4.8 | 4.8 | 4 | 0 | 0 | 0 | 44169 | 10783 | 883.386 |
| `chess-analyst` | 2.925 | 10 | 4 | 4.2 | 3.63 | 2 | 0 | 2 | 42630 | 10824 | 852.6 |
| `chess-critic` | 4.6 | 10 | 5 | 4.4 | 4.6 | 0 | 1 | 0 | 30205 | 4704 | 604.1 |
| `chess-vision` | 4.11 | 10 | 4.5 | 4.5 | 4.9 | 1 | 1 | 0 | 19485 | 2387 | 389.694 |
| `chess-scribe` | 4.095 | 10 | 4.1 | 4.5 | 5 | 0 | 3 | 0 | 18208 | 7083 | 364.168 |
| `chess-researcher` | 0.97 | 10 | 3.2 | 2.7 | 5 | 4 | 3 | 3 | 8429 | 1471 | 168.574 |

### `qwen3.5:397b-cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 80802 | 3568 | 1616.044 |
| `chess-small` | -2.245 | 10 | 1 | 1 | 2.7 | 10 | 0 | 0 | 130253 | 8172 | 2605.068 |
| `chess-large` | 2.235 | 10 | 3.2 | 3.2 | 2.1 | 2 | 1 | 0 | 212174 | 8786 | 4243.47 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 70632 | 11612 | 1412.646 |
| `chess-translator` | 2.695 | 10 | 2.9 | 2.9 | 2.2 | 0 | 1 | 0 | 160683 | 2771 | 3213.668 |
| `chess-coach` | 3.535 | 10 | 3.7 | 3.7 | 2.6 | 0 | 0 | 0 | 127961 | 7413 | 2559.224 |
| `chess-analyst` | 1.465 | 10 | 3 | 3 | 2.1 | 4 | 0 | 0 | 168573 | 8525 | 3371.456 |
| `chess-critic` | 3.809 | 10 | 4.5 | 4.5 | 3.56 | 1 | 0 | 1 | 103052 | 3585 | 2061.042 |
| `chess-vision` | 3.225 | 10 | 4.1 | 4.2 | 4 | 2 | 0 | 1 | 78002 | 2637 | 1560.046 |
| `chess-scribe` | 4.27 | 10 | 4.5 | 4.8 | 3.5 | 0 | 2 | 0 | 179182 | 7723 | 3583.648 |
| `chess-researcher` | 0.835 | 10 | 3.4 | 2.8 | 3.57 | 5 | 0 | 3 | 90216 | 2629 | 1804.328 |

### `qwen3.5:397b-cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 34045 | 3319 | 680.902 |
| `chess-small` | -2.23 | 10 | 1 | 1 | 2.8 | 10 | 0 | 0 | 91197 | 8869 | 1823.942 |
| `chess-large` | 3.595 | 10 | 3.8 | 3.8 | 3.1 | 0 | 1 | 0 | 79273 | 9030 | 1585.46 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 103482 | 10980 | 2069.64 |
| `chess-translator` | 2.845 | 10 | 2.9 | 2.9 | 3.2 | 0 | 1 | 0 | 68066 | 2856 | 1361.322 |
| `chess-coach` | 3.91 | 10 | 4 | 4 | 3.4 | 0 | 0 | 0 | 62164 | 7725 | 1243.288 |
| `chess-analyst` | 2.441 | 10 | 3.5 | 3.5 | 2.44 | 2 | 0 | 1 | 108361 | 8399 | 2167.228 |
| `chess-critic` | 2.639 | 10 | 3.3 | 3.3 | 2.56 | 1 | 0 | 1 | 98811 | 3570 | 1976.226 |
| `chess-vision` | 3.015 | 10 | 3 | 3 | 3.1 | 0 | 0 | 0 | 86193 | 2487 | 1723.868 |
| `chess-scribe` | 2.94 | 10 | 3 | 3 | 2.6 | 0 | 0 | 0 | 115196 | 7437 | 2303.916 |
| `chess-researcher` | 1.579 | 10 | 2.6 | 2.6 | 3.13 | 2 | 0 | 2 | 71369 | 2534 | 1427.376 |

### `qwen3.5:397b-cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 41107 | 2985 | 822.14 |
| `chess-small` | -2.26 | 10 | 1 | 1 | 2.6 | 10 | 0 | 0 | 122483 | 8843 | 2449.65 |
| `chess-large` | 2.69 | 10 | 3.3 | 3.3 | 2.9 | 1 | 2 | 0 | 96357 | 8745 | 1927.146 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 22864 | 11261 | 457.282 |
| `chess-translator` | 2.875 | 10 | 2.9 | 2.9 | 3.4 | 0 | 1 | 0 | 57464 | 3014 | 1149.278 |
| `chess-coach` | 3.85 | 10 | 4 | 4 | 3 | 0 | 0 | 0 | 82528 | 7533 | 1650.562 |
| `chess-analyst` | 3.015 | 10 | 3.6 | 3.6 | 2.7 | 1 | 1 | 0 | 107551 | 8026 | 2151.018 |
| `chess-critic` | 2.825 | 10 | 3.5 | 3.5 | 2.67 | 1 | 0 | 1 | 112866 | 3611 | 2257.31 |
| `chess-vision` | 2.55 | 10 | 2.9 | 2.9 | 2.9 | 1 | 0 | 0 | 92594 | 2199 | 1851.874 |
| `chess-scribe` | 2.88 | 10 | 3 | 3 | 2.2 | 0 | 0 | 0 | 158049 | 8044 | 3160.988 |
| `chess-researcher` | 1.522 | 10 | 2.6 | 2.6 | 2.75 | 2 | 0 | 2 | 79111 | 2555 | 1582.228 |

### `qwen3.5:397b-cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -3.095 | 10 | 1.3 | 1.3 | 5 | 9 | 0 | 9 | 54469 | 2664 | 1089.384 |
| `chess-small` | -2.26 | 10 | 1 | 1 | 2.6 | 10 | 0 | 0 | 124138 | 9020 | 2482.752 |
| `chess-large` | 2.407 | 10 | 3.4 | 3.4 | 2.78 | 2 | 0 | 1 | 107985 | 8388 | 2159.7 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 55302 | 12763 | 1106.038 |
| `chess-translator` | 2.875 | 10 | 2.9 | 2.9 | 3.4 | 0 | 1 | 0 | 62956 | 3348 | 1259.11 |
| `chess-coach` | 3.765 | 10 | 3.9 | 3.9 | 3 | 0 | 0 | 0 | 90568 | 7845 | 1811.354 |
| `chess-analyst` | 2.795 | 10 | 3.6 | 3.6 | 2.9 | 2 | 0 | 0 | 86036 | 8092 | 1720.718 |
| `chess-critic` | 2.823 | 10 | 3.4 | 3.4 | 3.22 | 1 | 0 | 1 | 80272 | 3496 | 1605.43 |
| `chess-vision` | 2.296 | 10 | 2.8 | 2.8 | 3.11 | 1 | 0 | 1 | 64426 | 2554 | 1288.518 |
| `chess-scribe` | 3 | 10 | 3 | 3 | 3 | 0 | 0 | 0 | 68097 | 7483 | 1361.93 |
| `chess-researcher` | 1.655 | 10 | 2.6 | 2.6 | 3.63 | 2 | 0 | 2 | 48473 | 2141 | 969.46 |

### `qwen3.5:397b-cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 28157 | 2654 | 563.148 |
| `chess-small` | -2.215 | 10 | 1 | 1 | 2.9 | 10 | 0 | 0 | 96349 | 9806 | 1926.972 |
| `chess-large` | 3.295 | 10 | 3.6 | 3.6 | 2.9 | 0 | 2 | 0 | 92367 | 8525 | 1847.336 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 18374 | 10874 | 367.48 |
| `chess-translator` | 2.875 | 10 | 2.9 | 2.9 | 3.4 | 0 | 1 | 0 | 56484 | 2765 | 1129.688 |
| `chess-coach` | 2.944 | 10 | 3.6 | 3.6 | 2.89 | 1 | 0 | 1 | 95458 | 7657 | 1909.16 |
| `chess-analyst` | 2.827 | 10 | 3.6 | 3.6 | 2.78 | 1 | 1 | 1 | 106156 | 8652 | 2123.12 |
| `chess-critic` | 2.688 | 10 | 3.3 | 3.3 | 2.89 | 1 | 0 | 1 | 98220 | 3481 | 1964.402 |
| `chess-vision` | 1.878 | 10 | 2.7 | 2.7 | 3.22 | 2 | 0 | 1 | 63062 | 2468 | 1261.248 |
| `chess-scribe` | 3.015 | 10 | 3 | 3 | 3.1 | 0 | 0 | 0 | 83288 | 7501 | 1665.764 |
| `chess-researcher` | 2.346 | 10 | 2.8 | 2.8 | 3.44 | 1 | 0 | 1 | 47070 | 2174 | 941.408 |

### `qwen3.5:cloud:think-high`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 17457 | 2442 | 349.132 |
| `chess-small` | 4.55 | 10 | 4.5 | 5 | 3.5 | 0 | 0 | 0 | 71888 | 8701 | 1437.756 |
| `chess-large` | 3.865 | 10 | 4.5 | 4.6 | 3.67 | 1 | 0 | 1 | 81466 | 8971 | 1629.316 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 21106 | 11427 | 422.114 |
| `chess-translator` | 0.93 | 10 | 3 | 3 | 4.2 | 5 | 5 | 0 | 48074 | 3460 | 961.486 |
| `chess-coach` | 4.88 | 10 | 5 | 5 | 4.2 | 0 | 0 | 0 | 41205 | 7477 | 824.09 |
| `chess-analyst` | 3.025 | 10 | 4.1 | 4.2 | 4 | 2 | 0 | 2 | 63089 | 8358 | 1261.778 |
| `chess-critic` | 3.645 | 10 | 4.3 | 4.4 | 4 | 1 | 1 | 1 | 48796 | 3297 | 975.914 |
| `chess-vision` | 4.235 | 10 | 4.6 | 4.6 | 4.5 | 1 | 0 | 0 | 27692 | 2184 | 553.836 |
| `chess-scribe` | 4.575 | 10 | 4.7 | 4.9 | 4 | 0 | 1 | 0 | 42351 | 7544 | 847.02 |
| `chess-researcher` | 1.153 | 10 | 3.1 | 3.3 | 4.25 | 4 | 4 | 2 | 30776 | 2701 | 615.522 |

### `qwen3.5:cloud:think-low`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 20367 | 2745 | 407.346 |
| `chess-small` | 4.49 | 10 | 4.3 | 5 | 3.7 | 0 | 0 | 0 | 83950 | 8900 | 1678.994 |
| `chess-large` | 3.91 | 10 | 4.6 | 4.6 | 3.67 | 1 | 0 | 1 | 86061 | 8780 | 1721.224 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 29042 | 11615 | 580.846 |
| `chess-translator` | 0.93 | 10 | 3 | 3 | 4.2 | 5 | 5 | 0 | 42635 | 2717 | 852.692 |
| `chess-coach` | 4.85 | 10 | 5 | 5 | 4 | 0 | 0 | 0 | 69213 | 7726 | 1384.258 |
| `chess-analyst` | 3.899 | 10 | 4.5 | 4.6 | 3.89 | 1 | 0 | 1 | 66222 | 9034 | 1324.438 |
| `chess-critic` | 3.792 | 10 | 4.3 | 4.6 | 3.78 | 1 | 0 | 1 | 67574 | 3588 | 1351.486 |
| `chess-vision` | 3.724 | 10 | 4.2 | 4.5 | 4.56 | 1 | 1 | 1 | 32864 | 2355 | 657.286 |
| `chess-scribe` | 4.345 | 10 | 4.5 | 4.8 | 4 | 0 | 2 | 0 | 53187 | 7588 | 1063.742 |
| `chess-researcher` | 1.545 | 10 | 3.5 | 2.8 | 4.33 | 4 | 2 | 1 | 32283 | 2702 | 645.664 |

### `qwen3.5:cloud:think-medium`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 30780 | 3597 | 615.592 |
| `chess-small` | 4.61 | 10 | 4.4 | 5 | 4.2 | 0 | 0 | 0 | 52949 | 8759 | 1058.98 |
| `chess-large` | 4.775 | 10 | 4.9 | 5 | 3.8 | 0 | 0 | 0 | 61415 | 8261 | 1228.3 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 234643 | 12152 | 4692.866 |
| `chess-translator` | 0.615 | 10 | 3 | 2.8 | 4.3 | 6 | 4 | 0 | 39866 | 2698 | 797.326 |
| `chess-coach` | 4.88 | 10 | 5 | 5 | 4.2 | 0 | 0 | 0 | 56861 | 7440 | 1137.214 |
| `chess-analyst` | 3.948 | 10 | 4.5 | 4.6 | 4.22 | 1 | 0 | 1 | 45208 | 8476 | 904.164 |
| `chess-critic` | 3.584 | 10 | 4.4 | 4.3 | 3.56 | 1 | 1 | 1 | 68924 | 3508 | 1378.488 |
| `chess-vision` | 2.515 | 10 | 3.8 | 3.9 | 4.63 | 3 | 0 | 2 | 57462 | 2825 | 1149.23 |
| `chess-scribe` | 4.505 | 10 | 4.6 | 4.8 | 4.1 | 0 | 1 | 0 | 47488 | 7483 | 949.756 |
| `chess-researcher` | 1.885 | 10 | 3.6 | 3.1 | 4.5 | 3 | 2 | 2 | 34540 | 2740 | 690.804 |

### `qwen3.5:cloud:think-off`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 41475 | 2642 | 829.496 |
| `chess-small` | 4.415 | 10 | 4.4 | 5 | 2.9 | 0 | 0 | 0 | 134425 | 9235 | 2688.494 |
| `chess-large` | 4.715 | 10 | 5 | 5 | 3.1 | 0 | 0 | 0 | 130986 | 8717 | 2619.728 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 28897 | 11046 | 577.932 |
| `chess-translator` | 0.45 | 10 | 3 | 2.8 | 3.2 | 6 | 4 | 0 | 113284 | 4265 | 2265.68 |
| `chess-coach` | 4.76 | 10 | 5 | 5 | 3.4 | 0 | 0 | 0 | 104343 | 8019 | 2086.86 |
| `chess-analyst` | 4.73 | 10 | 4.9 | 5 | 3.5 | 0 | 0 | 0 | 118715 | 8338 | 2374.306 |
| `chess-critic` | 3.786 | 10 | 4.4 | 4.6 | 3.44 | 1 | 0 | 1 | 88304 | 3650 | 1766.07 |
| `chess-vision` | 4.605 | 10 | 4.7 | 4.9 | 4.2 | 0 | 1 | 0 | 49085 | 2255 | 981.702 |
| `chess-scribe` | 4.245 | 10 | 4.5 | 4.7 | 3.6 | 0 | 2 | 0 | 83573 | 7905 | 1671.464 |
| `chess-researcher` | 1.867 | 10 | 3.6 | 3.2 | 4.11 | 3 | 4 | 1 | 56812 | 2643 | 1136.248 |

### `qwen3.5:cloud:think-on`
Testdatum: 2026-05-28T21:49:20+00:00.
Run: `ptg-three-games-20260526T092135Z`.

| Rolle | Score | Feedbacks | Qualitaet | Aufgabe | Dauer | Major | Minor | Fehler | ms avg | Tokens avg | Usage units |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `chess-router` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 27874 | 2349 | 557.484 |
| `chess-small` | 4.505 | 10 | 4.3 | 5 | 3.8 | 0 | 0 | 0 | 66864 | 8730 | 1337.28 |
| `chess-large` | 4.7 | 10 | 4.9 | 5 | 3.3 | 0 | 0 | 0 | 98860 | 9187 | 1977.19 |
| `chess-task` | -4.65 | 10 | 1 | 1 |  | 10 | 0 | 10 | 104471 | 18157 | 2089.414 |
| `chess-translator` | 0.6 | 10 | 3 | 2.8 | 4.2 | 6 | 4 | 0 | 48044 | 2795 | 960.88 |
| `chess-coach` | 4.865 | 10 | 5 | 5 | 4.1 | 0 | 0 | 0 | 54987 | 7969 | 1099.736 |
| `chess-analyst` | 3.837 | 10 | 4.4 | 4.6 | 3.78 | 1 | 0 | 1 | 72063 | 8399 | 1441.254 |
| `chess-critic` | 3.865 | 10 | 4.5 | 4.6 | 3.67 | 1 | 0 | 1 | 63598 | 3725 | 1271.954 |
| `chess-vision` | 4.55 | 10 | 4.6 | 4.8 | 4.4 | 0 | 1 | 0 | 43404 | 2271 | 868.076 |
| `chess-scribe` | 4.375 | 10 | 4.5 | 4.8 | 4.2 | 0 | 2 | 0 | 53312 | 7636 | 1066.246 |
| `chess-researcher` | 2.075 | 10 | 3.7 | 3.4 | 5 | 4 | 1 | 1 | 21125 | 2275 | 422.496 |
