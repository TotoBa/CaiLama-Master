# Modellrollen-Benchmark

Dieser Ordner enthaelt den versionierten Aufgaben- und Prompt-Vertrag fuer den
CaiLama-Modellrollen-Benchmark.

- `tasks.json`: editierbarer Aufgabenkatalog. Aktuell 10 Aufgaben je Rolle.
- `system_prompts/`: Rollen-Systemprompts, die beim Runtime-Deploy nach
  `config/system_prompt.<rolle>.md` kopiert werden.
- `prompt_templates/role_task.md`: gemeinsamer Prompt-Rahmen fuer einzelne
  Rollenaufgaben.

Die Aufgaben duerfen keine Secrets, lokalen Pfade oder privaten Roh-PGNs
enthalten. Erwartete Tool-Aufrufe stehen im Feld `expected_tools`; fehlende
erwartete Tools werden im Runner automatisch als Strukturfehler gewertet.
Unerwartete, aber formal gueltige Tool-Aufrufe bleiben offen fuer menschliches
Blindfeedback.
