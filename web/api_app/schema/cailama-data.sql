-- CaiLama application database schema.
-- Contains application metadata and website user authentication tables.
-- The login data lives in the same database because IONOS shared hosting
-- only allows connecting to a single database per PHP process.

CREATE TABLE IF NOT EXISTS cailama_schema_meta (
    id TINYINT UNSIGNED PRIMARY KEY,
    schema_name VARCHAR(80) NOT NULL,
    schema_version VARCHAR(40) NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cailama_schema_meta (id, schema_name, schema_version)
VALUES (1, 'cailama-data', '0.3.0')
ON DUPLICATE KEY UPDATE schema_version = VALUES(schema_version);

-- Website user authentication table (formerly in a separate auth database).
-- Build a real password hash on the target system when creating the first user:
-- php -r 'echo password_hash("replace-this-password", PASSWORD_DEFAULT), PHP_EOL;'
CREATE TABLE IF NOT EXISTS web_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    display_name VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'locked') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example first user (uncomment and replace hash on target system):
-- INSERT INTO web_users (email, display_name, password_hash, status)
-- VALUES ('sample@example.invalid', 'CaiLama Admin', '<replace-with-password-hash>', 'active');

-- Model-role benchmark cases and reusable human feedback.
-- Raw prompts, full responses and private game data do not belong here.
CREATE TABLE IF NOT EXISTS cailama_model_benchmark_cases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_key VARCHAR(120) NOT NULL UNIQUE,
    area ENUM('coding', 'chess_role', 'pipeline', 'search_rag') NOT NULL,
    role_name VARCHAR(80) NOT NULL,
    model_a VARCHAR(120) NULL,
    model_b VARCHAR(120) NULL,
    task_label VARCHAR(190) NOT NULL,
    task_summary TEXT NOT NULL,
    quality_question VARCHAR(255) NOT NULL,
    status ENUM('hypothesis', 'active', 'archived') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cailama_model_benchmark_cases_status (status, area, role_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cailama_model_feedback (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    model_label VARCHAR(120) NOT NULL,
    duration_ms INT UNSIGNED NULL,
    input_tokens INT UNSIGNED NULL,
    thinking_tokens INT UNSIGNED NULL,
    output_tokens INT UNSIGNED NULL,
    quality_score TINYINT UNSIGNED NOT NULL,
    task_solution_score TINYINT UNSIGNED NOT NULL,
    logic_error_level ENUM('none', 'minor', 'major', 'unknown') NOT NULL DEFAULT 'unknown',
    preferred_option ENUM('a', 'b', 'tie', 'not_applicable') NOT NULL DEFAULT 'not_applicable',
    feedback_text TEXT NULL,
    improvement_note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cailama_model_feedback_case_created (case_id, created_at),
    INDEX idx_cailama_model_feedback_model (model_label),
    CONSTRAINT fk_cailama_model_feedback_case
        FOREIGN KEY (case_id) REFERENCES cailama_model_benchmark_cases (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cailama_model_benchmark_cases
    (case_key, area, role_name, model_a, model_b, task_label, task_summary, quality_question, status)
VALUES
    (
        'coding-agent-todo-work',
        'coding',
        'coding.default',
        'kimi-k2.6:cloud',
        'qwen3-coder:480b-cloud',
        'Coding-Agent: TODO sauber abarbeiten',
        'Agent liest AGENTS/TODO, setzt eine kleine Änderung um, aktualisiert Doku/TODO, testet und hält den Arbeitsbaum sauber.',
        'Wie gut wurde die konkrete Repo-Aufgabe gelöst, inklusive Regelbefolgung, Tests und Doku-Sync?',
        'active'
    ),
    (
        'chess-small-move-classification',
        'chess_role',
        'chess-small',
        'gemma4:31b-cloud',
        'deepseek-v4-flash:cloud',
        'Zugklassifikation und JSON-Disziplin',
        'Modell klassifiziert alle Züge, erzeugt stabile Labels und bleibt bei gültigen, weiterverarbeitbaren JSON-Artefakten.',
        'Wie korrekt und weiterverarbeitbar ist die Klassifikation?',
        'active'
    ),
    (
        'chess-coach-didactic-answer',
        'chess_role',
        'chess-coach',
        'gemma4:31b-cloud',
        'kimi-k2.6:cloud',
        'Coach-Antwort zu Trainingsstellung',
        'Modell erklärt eine gewichtete Trainingsposition auf Deutsch, spoilerarm, passend zur Spielstärke und mit klarer Übungsfrage.',
        'Wie hilfreich, präzise und didaktisch passend ist die Coach-Antwort?',
        'active'
    ),
    (
        'chess-analyst-engine-grounding',
        'chess_role',
        'chess-analyst',
        'qwen3.5:397b-cloud',
        'kimi-k2.6:cloud',
        'Engine-grounded Analyse',
        'Modell nutzt Engine- und BoardTruth-Kontext, trennt Bewertung von Vermutung und vermeidet erfundene Varianten.',
        'Wie gut ist die Analyse gegen Engine-/Brettwahrheit geerdet?',
        'active'
    ),
    (
        'chess-researcher-rag-provenance',
        'search_rag',
        'chess-researcher',
        'kimi-k2.6:cloud',
        'deepseek-v4-flash:cloud',
        'RAG-Antwort mit Quellenprovenienz',
        'Modell verdichtet Search-Kontext mit sichtbaren Quellen, Verwendet-für-Hinweis und Unsicherheit.',
        'Wie gut sind Quellen, Unsicherheit und Antwortnutzen nachvollziehbar?',
        'active'
    ),
    (
        'chess-vision-no-guessed-fen',
        'chess_role',
        'chess-vision',
        'gemma4:31b-cloud',
        'qwen3.5:397b-cloud',
        'Vision/OCR ohne geratene FEN',
        'Modell arbeitet mit Diagramm-/OCR-Kontext, markiert Unsicherheit und gibt keine FEN aus, wenn die Stellung nicht belastbar erkannt ist.',
        'Wie zuverlässig vermeidet das Modell geratene FENs und falsche Sicherheit?',
        'active'
    )
ON DUPLICATE KEY UPDATE
    area = VALUES(area),
    role_name = VALUES(role_name),
    model_a = VALUES(model_a),
    model_b = VALUES(model_b),
    task_label = VALUES(task_label),
    task_summary = VALUES(task_summary),
    quality_question = VALUES(quality_question),
    status = VALUES(status);
