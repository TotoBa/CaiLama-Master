<?php
declare(strict_types=1);

namespace CaiLama\WebApi;

final class Response
{
    public function __construct(
        private readonly array|string $payload,
        private readonly int $status = 200,
        private readonly array $headers = [],
        private readonly bool $raw = false,
    ) {
    }

    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        return new self($payload, $status, $headers, false);
    }

    public static function raw(string $body, int $status = 200, array $headers = []): self
    {
        return new self($body, $status, $headers, true);
    }

    public function send(): void
    {
        header_remove('X-Powered-By');
        http_response_code($this->status);
        if (!$this->raw) {
            header('Content-Type: application/json; charset=utf-8');
        }
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($this->raw) {
            echo $this->payload;
            return;
        }

        echo json_encode($this->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
