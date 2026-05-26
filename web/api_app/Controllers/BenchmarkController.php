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
    public function reset(Request $request, array $config): Response
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

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $pdo->beginTransaction();
            $feedbackDeleted = (int) $pdo->exec('DELETE FROM cailama_model_feedback');
            $observationsDeleted = (int) $pdo->exec('DELETE FROM cailama_model_benchmark_observations');
            $pdo->commit();
        } catch (\Throwable) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return $this->error('reset_failed', 'Benchmark feedback could not be reset.', 500);
        }

        return Response::json([
            'status' => 'ok',
            'feedback_deleted' => $feedbackDeleted,
            'observations_deleted' => $observationsDeleted,
        ]);
    }

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
            $autoFeedback = 0;
            foreach ($payload['observations'] as $row) {
                if (!is_array($row)) {
                    throw new \InvalidArgumentException('Observation must be an object.');
                }
                $observation = $this->normalizeObservation($row);
                $caseId = $this->upsertCase($pdo, $observation);
                $observationId = $this->upsertObservation($pdo, $caseId, $observation);
                if ($this->autoCloseObservation($pdo, $caseId, $observationId, $observation)) {
                    $autoFeedback++;
                }
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
            'auto_feedback' => $autoFeedback,
        ]);
    }

    public function feedbackExport(Request $request, array $config): Response
    {
        if (!ApiTokenGuard::hasAnyScope($request, $config, ['admin'])) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }
        if ($request->query !== []) {
            return $this->error('query_not_allowed', 'Query parameters are not allowed for this endpoint.', 400);
        }

        $runKey = '';
        $includeModelLabels = false;
        if (trim($request->body) !== '') {
            try {
                $payload = json_decode($request->body, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                return $this->error('invalid_json', 'JSON body is invalid.', 400);
            }
            if (!is_array($payload)) {
                return $this->error('invalid_payload', 'Expected JSON object.', 400);
            }
            $runKey = $this->optionalString($payload, 'run_key', 120);
            $includeModelLabels = (bool) ($payload['include_model_labels'] ?? false);
        }

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $params = [];
            $where = '';
            if ($runKey !== '') {
                $where = 'WHERE f.run_key = :run_key';
                $params['run_key'] = $runKey;
            }
            $statement = $pdo->prepare(
                "SELECT
                    f.run_key,
                    f.model_label,
                    f.quality_score,
                    f.task_solution_score,
                    f.duration_score,
                    f.logic_error_level,
                    f.preferred_option,
                    f.translation_score,
                    f.feedback_text,
                    f.improvement_note,
                    f.translation_note,
                    f.created_at,
                    c.case_key,
                    c.role_name,
                    c.task_label,
                    o.error_status,
                    o.error_message
                 FROM cailama_model_feedback f
                 INNER JOIN cailama_model_benchmark_cases c ON c.id = f.case_id
                 LEFT JOIN cailama_model_benchmark_observations o ON o.id = f.observation_id
                 " . $where . "
                 ORDER BY f.created_at ASC, f.id ASC
                 LIMIT 2000"
            );
            $statement->execute($params);
            $rows = [];
            foreach ($statement->fetchAll() as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $candidate = substr(hash('sha256', (string) $row['run_key'] . '|' . (string) $row['case_key'] . '|' . (string) $row['model_label']), 0, 12);
                $export = [
                    'run_key' => (string) $row['run_key'],
                    'candidate_code' => strtoupper($candidate),
                    'case_key' => (string) $row['case_key'],
                    'role_name' => (string) $row['role_name'],
                    'task_label' => (string) $row['task_label'],
                    'quality_score' => $row['quality_score'],
                    'task_solution_score' => $row['task_solution_score'],
                    'duration_score' => $row['duration_score'],
                    'logic_error_level' => (string) $row['logic_error_level'],
                    'preferred_option' => (string) $row['preferred_option'],
                    'translation_score' => $row['translation_score'],
                    'feedback_text' => (string) ($row['feedback_text'] ?? ''),
                    'improvement_note' => (string) ($row['improvement_note'] ?? ''),
                    'translation_note' => (string) ($row['translation_note'] ?? ''),
                    'error_status' => (string) ($row['error_status'] ?? ''),
                    'error_message' => (string) ($row['error_message'] ?? ''),
                    'created_at' => (string) $row['created_at'],
                ];
                if ($includeModelLabels) {
                    $export['model_label'] = (string) $row['model_label'];
                }
                $rows[] = $export;
            }
        } catch (\Throwable) {
            return $this->error('export_failed', 'Benchmark feedback could not be exported.', 500);
        }

        return Response::json([
            'status' => 'ok',
            'run_key' => $runKey,
            'count' => count($rows),
            'feedback' => $rows,
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
            'total_tokens' => $this->nullableInt($row, 'total_tokens'),
            'model_usage_level' => $this->optionalUsageLevel($row, 'model_usage_level'),
            'model_usage_weight' => $this->nullableInt($row, 'model_usage_weight'),
            'weighted_token_units' => $this->nullableInt($row, 'weighted_token_units'),
            'estimated_usage_units' => $this->nullableDecimal($row, 'estimated_usage_units'),
            'artifact_ref' => $this->optionalString($row, 'artifact_ref', 190),
            'position_fen' => $this->optionalFen($row, 'position_fen'),
            'side_to_move' => $this->optionalSideToMove($row, 'side_to_move'),
            'position_label' => $this->optionalString($row, 'position_label', 190),
            'task_query' => $this->optionalString($row, 'task_query', 60000),
            'system_prompt_excerpt' => $this->optionalString($row, 'system_prompt_excerpt', 200000),
            'task_prompt_excerpt' => $this->optionalString($row, 'task_prompt_excerpt', 200000),
            'expected_output_type' => $this->optionalString($row, 'expected_output_type', 80),
            'candidate_moves_excerpt' => $this->optionalString($row, 'candidate_moves_excerpt', 5000),
            'error_status' => $this->optionalString($row, 'error_status', 40),
            'error_message' => $this->optionalString($row, 'error_message', 500),
            'output_excerpt' => $this->optionalString($row, 'output_excerpt', 1000000),
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

    private function upsertObservation(PDO $pdo, int $caseId, array $observation): int
    {
        $statement = $pdo->prepare(
            "INSERT INTO cailama_model_benchmark_observations
                (case_id, run_key, model_label, duration_ms, input_tokens, thinking_tokens, output_tokens,
                 total_tokens, model_usage_level, model_usage_weight, weighted_token_units, estimated_usage_units,
                 artifact_ref, position_fen, side_to_move, position_label, task_query, system_prompt_excerpt, task_prompt_excerpt,
                 expected_output_type, candidate_moves_excerpt, error_status, error_message, output_excerpt)
             VALUES
                 (:case_id, :run_key, :model_label, :duration_ms, :input_tokens, :thinking_tokens, :output_tokens,
                  :total_tokens, :model_usage_level, :model_usage_weight, :weighted_token_units, :estimated_usage_units,
                 :artifact_ref, :position_fen, :side_to_move, :position_label, :task_query, :system_prompt_excerpt, :task_prompt_excerpt,
                 :expected_output_type, :candidate_moves_excerpt, :error_status, :error_message, :output_excerpt)
             ON DUPLICATE KEY UPDATE
                duration_ms = VALUES(duration_ms),
                input_tokens = VALUES(input_tokens),
                thinking_tokens = VALUES(thinking_tokens),
                output_tokens = VALUES(output_tokens),
                total_tokens = VALUES(total_tokens),
                model_usage_level = VALUES(model_usage_level),
                model_usage_weight = VALUES(model_usage_weight),
                weighted_token_units = VALUES(weighted_token_units),
                estimated_usage_units = VALUES(estimated_usage_units),
                position_fen = VALUES(position_fen),
                side_to_move = VALUES(side_to_move),
                position_label = VALUES(position_label),
                task_query = VALUES(task_query),
                system_prompt_excerpt = VALUES(system_prompt_excerpt),
                task_prompt_excerpt = VALUES(task_prompt_excerpt),
                expected_output_type = VALUES(expected_output_type),
                candidate_moves_excerpt = VALUES(candidate_moves_excerpt),
                error_status = VALUES(error_status),
                error_message = VALUES(error_message),
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
            'total_tokens' => $observation['total_tokens'],
            'model_usage_level' => $observation['model_usage_level'],
            'model_usage_weight' => $observation['model_usage_weight'],
            'weighted_token_units' => $observation['weighted_token_units'],
            'estimated_usage_units' => $observation['estimated_usage_units'],
            'artifact_ref' => $observation['artifact_ref'],
            'position_fen' => $observation['position_fen'],
            'side_to_move' => $observation['side_to_move'],
            'position_label' => $observation['position_label'],
            'task_query' => $observation['task_query'],
            'system_prompt_excerpt' => $observation['system_prompt_excerpt'],
            'task_prompt_excerpt' => $observation['task_prompt_excerpt'],
            'expected_output_type' => $observation['expected_output_type'],
            'candidate_moves_excerpt' => $observation['candidate_moves_excerpt'],
            'error_status' => $observation['error_status'],
            'error_message' => $observation['error_message'],
            'output_excerpt' => $observation['output_excerpt'],
        ]);

        $select = $pdo->prepare(
            "SELECT id
             FROM cailama_model_benchmark_observations
             WHERE case_id = :case_id
               AND run_key = :run_key
               AND model_label = :model_label
               AND artifact_ref = :artifact_ref
             LIMIT 1"
        );
        $select->execute([
            'case_id' => $caseId,
            'run_key' => $observation['run_key'],
            'model_label' => $observation['model_label'],
            'artifact_ref' => $observation['artifact_ref'],
        ]);
        $id = $select->fetchColumn();
        if ($id === false) {
            throw new \RuntimeException('Benchmark observation not found after upsert.');
        }
        return (int) $id;
    }

    private function autoCloseObservation(PDO $pdo, int $caseId, int $observationId, array $observation): bool
    {
        $status = (string) $observation['error_status'];
        if (!in_array($status, ['structure_failed', 'llm_error', 'model_failed'], true)) {
            return false;
        }

        $existing = $pdo->prepare(
            "SELECT 1 FROM cailama_model_feedback WHERE observation_id = :observation_id LIMIT 1"
        );
        $existing->execute(['observation_id' => $observationId]);
        if ($existing->fetchColumn() !== false) {
            return false;
        }

        $message = $this->autoFeedbackMessage($status, (string) $observation['error_message']);
        $statement = $pdo->prepare(
            "INSERT INTO cailama_model_feedback
                (observation_id, case_id, user_id, run_key, model_label, duration_ms, input_tokens,
                 thinking_tokens, output_tokens, total_tokens, model_usage_level, model_usage_weight,
                 weighted_token_units, estimated_usage_units, quality_score, task_solution_score,
                 logic_error_level, preferred_option, feedback_text, improvement_note)
             VALUES
                (:observation_id, :case_id, NULL, :run_key, :model_label, :duration_ms, :input_tokens,
                 :thinking_tokens, :output_tokens, :total_tokens, :model_usage_level, :model_usage_weight,
                 :weighted_token_units, :estimated_usage_units, 1, 1,
                 'major', 'not_applicable', :feedback_text, :improvement_note)"
        );
        $statement->execute([
            'observation_id' => $observationId,
            'case_id' => $caseId,
            'run_key' => $observation['run_key'],
            'model_label' => $observation['model_label'],
            'duration_ms' => $observation['duration_ms'],
            'input_tokens' => $observation['input_tokens'],
            'thinking_tokens' => $observation['thinking_tokens'],
            'output_tokens' => $observation['output_tokens'],
            'total_tokens' => $observation['total_tokens'],
            'model_usage_level' => $observation['model_usage_level'],
            'model_usage_weight' => $observation['model_usage_weight'],
            'weighted_token_units' => $observation['weighted_token_units'],
            'estimated_usage_units' => $observation['estimated_usage_units'],
            'feedback_text' => $message,
            'improvement_note' => (string) $observation['error_message'],
        ]);
        return true;
    }

    private function autoFeedbackMessage(string $status, string $message): string
    {
        $prefix = match ($status) {
            'structure_failed' => 'Automatisch: Struktur-/Toolprüfung fehlgeschlagen.',
            'llm_error' => 'Automatisch: LLM-Aufruf fehlgeschlagen.',
            'model_failed' => 'Automatisch: Modelllauf fehlgeschlagen.',
            default => 'Automatisch: Fall nicht manuell bewertbar.',
        };
        $message = trim($message);
        return $message === '' ? $prefix : $prefix . ' ' . $message;
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

    private function optionalUsageLevel(array $row, string $key): string
    {
        $value = strtolower($this->optionalString($row, $key, 32));
        if ($value === '') {
            return '';
        }
        if (!in_array($value, ['local', 'unknown', 'low', 'medium', 'high', 'extra high'], true)) {
            return 'unknown';
        }
        return $value;
    }

    private function nullableDecimal(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_int($value) && !is_float($value) && !(is_string($value) && is_numeric($value))) {
            throw new \InvalidArgumentException('Invalid decimal field: ' . $key);
        }
        $float = max(0.0, min((float) $value, 999999999999999.999));
        return number_format($float, 3, '.', '');
    }

    private function optionalFen(array $row, string $key): string
    {
        $value = $this->optionalString($row, $key, 120);
        if ($value === '') {
            return '';
        }
        $parts = preg_split('/\s+/', $value) ?: [];
        if (count($parts) !== 6 || !in_array($parts[1], ['w', 'b'], true)) {
            throw new \InvalidArgumentException('Invalid FEN field: ' . $key);
        }
        if (!preg_match('/^[prnbqkPRNBQK1-8\/]+$/', $parts[0])) {
            throw new \InvalidArgumentException('Invalid FEN field: ' . $key);
        }
        return $value;
    }

    private function optionalSideToMove(array $row, string $key): string
    {
        $value = strtolower($this->optionalString($row, $key, 8));
        if ($value === '') {
            return '';
        }
        if (!in_array($value, ['w', 'b', 'white', 'black'], true)) {
            throw new \InvalidArgumentException('Invalid side_to_move field.');
        }
        return $value;
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
