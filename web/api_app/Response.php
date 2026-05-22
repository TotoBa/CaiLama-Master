<?php
declare(strict_types=1);

namespace CaiLama\WebApi;

final class Response
{
    public function __construct(
        private readonly array $payload,
        private readonly int $status = 200,
        private readonly array $headers = [],
    ) {
    }

    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        return new self($payload, $status, $headers);
    }

    public function send(): void
    {
        header_remove('X-Powered-By');
        http_response_code($this->status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo json_encode($this->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
