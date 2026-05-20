<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Db;

use PDO;
use RuntimeException;

final class ConnectionFactory
{
    public static function fromConfig(array $config, string $name): PDO
    {
        $database = $config['databases'][$name] ?? null;
        if (!is_array($database) || !($database['enabled'] ?? false)) {
            throw new RuntimeException('Database is not configured.');
        }

        $dsn = trim((string) ($database['dsn'] ?? ''));
        $user = (string) ($database['user'] ?? '');
        $password = (string) ($database['password'] ?? '');

        if ($dsn === '' || $user === '') {
            throw new RuntimeException('Database is not configured.');
        }

        $options = $database['options'] ?? [];
        if (!is_array($options)) {
            $options = [];
        }

        $options += [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $user, $password, $options);
    }
}
