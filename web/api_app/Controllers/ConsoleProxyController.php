<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Controllers;

use CaiLama\WebApi\Auth\ConsoleKeyGuard;
use CaiLama\WebApi\Db\ConnectionFactory;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;

final class ConsoleProxyController
{
    public function llmChat(Request $request, array $config): Response
    {
        return $this->proxy($request, $config, 'llm_chat');
    }

    public function searchQuery(Request $request, array $config): Response
    {
        return $this->proxy($request, $config, 'search_query');
    }

    public function createJob(Request $request, array $config): Response
    {
        return $this->proxy($request, $config, 'jobs_create');
    }

    public function listJobs(Request $request, array $config): Response
    {
        return $this->proxy($request, $config, 'jobs_list');
    }

    public function jobStatus(Request $request, array $config): Response
    {
        return $this->proxy($request, $config, 'jobs_status');
    }

    public function jobResult(Request $request, array $config): Response
    {
        return $this->proxy($request, $config, 'jobs_result');
    }

    private function proxy(Request $request, array $config, string $routeName): Response
    {
        $route = $config['origin']['allowed_routes'][$routeName] ?? null;
        if (!is_array($route)) {
            return $this->error('route_not_configured', 'Origin route is not configured.', 503);
        }
        if ($request->method !== (string) ($route['method'] ?? '')) {
            return $this->error('method_not_allowed', 'Method not allowed.', 405);
        }
        if (trim($request->body) === '') {
            return $this->error('body_required', 'JSON body is required.', 400);
        }
        try {
            json_decode($request->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return $this->error('invalid_json', 'JSON body is invalid.', 400);
        }

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $profile = ConsoleKeyGuard::authenticate($request, $pdo, (string) ($route['scope'] ?? ''));
        } catch (\Throwable) {
            return $this->error('auth_unavailable', 'Console authentication is unavailable.', 503);
        }
        if ($profile === null) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }

        $origin = $config['origin'] ?? [];
        $baseUrl = rtrim((string) ($origin['base_url'] ?? ''), '/');
        $proxyKey = (string) ($origin['proxy_key'] ?? '');
        $hmacSecret = (string) ($origin['hmac_secret'] ?? '');
        if (!str_starts_with($baseUrl, 'https://') || $proxyKey === '' || $hmacSecret === '') {
            return $this->error('origin_not_configured', 'Origin proxy is not configured.', 503);
        }

        $originPath = (string) ($route['path'] ?? '');
        $originResponse = $this->sendOriginRequest(
            $baseUrl . $originPath,
            (string) $route['method'],
            $originPath,
            $request->body,
            $proxyKey,
            $hmacSecret,
            max(1, (int) ($origin['timeout_seconds'] ?? 20)),
            $profile,
        );

        if ($originResponse['error'] !== '') {
            return $this->error('origin_unavailable', 'Origin request failed.', 502);
        }

        $payload = json_decode($originResponse['body'], true);
        if (!is_array($payload)) {
            return $this->error('origin_invalid_response', 'Origin response was not valid JSON.', 502);
        }
        return Response::json($payload, $originResponse['status']);
    }

    private function sendOriginRequest(
        string $url,
        string $method,
        string $path,
        string $body,
        string $proxyKey,
        string $hmacSecret,
        int $timeout,
        array $profile,
    ): array {
        $timestamp = (string) time();
        $bodySha = hash('sha256', $body);
        $signaturePayload = $method . "\n" . $path . "\n" . $timestamp . "\n" . $bodySha;
        $signature = hash_hmac('sha256', $signaturePayload, $hmacSecret);
        $headers = [
            'Content-Type: application/json',
            'X-CaiLama-Proxy-Key: ' . $proxyKey,
            'X-CaiLama-Timestamp: ' . $timestamp,
            'X-CaiLama-Body-SHA256: ' . $bodySha,
            'X-CaiLama-Signature: ' . $signature,
            'X-CaiLama-Profile-Key: ' . (string) $profile['profile_key'],
            'X-CaiLama-Training-Name: ' . (string) $profile['training_name'],
        ];

        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            ]);
            $responseBody = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            return [
                'status' => $status > 0 ? $status : 502,
                'body' => is_string($responseBody) ? $responseBody : '',
                'error' => $error,
            ];
        }

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
        ]);
        $responseBody = @file_get_contents($url, false, $context);
        $status = $this->statusFromHeaders($http_response_header ?? []);
        return [
            'status' => $status,
            'body' => is_string($responseBody) ? $responseBody : '',
            'error' => is_string($responseBody) ? '' : 'stream_failed',
        ];
    }

    private function statusFromHeaders(array $headers): int
    {
        $line = (string) ($headers[0] ?? '');
        if (preg_match('/\s(\d{3})\s/', $line, $matches)) {
            return (int) $matches[1];
        }
        return 502;
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
