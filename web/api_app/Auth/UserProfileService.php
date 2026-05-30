<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Auth;

use PDO;

final class UserProfileService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly array $authConfig = [],
    ) {
    }

    /**
     * @param array{id?: string, login_name?: string, email?: string, display_name?: string} $user
     * @return array<string, string|null>
     */
    public function attachProfile(array $user): array
    {
        $userId = (string) ($user['id'] ?? '');
        if ($userId === '') {
            return $this->withEmptyProfile($user);
        }

        $profile = $this->profileForUserId($userId);
        if ($profile === null) {
            return $this->withEmptyProfile($user);
        }

        return array_merge($user, $profile);
    }

    /**
     * @return array<string, string|null>|null
     */
    public function profileForUserId(string $userId): ?array
    {
        if ($userId === '' || !ctype_digit($userId)) {
            return null;
        }

        $usersTable = $this->identifier((string) ($this->authConfig['users_table'] ?? 'web_users'));
        $idColumn = $this->identifier((string) ($this->authConfig['id_column'] ?? 'id'));

        $sql = sprintf(
            'SELECT p.id AS player_profile_id,
                    p.profile_key,
                    p.display_name AS player_display_name,
                    p.training_name,
                    p.status AS player_profile_status
             FROM %s w
             LEFT JOIN cailama_player_profiles p
               ON p.id = w.player_profile_id
              AND p.status = \'active\'
             WHERE w.%s = :user_id
             LIMIT 1',
            $usersTable,
            $idColumn,
        );

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['user_id' => $userId]);
        $row = $statement->fetch();

        if (!is_array($row) || ($row['player_profile_id'] ?? null) === null) {
            return null;
        }

        return [
            'player_profile_id' => (string) $row['player_profile_id'],
            'profile_key' => (string) ($row['profile_key'] ?? ''),
            'player_display_name' => (string) ($row['player_display_name'] ?? ''),
            'training_name' => (string) ($row['training_name'] ?? ''),
            'player_profile_status' => (string) ($row['player_profile_status'] ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function withEmptyProfile(array $user): array
    {
        return array_merge($user, [
            'player_profile_id' => null,
            'profile_key' => null,
            'player_display_name' => null,
            'training_name' => null,
            'player_profile_status' => null,
        ]);
    }

    private function identifier(string $value): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            throw new \RuntimeException('Invalid authentication configuration.');
        }
        return '`' . $value . '`';
    }
}
