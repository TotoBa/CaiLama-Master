<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Auth;

use PDO;
use RuntimeException;

final class AuthService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly array $config,
    ) {
    }

    public function authenticate(string $email, string $password): ?array
    {
        if (!($this->config['enabled'] ?? false)) {
            throw new RuntimeException('Login is not configured.');
        }

        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || $password === '') {
            return null;
        }

        $table = self::identifier((string) ($this->config['users_table'] ?? 'web_users'));
        $columns = [
            'id' => self::identifier((string) ($this->config['id_column'] ?? 'id')),
            'email' => self::identifier((string) ($this->config['email_column'] ?? 'email')),
            'display_name' => self::identifier((string) ($this->config['display_name_column'] ?? 'display_name')),
            'password_hash' => self::identifier((string) ($this->config['password_hash_column'] ?? 'password_hash')),
            'status' => self::identifier((string) ($this->config['status_column'] ?? 'status')),
        ];

        $sql = sprintf(
            'SELECT %s AS id, %s AS email, %s AS display_name, %s AS password_hash, %s AS status FROM %s WHERE %s = :email LIMIT 1',
            $columns['id'],
            $columns['email'],
            $columns['display_name'],
            $columns['password_hash'],
            $columns['status'],
            $table,
            $columns['email'],
        );

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['email' => $email]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            return null;
        }

        $activeStatus = (string) ($this->config['active_status'] ?? 'active');
        if ((string) ($row['status'] ?? '') !== $activeStatus) {
            return null;
        }

        $hash = (string) ($row['password_hash'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return null;
        }

        return [
            'id' => (string) ($row['id'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'display_name' => (string) ($row['display_name'] ?? ''),
        ];
    }

    private static function identifier(string $value): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            throw new RuntimeException('Invalid authentication configuration.');
        }
        return '`' . $value . '`';
    }
}
