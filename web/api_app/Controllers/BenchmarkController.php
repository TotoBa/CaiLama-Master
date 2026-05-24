<?php
declare(strict_types=1);

namespace CaiLama\WebApi\Controllers;

use CaiLama\WebApi\Auth\ApiTokenGuard;
use CaiLama\WebApi\Db\ConnectionFactory;
use CaiLama\WebApi\Http\Request;
use CaiLama\WebApi\Response;
use PDO;

final class BenchmarkController
{
    public function observations(Request $request, array $config): Response
    {
        if (!ApiTokenGuard::hasAnyScope($request, $config, ['benchmark:write', 'admin'])) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }
        if ($request->query !== []) {
            return $this->error('query_not_allowed', 'Query parameters are not allowed for this endpoint.', 400);
        }
        if (trim($request->body) === '') {
            return $this->error('body_required', 'JSON body is required.', 400);
        }

        try {
            $payload = json_decode($request->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return $this->error('invalid_json', 'JSON body is invalid.', 400);
        }
        if (!is_array($payload) || !is_array($payload['observations'] ?? null)) {
            return $this->error('invalid_payload', 'Expected observations array.', 400);
        }
        if (count($payload['observations']) > 100) {
            return $this->error('too_many_observations', 'Too many observations in one request.', 400);
        }

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $pdo->beginTransaction();
            $imported = 0;
            foreach ($payload['observations'] as $row) {
                if (!is_array($row)) {
                    throw new \InvalidArgumentException('Observation must be an object.');
                }
                $observation = $this->normalizeObservation($row);
                $caseId = $this->upsertCase($pdo, $observation);
                $this->upsertObservation($pdo, $caseId, $observation);
                $imported++;
            }
            $pdo->commit();
        } catch (\InvalidArgumentException $exc) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return $this->error('invalid_observation', $exc->getMessage(), 400);
        } catch (\Throwable) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return $this->error('import_failed', 'Benchmark observations could not be saved.', 500);
        }

        return Response::json([
            'status' => 'ok',
            'imported' => $imported,
        ]);
    }

    private function normalizeObservation(array $row): array
    {
        $area = $this->stringField($row, 'area', 40);
        if (!in_array($area, ['coding', 'chess_role', 'pipeline', 'search_rag'], true)) {
            throw new \InvalidArgumentException('Invalid area.');
        }
        return [
            'case_key' => $this->stringField($row, 'case_key', 120),
            'area' => $area,
            'role_name' => $this->stringField($row, 'role_name', 80),
            'model_label' => $this->stringField($row, 'model_label', 120),
            'task_label' => $this->stringField($row, 'task_label', 190),
            'task_summary' => $this->stringField($row, 'task_summary', 5000),
            'quality_question' => $this->stringField($row, 'quality_question', 255),
            'run_key' => $this->stringField($row, 'run_key', 120),
            'duration_ms' => $this->nullableInt($row, 'duration_ms'),
            'input_tokens' => $this->nullableInt($row, 'input_tokens'),
            'thinking_tokens' => $this->nullableInt($row, 'thinking_tokens'),
            'output_tokens' => $this->nullableInt($row, 'output_tokens'),
            'artifact_ref' => $this->optionalString($row, 'artifact_ref', 190),
            'output_excerpt' => $this->optionalString($row, 'output_excerpt', 5000),
        ];
    }

    private function upsertCase(PDO $pdo, array $observation): int
    {
        $statement = $pdo->prepare(
            "INSERT INTO cailama_model_benchmark_cases
                (case_key, area, role_name, task_label, task_summary, quality_question, status)
             VALUES
                (:case_key, :area, :role_name, :task_label, :task_summary, :quality_question, 'active')
             ON DUPLICATE KEY UPDATE
                area = VALUES(area),
                role_name = VALUES(role_name),
                task_label = VALUES(task_label),
                task_summary = VALUES(task_summary),
                quality_question = VALUES(quality_question),
                status = 'active'"
        );
        $statement->execute([
            'case_key' => $observation['case_key'],
            'area' => $observation['area'],
            'role_name' => $observation['role_name'],
            'task_label' => $observation['task_label'],
            'task_summary' => $observation['task_summary'],
            'quality_question' => $observation['quality_question'],
        ]);

        $select = $pdo->prepare("SELECT id FROM cailama_model_benchmark_cases WHERE case_key = :case_key");
        $select->execute(['case_key' => $observation['case_key']]);
        $id = $select->fetchColumn();
        if ($id === false) {
            throw new \RuntimeException('Benchmark case not found after upsert.');
        }
        return (int) $id;
    }

    private function upsertObservation(PDO $pdo, int $caseId, array $observation): void
    {
        $statement = $pdo->prepare(
            "INSERT INTO cailama_model_benchmark_observations
                (case_id, run_key, model_label, duration_ms, input_tokens, thinking_tokens, output_tokens,
                 artifact_ref, output_excerpt)
             VALUES
                (:case_id, :run_key, :model_label, :duration_ms, :input_tokens, :thinking_tokens, :output_tokens,
                 :artifact_ref, :output_excerpt)
             ON DUPLICATE KEY UPDATE
                duration_ms = VALUES(duration_ms),
                input_tokens = VALUES(input_tokens),
                thinking_tokens = VALUES(thinking_tokens),
                output_tokens = VALUES(output_tokens),
                output_excerpt = VALUES(output_excerpt)"
        );
        $statement->execute([
            'case_id' => $caseId,
            'run_key' => $observation['run_key'],
            'model_label' => $observation['model_label'],
            'duration_ms' => $observation['duration_ms'],
            'input_tokens' => $observation['input_tokens'],
            'thinking_tokens' => $observation['thinking_tokens'],
            'output_tokens' => $observation['output_tokens'],
            'artifact_ref' => $observation['artifact_ref'],
            'output_excerpt' => $observation['output_excerpt'],
        ]);
    }

    private function stringField(array $row, string $key, int $maxLength): string
    {
        $value = $row[$key] ?? null;
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Missing field: ' . $key);
        }
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('Empty field: ' . $key);
        }
        if (strlen($value) > $maxLength) {
            return substr($value, 0, $maxLength);
        }
        return $value;
    }

    private function optionalString(array $row, string $key, int $maxLength): string
    {
        $value = $row[$key] ?? '';
        if (!is_string($value)) {
            return '';
        }
        $value = trim($value);
        if (strlen($value) > $maxLength) {
            return substr($value, 0, $maxLength);
        }
        return $value;
    }

    private function nullableInt(array $row, string $key): ?int
    {
        $value = $row[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_int($value) && !(is_string($value) && preg_match('/^\d+$/', $value))) {
            throw new \InvalidArgumentException('Invalid integer field: ' . $key);
        }
        $int = (int) $value;
        return min($int, 2147483647);
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
