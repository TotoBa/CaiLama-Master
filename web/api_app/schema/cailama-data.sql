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
VALUES (1, 'cailama-data', '0.2.0')
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
