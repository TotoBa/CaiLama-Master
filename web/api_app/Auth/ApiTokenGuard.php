<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Auth;

use CaiLama\WebApi\Http\Request;

final class ApiTokenGuard
{
    public static function hasScope(Request $request, array $config, string $scope): bool
    {
        return self::hasAnyScope($request, $config, [$scope]);
    }

    public static function hasAnyScope(Request $request, array $config, array $requiredScopes): bool
    {
        $token = self::bearerToken($request);
        if ($token === '') {
            return false;
        }

        $tokenHash = 'sha256:' . hash('sha256', $token);
        foreach (($config['api_tokens'] ?? []) as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $configuredHash = (string) ($entry['hash'] ?? '');
            $scopes = $entry['scopes'] ?? [];
            if (!is_array($scopes) || !array_intersect($requiredScopes, $scopes)) {
                continue;
            }
            if ($configuredHash !== '' && hash_equals($configuredHash, $tokenHash)) {
                return true;
            }
        }

        return false;
    }

    private static function bearerToken(Request $request): string
    {
        $header = $request->headers['authorization'] ?? '';
        if (!is_string($header)) {
            return '';
        }
        if (!preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
            return '';
        }
        return trim($matches[1]);
    }
}
