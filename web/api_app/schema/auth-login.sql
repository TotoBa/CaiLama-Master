-- CaiLama website login schema for the provider-side auth database.
-- Replace sample values before using this on a reachable system.

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

-- Build a real password hash on the target system:
-- php -r 'echo password_hash("replace-this-password", PASSWORD_DEFAULT), PHP_EOL;'
--
-- INSERT INTO web_users (email, display_name, password_hash, status)
-- VALUES ('sample@example.invalid', 'CaiLama Admin', '<replace-with-password-hash>', 'active');
