<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Http;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $headers,
        public readonly string $body,
        public readonly bool $bodyTooLarge = false,
    ) {
    }

    public static function fromGlobals(int $maxBodyBytes = 1048576): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/') ?: '/';
        $bodyTooLarge = false;
        $body = '';
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($maxBodyBytes > 0 && $contentLength > $maxBodyBytes) {
            $bodyTooLarge = true;
        } else {
            $readLimit = $maxBodyBytes > 0 ? $maxBodyBytes + 1 : 1048577;
            $body = file_get_contents('php://input', false, null, 0, $readLimit) ?: '';
            if ($maxBodyBytes > 0 && strlen($body) > $maxBodyBytes) {
                $bodyTooLarge = true;
                $body = substr($body, 0, $maxBodyBytes);
            }
        }

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path,
            $_GET,
            self::headersFromGlobals(),
            $body,
            $bodyTooLarge,
        );
    }

    private static function headersFromGlobals(): array
    {
        $headers = [];
        if (function_exists('getallheaders')) {
            foreach (getallheaders() ?: [] as $name => $value) {
                $headers[strtolower((string) $name)] = $value;
            }
        }
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }
        foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $key) {
            if (isset($_SERVER[$key])) {
                $headers['authorization'] = $_SERVER[$key];
            }
        }
        return $headers;
    }
}
