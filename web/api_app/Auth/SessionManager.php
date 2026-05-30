<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Auth;

final class SessionManager
{
    public function __construct(private readonly array $config)
    {
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = $this->config['cookie_secure'] ?? 'auto';
        if ($secure === 'auto') {
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        }

        session_name((string) ($this->config['name'] ?? 'cailama_session'));
        session_set_cookie_params([
            'lifetime' => (int) ($this->config['cookie_lifetime'] ?? 0),
            'path' => '/',
            'secure' => (bool) $secure,
            'httponly' => true,
            'samesite' => (string) ($this->config['cookie_samesite'] ?? 'Lax'),
        ]);

        session_start();
        $this->enforceIdleTimeout();
    }

    public function currentUser(): ?array
    {
        $user = $_SESSION['user'] ?? null;
        return is_array($user) ? $user : null;
    }

    public function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (string) ($user['id'] ?? ''),
            'login_name' => (string) ($user['login_name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'display_name' => (string) ($user['display_name'] ?? ''),
            'player_profile_id' => isset($user['player_profile_id']) ? (string) $user['player_profile_id'] : null,
            'profile_key' => isset($user['profile_key']) ? (string) $user['profile_key'] : null,
            'player_display_name' => isset($user['player_display_name']) ? (string) $user['player_display_name'] : null,
            'training_name' => isset($user['training_name']) ? (string) $user['training_name'] : null,
        ];
        $_SESSION['auth_time'] = time();
        $_SESSION['last_seen'] = time();
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            $cookie = [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => true,
                'samesite' => (string) ($params['samesite'] ?? 'Lax'),
            ];

            if (!empty($params['domain'])) {
                $cookie['domain'] = (string) $params['domain'];
            }

            setcookie(session_name(), '', $cookie);
        }

        session_destroy();
    }

    public function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrf(?string $token): bool
    {
        $knownToken = $_SESSION['csrf_token'] ?? '';
        return is_string($token) && is_string($knownToken) && hash_equals($knownToken, $token);
    }

    public function canAttemptLogin(int $maxAttempts, int $windowSeconds): bool
    {
        $attempts = $this->loginAttempts($windowSeconds);
        return count($attempts) < max(1, $maxAttempts);
    }

    public function recordLoginFailure(int $windowSeconds): void
    {
        $attempts = $this->loginAttempts($windowSeconds);
        $attempts[] = time();
        $_SESSION['login_failures'] = $attempts;
    }

    public function clearLoginFailures(): void
    {
        unset($_SESSION['login_failures']);
    }

    private function enforceIdleTimeout(): void
    {
        $timeout = (int) ($this->config['idle_timeout_seconds'] ?? 1800);
        if ($timeout <= 0) {
            return;
        }

        $lastSeen = $_SESSION['last_seen'] ?? null;
        if (is_int($lastSeen) && time() - $lastSeen > $timeout) {
            $this->logout();
            return;
        }

        $_SESSION['last_seen'] = time();
    }

    private function loginAttempts(int $windowSeconds): array
    {
        $windowSeconds = max(60, $windowSeconds);
        $since = time() - $windowSeconds;
        $attempts = $_SESSION['login_failures'] ?? [];
        if (!is_array($attempts)) {
            return [];
        }

        $attempts = array_values(array_filter(
            $attempts,
            static fn ($attempt): bool => is_int($attempt) && $attempt >= $since,
        ));
        $_SESSION['login_failures'] = $attempts;
        return $attempts;
    }
}
