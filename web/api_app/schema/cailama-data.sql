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
VALUES (1, 'cailama-data', '0.8.1')
ON DUPLICATE KEY UPDATE schema_version = VALUES(schema_version);

-- Website user authentication table (formerly in a separate auth database).
-- Build a real password hash on the target system when creating the first user:
-- php -r 'echo password_hash("replace-this-password", PASSWORD_DEFAULT), PHP_EOL;'
CREATE TABLE IF NOT EXISTS web_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login_name VARCHAR(190) NOT NULL UNIQUE,
    email VARCHAR(190) NULL UNIQUE,
    display_name VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'locked') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example first user (uncomment and replace hash on target system):
-- INSERT INTO web_users (login_name, email, display_name, password_hash, status)
-- VALUES ('testuser', NULL, 'CaiLama Admin', '<replace-with-password-hash>', 'active');

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
    observation_id BIGINT UNSIGNED NULL,
    case_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    run_key VARCHAR(120) NOT NULL DEFAULT '',
    model_label VARCHAR(120) NOT NULL,
    duration_ms INT UNSIGNED NULL,
    input_tokens INT UNSIGNED NULL,
    thinking_tokens INT UNSIGNED NULL,
    output_tokens INT UNSIGNED NULL,
    total_tokens INT UNSIGNED NULL,
    model_usage_level VARCHAR(32) NOT NULL DEFAULT '',
    model_usage_weight TINYINT UNSIGNED NULL,
    weighted_token_units BIGINT UNSIGNED NULL,
    estimated_usage_units DECIMAL(18,3) NULL,
    quality_score TINYINT UNSIGNED NOT NULL,
    task_solution_score TINYINT UNSIGNED NOT NULL,
    duration_score TINYINT UNSIGNED NULL,
    logic_error_level ENUM('none', 'minor', 'major', 'unknown') NOT NULL DEFAULT 'unknown',
    preferred_option ENUM('a', 'b', 'tie', 'not_applicable') NOT NULL DEFAULT 'not_applicable',
    translation_score TINYINT UNSIGNED NULL,
    feedback_text TEXT NULL,
    improvement_note TEXT NULL,
    translation_note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cailama_model_feedback_observation (observation_id),
    INDEX idx_cailama_model_feedback_run_case (run_key, case_id),
    INDEX idx_cailama_model_feedback_case_created (case_id, created_at),
    INDEX idx_cailama_model_feedback_model (model_label),
    CONSTRAINT fk_cailama_model_feedback_case
        FOREIGN KEY (case_id) REFERENCES cailama_model_benchmark_cases (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cailama_model_benchmark_observations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id BIGINT UNSIGNED NOT NULL,
    run_key VARCHAR(120) NOT NULL,
    model_label VARCHAR(120) NOT NULL,
    duration_ms INT UNSIGNED NULL,
    input_tokens INT UNSIGNED NULL,
    thinking_tokens INT UNSIGNED NULL,
    output_tokens INT UNSIGNED NULL,
    total_tokens INT UNSIGNED NULL,
    model_usage_level VARCHAR(32) NOT NULL DEFAULT '',
    model_usage_weight TINYINT UNSIGNED NULL,
    weighted_token_units BIGINT UNSIGNED NULL,
    estimated_usage_units DECIMAL(18,3) NULL,
    artifact_ref VARCHAR(190) NOT NULL DEFAULT '',
    position_fen VARCHAR(120) NOT NULL DEFAULT '',
    side_to_move VARCHAR(8) NOT NULL DEFAULT '',
    position_label VARCHAR(190) NOT NULL DEFAULT '',
    task_prompt_excerpt TEXT NULL,
    expected_output_type VARCHAR(80) NOT NULL DEFAULT '',
    candidate_moves_excerpt TEXT NULL,
    error_status VARCHAR(40) NOT NULL DEFAULT '',
    error_message VARCHAR(500) NOT NULL DEFAULT '',
    output_excerpt TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_cailama_benchmark_observation (case_id, run_key, model_label, artifact_ref),
    INDEX idx_cailama_benchmark_observations_created (created_at),
    INDEX idx_cailama_benchmark_observations_model (model_label),
    CONSTRAINT fk_cailama_model_benchmark_observation_case
        FOREIGN KEY (case_id) REFERENCES cailama_model_benchmark_cases (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE cailama_model_benchmark_observations
    ADD COLUMN IF NOT EXISTS total_tokens INT UNSIGNED NULL AFTER output_tokens,
    ADD COLUMN IF NOT EXISTS model_usage_level VARCHAR(32) NOT NULL DEFAULT '' AFTER total_tokens,
    ADD COLUMN IF NOT EXISTS model_usage_weight TINYINT UNSIGNED NULL AFTER model_usage_level,
    ADD COLUMN IF NOT EXISTS weighted_token_units BIGINT UNSIGNED NULL AFTER model_usage_weight,
    ADD COLUMN IF NOT EXISTS estimated_usage_units DECIMAL(18,3) NULL AFTER weighted_token_units,
    ADD COLUMN IF NOT EXISTS position_fen VARCHAR(120) NOT NULL DEFAULT '' AFTER artifact_ref,
    ADD COLUMN IF NOT EXISTS side_to_move VARCHAR(8) NOT NULL DEFAULT '' AFTER position_fen,
    ADD COLUMN IF NOT EXISTS position_label VARCHAR(190) NOT NULL DEFAULT '' AFTER side_to_move,
    ADD COLUMN IF NOT EXISTS task_prompt_excerpt TEXT NULL AFTER position_label,
    ADD COLUMN IF NOT EXISTS expected_output_type VARCHAR(80) NOT NULL DEFAULT '' AFTER task_prompt_excerpt,
    ADD COLUMN IF NOT EXISTS candidate_moves_excerpt TEXT NULL AFTER expected_output_type,
    ADD COLUMN IF NOT EXISTS error_status VARCHAR(40) NOT NULL DEFAULT '' AFTER candidate_moves_excerpt,
    ADD COLUMN IF NOT EXISTS error_message VARCHAR(500) NOT NULL DEFAULT '' AFTER error_status;

ALTER TABLE web_users
    ADD COLUMN IF NOT EXISTS login_name VARCHAR(190) NOT NULL DEFAULT '' AFTER id;

UPDATE web_users
SET login_name = email
WHERE login_name = '' AND email IS NOT NULL AND email <> '';

ALTER TABLE web_users
    MODIFY COLUMN email VARCHAR(190) NULL;

ALTER TABLE cailama_model_feedback
    ADD COLUMN IF NOT EXISTS observation_id BIGINT UNSIGNED NULL AFTER id,
    ADD COLUMN IF NOT EXISTS run_key VARCHAR(120) NOT NULL DEFAULT '' AFTER user_id,
    ADD COLUMN IF NOT EXISTS total_tokens INT UNSIGNED NULL AFTER output_tokens,
    ADD COLUMN IF NOT EXISTS model_usage_level VARCHAR(32) NOT NULL DEFAULT '' AFTER total_tokens,
    ADD COLUMN IF NOT EXISTS model_usage_weight TINYINT UNSIGNED NULL AFTER model_usage_level,
    ADD COLUMN IF NOT EXISTS weighted_token_units BIGINT UNSIGNED NULL AFTER model_usage_weight,
    ADD COLUMN IF NOT EXISTS estimated_usage_units DECIMAL(18,3) NULL AFTER weighted_token_units,
    ADD COLUMN IF NOT EXISTS duration_score TINYINT UNSIGNED NULL AFTER task_solution_score,
    ADD COLUMN IF NOT EXISTS translation_score TINYINT UNSIGNED NULL AFTER preferred_option,
    ADD COLUMN IF NOT EXISTS translation_note TEXT NULL AFTER improvement_note;

INSERT INTO cailama_model_benchmark_cases
    (case_key, area, role_name, model_a, model_b, task_label, task_summary, quality_question, status)
VALUES
    (
        'ptg-three-games',
        'pipeline',
        'ptg-three-games',
        'kimi-k2.6:cloud',
        'gemma4:31b-cloud',
        'PTG-Drei-Spiele-Benchmark',
        'Modell klassifiziert alle Zuege der drei freigegebenen PTG-Baseline-Spiele und analysiert die priorisierten Schluesselstellungen tief.',
        'Wie gut sind Klassifikation, Trainingsfragen und Analysequalitaet fuer diese drei Spiele?',
        'active'
    ),
    (
        'ptg-three-games-classify',
        'pipeline',
        'chess-small',
        'gemma4:31b-cloud',
        'deepseek-v4-flash:cloud',
        'PTG-Zugklassifikation',
        'Modell klassifiziert alle Zuege der drei freigegebenen PTG-Baseline-Spiele und muss stabile, weiterverarbeitbare Klassifikationsdaten liefern.',
        'Wie korrekt, stabil und pipeline-tauglich ist die Zugklassifikation?',
        'active'
    ),
    (
        'ptg-three-games-analysis',
        'pipeline',
        'chess-analyst',
        'qwen3.5:397b-cloud',
        'kimi-k2.6:cloud',
        'PTG-Schluesselstellungsanalyse',
        'Modell analysiert die priorisierten Schluesselstellungen der drei freigegebenen PTG-Baseline-Spiele mit Engine- und BoardTruth-Kontext.',
        'Wie gut sind Analysequalitaet, Engine-Grounding, Variantenlogik und Trainingsnutzen?',
        'active'
    ),
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
