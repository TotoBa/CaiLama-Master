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

    public function authenticate(string $login, string $password): ?array
    {
        if (!($this->config['enabled'] ?? false)) {
            throw new RuntimeException('Login is not configured.');
        }

        $login = strtolower(trim($login));
        if ($login === '' || strlen($login) > 190 || !preg_match('/^[a-z0-9._@-]+$/', $login) || $password === '') {
            return null;
        }

        $table = self::identifier((string) ($this->config['users_table'] ?? 'web_users'));
        $columns = [
            'id' => self::identifier((string) ($this->config['id_column'] ?? 'id')),
            'login' => self::identifier((string) ($this->config['login_column'] ?? 'login_name')),
            'email' => self::identifier((string) ($this->config['email_column'] ?? 'email')),
            'display_name' => self::identifier((string) ($this->config['display_name_column'] ?? 'display_name')),
            'password_hash' => self::identifier((string) ($this->config['password_hash_column'] ?? 'password_hash')),
            'status' => self::identifier((string) ($this->config['status_column'] ?? 'status')),
        ];

        $sql = sprintf(
            'SELECT %s AS id, %s AS login_name, %s AS email, %s AS display_name, %s AS password_hash, %s AS status FROM %s WHERE %s = :login LIMIT 1',
            $columns['id'],
            $columns['login'],
            $columns['email'],
            $columns['display_name'],
            $columns['password_hash'],
            $columns['status'],
            $table,
            $columns['login'],
        );

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['login' => $login]);
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
            'login_name' => (string) ($row['login_name'] ?? ''),
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
