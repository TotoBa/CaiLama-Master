<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Controllers;

use CaiLama\WebApi\Auth\ApiTokenGuard;
use CaiLama\WebApi\Db\ConnectionFactory;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;
use PDO;

final class ConsoleKeyAdminController
{
    public function upsert(Request $request, array $config): Response
    {
        if (!ApiTokenGuard::hasAnyScope($request, $config, ['admin'])) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }
        $payload = $this->payload($request);
        if (!is_array($payload)) {
            return $this->error('invalid_json', 'JSON body is invalid.', 400);
        }

        $profileKey = $this->stringField($payload, 'profile_key', 80);
        $displayName = $this->stringField($payload, 'display_name', 190);
        $trainingName = $this->stringField($payload, 'training_name', 120);
        $label = $this->stringField($payload, 'label', 120);
        $keyPrefix = $this->stringField($payload, 'key_prefix', 32);
        $keyHash = $this->stringField($payload, 'key_hash', 96);
        $scopes = $payload['scopes'] ?? [];
        if (
            $profileKey === '' || $displayName === '' || $trainingName === '' || $label === ''
            || $keyPrefix === '' || !preg_match('/^sha256:[a-f0-9]{64}$/', $keyHash)
            || !is_array($scopes) || $scopes === []
        ) {
            return $this->error('invalid_payload', 'Console key payload is invalid.', 400);
        }
        $scopes = array_values(array_filter($scopes, 'is_string'));

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $pdo->beginTransaction();
            $profileId = $this->upsertProfile($pdo, $profileKey, $displayName, $trainingName);
            $keyId = $this->upsertKey($pdo, $profileId, $label, $keyPrefix, $keyHash, $scopes);
            $pdo->commit();
        } catch (\Throwable) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return $this->error('console_key_failed', 'Console key could not be saved.', 500);
        }

        return Response::json([
            'status' => 'ok',
            'profile_key' => $profileKey,
            'key_id' => $keyId,
            'key_prefix' => $keyPrefix,
        ]);
    }

    public function refuse(Request $request, array $config): Response
    {
        if (!ApiTokenGuard::hasAnyScope($request, $config, ['admin'])) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }
        $payload = $this->payload($request);
        if (!is_array($payload)) {
            return $this->error('invalid_json', 'JSON body is invalid.', 400);
        }
        $keyPrefix = $this->stringField($payload, 'key_prefix', 32);
        if ($keyPrefix === '') {
            return $this->error('invalid_payload', 'key_prefix is required.', 400);
        }

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $stmt = $pdo->prepare(
                "UPDATE cailama_console_api_keys
                 SET status = 'refused',
                     refused_at = CURRENT_TIMESTAMP
                 WHERE key_prefix = :key_prefix"
            );
            $stmt->execute(['key_prefix' => $keyPrefix]);
        } catch (\Throwable) {
            return $this->error('console_key_refuse_failed', 'Console key could not be refused.', 500);
        }

        return Response::json([
            'status' => 'ok',
            'refused' => $stmt->rowCount(),
        ]);
    }

    private function payload(Request $request): ?array
    {
        if (trim($request->body) === '') {
            return null;
        }
        try {
            $payload = json_decode($request->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
        return is_array($payload) ? $payload : null;
    }

    private function upsertProfile(PDO $pdo, string $profileKey, string $displayName, string $trainingName): int
    {
        $stmt = $pdo->prepare(
            "INSERT INTO cailama_player_profiles (profile_key, display_name, training_name, status)
             VALUES (:profile_key, :display_name, :training_name, 'active')
             ON DUPLICATE KEY UPDATE
                 display_name = VALUES(display_name),
                 training_name = VALUES(training_name),
                 status = 'active'"
        );
        $stmt->execute([
            'profile_key' => $profileKey,
            'display_name' => $displayName,
            'training_name' => $trainingName,
        ]);

        $select = $pdo->prepare('SELECT id FROM cailama_player_profiles WHERE profile_key = :profile_key LIMIT 1');
        $select->execute(['profile_key' => $profileKey]);
        return (int) $select->fetchColumn();
    }

    private function upsertKey(
        PDO $pdo,
        int $profileId,
        string $label,
        string $keyPrefix,
        string $keyHash,
        array $scopes,
    ): int {
        $stmt = $pdo->prepare(
            "INSERT INTO cailama_console_api_keys (profile_id, label, key_prefix, key_hash, scopes, status)
             VALUES (:profile_id, :label, :key_prefix, :key_hash, :scopes, 'active')
             ON DUPLICATE KEY UPDATE
                 profile_id = VALUES(profile_id),
                 label = VALUES(label),
                 scopes = VALUES(scopes),
                 status = 'active',
                 refused_at = NULL"
        );
        $stmt->execute([
            'profile_id' => $profileId,
            'label' => $label,
            'key_prefix' => $keyPrefix,
            'key_hash' => $keyHash,
            'scopes' => json_encode(array_values($scopes), JSON_UNESCAPED_SLASHES),
        ]);

        $select = $pdo->prepare('SELECT id FROM cailama_console_api_keys WHERE key_prefix = :key_prefix LIMIT 1');
        $select->execute(['key_prefix' => $keyPrefix]);
        return (int) $select->fetchColumn();
    }

    private function stringField(array $payload, string $field, int $maxLength): string
    {
        $value = $payload[$field] ?? '';
        if (!is_string($value)) {
            return '';
        }
        return substr(trim($value), 0, $maxLength);
    }

    private function error(string $code, string $message, int $status): Response
    {
        return Response::json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }
}
