<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Auth;

use CaiLama\WebApi\Http\Request;
use PDO;

final class ConsoleKeyGuard
{
    public static function authenticate(Request $request, PDO $pdo, string $scope): ?array
    {
        $token = self::requestToken($request);
        if ($token === '') {
            return null;
        }

        $hash = 'sha256:' . hash('sha256', $token);
        $stmt = $pdo->prepare(
            "SELECT
                k.id AS key_id,
                k.profile_id,
                k.scopes,
                p.profile_key,
                p.display_name,
                p.training_name
             FROM cailama_console_api_keys k
             JOIN cailama_player_profiles p ON p.id = k.profile_id
             WHERE k.key_hash = :key_hash
               AND k.status = 'active'
               AND p.status = 'active'
             LIMIT 1"
        );
        $stmt->execute(['key_hash' => $hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return null;
        }

        $scopes = self::parseScopes((string) ($row['scopes'] ?? ''));
        if (!in_array($scope, $scopes, true) && !in_array('console:all', $scopes, true)) {
            return null;
        }

        $update = $pdo->prepare(
            'UPDATE cailama_console_api_keys SET last_used_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $update->execute(['id' => (int) $row['key_id']]);

        return [
            'key_id' => (int) $row['key_id'],
            'profile_id' => (int) $row['profile_id'],
            'profile_key' => (string) $row['profile_key'],
            'display_name' => (string) $row['display_name'],
            'training_name' => (string) $row['training_name'],
            'scopes' => $scopes,
        ];
    }

    private static function requestToken(Request $request): string
    {
        $header = $request->headers['authorization'] ?? '';
        if (is_string($header) && preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
            return trim($matches[1]);
        }
        $consoleKey = $request->headers['x-cailama-console-key'] ?? '';
        return is_string($consoleKey) ? trim($consoleKey) : '';
    }

    private static function parseScopes(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, 'is_string'));
        }
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
