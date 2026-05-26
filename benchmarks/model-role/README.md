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

Der Runner nutzt diese Aufgaben nicht als parallele Promptstrecke: Router-
Faelle gehen durch den echten Router-Prompt mit aktueller Toolliste,
Nicht-Router-Faelle durch den CaiLama-`PromptBuilder`. RAG-/Researcher-Faelle
tragen eine `extra_context.rag_query`; CaiLama holt den Kontext vor dem Prompt
ueber das echte `search_rag`-Tool, damit Benchmark und interaktive Konsole
dieselben Informationen sehen. `query` ist die eigentliche Modellfrage und
wird im Website-Feedback separat angezeigt; der voll konstruierte User-Prompt
bleibt zusaetzlich sichtbar.
