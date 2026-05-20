-- Minimal placeholder for the separate CaiLama application database.
-- The provider login database and the CaiLama data database are intentionally
-- separate and are wired through different DSNs in config.local.php.

CREATE TABLE IF NOT EXISTS cailama_schema_meta (
    id TINYINT UNSIGNED PRIMARY KEY,
    schema_name VARCHAR(80) NOT NULL,
    schema_version VARCHAR(40) NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cailama_schema_meta (id, schema_name, schema_version)
VALUES (1, 'cailama-data', '0.1.0')
ON DUPLICATE KEY UPDATE schema_version = VALUES(schema_version);
