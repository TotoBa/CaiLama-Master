<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Controllers;

use CaiLama\WebApi\Auth\SessionManager;
use CaiLama\WebApi\Auth\UserProfileService;
use CaiLama\WebApi\Db\ConnectionFactory;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;

final class WebAppProxyController
{
    public function dispatch(Request $request, array $config, SessionManager $session): Response
    {
        $user = $session->currentUser();
        if ($user === null) {
            return $this->error('unauthorized', 'Login required.', 401);
        }

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $user = (new UserProfileService($pdo, $config['auth'] ?? []))->attachProfile($user);
        } catch (\Throwable) {
            // Profile enrichment is best-effort.
        }

        $webApi = $config['web_api'] ?? [];
        $baseUrl = rtrim((string) ($webApi['base_url'] ?? ''), '/');
        $token = (string) ($webApi['session_token'] ?? '');
        if ($baseUrl === '' || !str_starts_with($baseUrl, 'https://')) {
            return $this->error('web_api_not_configured', 'Web API origin is not configured.', 503);
        }

        $path = $this->normalizePath($request->path);
        if ($path === '') {
            return $this->error('not_found', 'Endpoint not found.', 404);
        }

        $targetUrl = $baseUrl . $path;
        if ($request->query !== []) {
            $targetUrl .= '?' . http_build_query($request->query);
        }

        $headers = [
            'Accept: application/json',
            'X-Web-User-Id: ' . (string) ($user['id'] ?? ''),
        ];
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        if (!empty($user['profile_key'])) {
            $headers[] = 'X-CaiLama-Profile-Key: ' . (string) $user['profile_key'];
        }
        if (!empty($user['training_name'])) {
            $headers[] = 'X-CaiLama-Training-Name: ' . (string) $user['training_name'];
        }

        $body = $request->body;
        if ($body !== '') {
            $headers[] = 'Content-Type: application/json';
        }

        $timeout = max(5, (int) ($webApi['timeout_seconds'] ?? 60));
        $originResponse = $this->sendRequest(
            $targetUrl,
            $request->method,
            $body,
            $headers,
            $timeout,
            str_contains($path, '/events'),
        );

        if ($originResponse['error'] !== '') {
            return $this->error('origin_unavailable', 'Web API request failed.', 502);
        }

        $contentType = $originResponse['content_type'] ?: 'application/json';
        if (str_contains($contentType, 'text/event-stream')) {
            return Response::raw(
                is_string($originResponse['body']) ? $originResponse['body'] : '',
                $originResponse['status'] ?: 200,
                [
                    'Content-Type' => 'text/event-stream; charset=utf-8',
                    'Cache-Control' => 'no-cache',
                    'X-Accel-Buffering' => 'no',
                ],
            );
        }

        $payload = json_decode((string) $originResponse['body'], true);
        if (!is_array($payload)) {
            return $this->error('origin_invalid_response', 'Web API response was not valid JSON.', 502);
        }

        return Response::json($payload, $originResponse['status'] ?: 200);
    }

    private function normalizePath(string $path): string
    {
        $path = preg_replace('#^/api/v1/web#', '', $path) ?? $path;
        $path = preg_replace('#^/app/api#', '', $path) ?? $path;
        $path = '/' . ltrim($path, '/');
        return $path === '/' ? '' : $path;
    }

    /**
     * @param list<string> $headers
     * @return array{status: int, body: string, error: string, content_type: string}
     */
    private function sendRequest(
        string $url,
        string $method,
        string $body,
        array $headers,
        int $timeout,
        bool $stream,
    ): array {
        if (!function_exists('curl_init')) {
            return ['status' => 502, 'body' => '', 'error' => 'curl_missing', 'content_type' => ''];
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
            CURLOPT_TIMEOUT => $stream ? 0 : $timeout,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        ]);
        if ($body !== '') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $error = curl_error($curl);
        curl_close($curl);

        if (!is_string($raw)) {
            return ['status' => 502, 'body' => '', 'error' => $error ?: 'curl_failed', 'content_type' => ''];
        }

        $rawHeaders = substr($raw, 0, $headerSize);
        $responseBody = substr($raw, $headerSize);
        $contentType = '';
        if (preg_match('/^Content-Type:\s*([^\r\n;]+)/mi', $rawHeaders, $matches)) {
            $contentType = trim($matches[1]);
        }

        return [
            'status' => $status > 0 ? $status : 502,
            'body' => $responseBody,
            'error' => $error,
            'content_type' => $contentType,
        ];
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
