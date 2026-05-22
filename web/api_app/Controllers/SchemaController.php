<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Controllers;

use CaiLama\WebApi\Auth\ApiTokenGuard;
use CaiLama\WebApi\Db\ConnectionFactory;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;
use CaiLama\WebApi\Schema\SchemaInstaller;
use RuntimeException;

final class SchemaController
{
    public function cailama(Request $request, array $config): Response
    {
        return $this->apply($request, $config, ['cailama']);
    }

    public function all(Request $request, array $config): Response
    {
        return $this->apply($request, $config, ['cailama']);
    }

    private function apply(Request $request, array $config, array $targets): Response
    {
        if (!ApiTokenGuard::hasAnyScope($request, $config, ['admin'])) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }
        if ($request->query !== []) {
            return $this->error('query_not_allowed', 'Query parameters are not allowed for this endpoint.', 400);
        }
        if ($request->body !== '') {
            return $this->error('body_not_allowed', 'Request body is not allowed for this endpoint.', 400);
        }

        $installer = new SchemaInstaller();
        $applied = [];

        try {
            foreach ($targets as $target) {
                $pdo = ConnectionFactory::fromConfig($config, $target);
                $stats = $installer->apply($pdo, $this->schemaPath($target));
                $applied[$target] = $stats;
            }
        } catch (RuntimeException) {
            return $this->error('schema_failed', 'Schema setup failed.', 500);
        } catch (\Throwable) {
            return $this->error('schema_failed', 'Schema setup failed.', 500);
        }

        return Response::json([
            'status' => 'ok',
            'applied' => $applied,
        ]);
    }

    private function schemaPath(string $target): string
    {
        return match ($target) {
            'cailama' => __DIR__ . '/../schema/cailama-data.sql',
            default => throw new RuntimeException('Unknown schema target.'),
        };
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
