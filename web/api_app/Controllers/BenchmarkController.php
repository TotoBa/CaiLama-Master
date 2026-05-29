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
    private const AUTO_CLOSE_ERROR_STATUSES = [
        'structure_failed',
        'invalid_json',
        'missing_required_field',
        'unexpected_tool',
        'invalid_tool_call',
        'missing_tool',
        'tool_argument_mismatch',
        'wrong_role',
        'routing_role_mismatch',
        'routing_tool_mismatch',
        'boardtruth_conflict',
        'empty_optional_field_reference',
        'guessed_fen',
        'rendered_board',
        'missing_citation',
        'fake_engine_claim',
        'invalid_quality_band',
        'empty_output',
        'llm_error',
        'model_failed',
    ];

    private const CONTRACT_ERROR_STATUSES = [
        'structure_failed',
        'invalid_json',
        'missing_required_field',
        'unexpected_tool',
        'invalid_tool_call',
        'missing_tool',
        'tool_argument_mismatch',
        'wrong_role',
        'routing_role_mismatch',
        'routing_tool_mismatch',
        'boardtruth_conflict',
        'empty_optional_field_reference',
        'guessed_fen',
        'rendered_board',
        'missing_citation',
        'fake_engine_claim',
        'invalid_quality_band',
        'empty_output',
    ];

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

    public function feedbackOpen(Request $request, array $config): Response
    {
        if (!ApiTokenGuard::hasAnyScope($request, $config, ['benchmark:feedback', 'admin'])) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }
        if ($request->query !== []) {
            return $this->error('query_not_allowed', 'Query parameters are not allowed for this endpoint.', 400);
        }

        $payload = [];
        if (trim($request->body) !== '') {
            try {
                $payload = json_decode($request->body, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                return $this->error('invalid_json', 'JSON body is invalid.', 400);
            }
            if (!is_array($payload)) {
                return $this->error('invalid_payload', 'Expected JSON object.', 400);
            }
        }

        $runKey = $this->optionalString($payload, 'run_key', 120);
        try {
            $limit = $this->optionalBoundedInt($payload, 'limit', 1, 200, 50);
        } catch (\InvalidArgumentException $exc) {
            return $this->error('invalid_feedback_query', $exc->getMessage(), 400);
        }
        $canSeeModelLabels = ApiTokenGuard::hasAnyScope($request, $config, ['admin']);
        $includeModelLabels = $canSeeModelLabels && (bool) ($payload['include_model_labels'] ?? false);

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            if ($runKey === '') {
                $runKey = $this->latestRunKey($pdo);
            }
            [$stats, $runs] = $this->feedbackRunOverview($pdo, $runKey);
            $observations = $this->openFeedbackObservations($pdo, $runKey, $limit, $includeModelLabels);
        } catch (\Throwable) {
            return $this->error('feedback_open_failed', 'Open benchmark feedback could not be loaded.', 500);
        }

        return Response::json([
            'status' => 'ok',
            'run_key' => $runKey,
            'include_model_labels' => $includeModelLabels,
            'stats' => $stats,
            'runs' => $runs,
            'count' => count($observations),
            'observations' => $observations,
        ]);
    }

    public function feedbackSubmit(Request $request, array $config): Response
    {
        if (!ApiTokenGuard::hasAnyScope($request, $config, ['benchmark:feedback', 'admin'])) {
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
        if (!is_array($payload)) {
            return $this->error('invalid_payload', 'Expected JSON object.', 400);
        }

        $feedbackRows = $payload['feedback'] ?? [$payload];
        if (!is_array($feedbackRows)) {
            return $this->error('invalid_payload', 'Expected feedback object or feedback array.', 400);
        }
        if (count($feedbackRows) > 100) {
            return $this->error('too_many_feedback_items', 'Too many feedback items in one request.', 400);
        }

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            $pdo->beginTransaction();
            $saved = [];
            foreach ($feedbackRows as $row) {
                if (!is_array($row)) {
                    throw new \InvalidArgumentException('Feedback item must be an object.');
                }
                $values = $this->normalizeFeedback($row);
                $observation = $this->loadFeedbackObservation($pdo, $values['observation_id']);
                if ($observation === null) {
                    throw new \InvalidArgumentException('Unknown observation_id: ' . $values['observation_id']);
                }
                $this->upsertApiFeedback($pdo, $observation, $values);
                $saved[] = [
                    'observation_id' => $values['observation_id'],
                    'run_key' => (string) $observation['run_key'],
                    'candidate_code' => $this->candidateCode($observation),
                    'role_name' => (string) $observation['role_name'],
                    'task_label' => (string) $observation['task_label'],
                ];
            }
            $pdo->commit();
        } catch (\InvalidArgumentException $exc) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return $this->error('invalid_feedback', $exc->getMessage(), 400);
        } catch (\Throwable) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return $this->error('feedback_save_failed', 'Benchmark feedback could not be saved.', 500);
        }

        return Response::json([
            'status' => 'ok',
            'saved_count' => count($saved),
            'saved' => $saved,
        ]);
    }

    public function feedbackSummary(Request $request, array $config): Response
    {
        if (!ApiTokenGuard::hasAnyScope($request, $config, ['benchmark:feedback', 'admin'])) {
            return $this->error('unauthorized', 'Unauthorized.', 401);
        }
        if ($request->query !== []) {
            return $this->error('query_not_allowed', 'Query parameters are not allowed for this endpoint.', 400);
        }

        $payload = [];
        if (trim($request->body) !== '') {
            try {
                $payload = json_decode($request->body, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                return $this->error('invalid_json', 'JSON body is invalid.', 400);
            }
            if (!is_array($payload)) {
                return $this->error('invalid_payload', 'Expected JSON object.', 400);
            }
        }

        $runKey = $this->optionalString($payload, 'run_key', 120);
        $canSeeModelLabels = ApiTokenGuard::hasAnyScope($request, $config, ['admin']);
        $includeModelLabels = $canSeeModelLabels && (bool) ($payload['include_model_labels'] ?? false);

        try {
            $pdo = ConnectionFactory::fromConfig($config, 'cailama');
            if ($runKey === '') {
                $runKey = $this->latestRunKey($pdo);
            }
            [$roleSummary, $modelRoleSummary, $errorClasses, $roleRecommendations] = $this->feedbackSummaryRows($pdo, $runKey, $includeModelLabels);
        } catch (\Throwable) {
            return $this->error('feedback_summary_failed', 'Benchmark feedback summary could not be loaded.', 500);
        }

        return Response::json([
            'status' => 'ok',
            'run_key' => $runKey,
            'include_model_labels' => $includeModelLabels,
            'roles' => $roleSummary,
            'model_roles' => $modelRoleSummary,
            'error_classes' => $errorClasses,
            'role_recommendations' => $roleRecommendations,
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

    private function latestRunKey(PDO $pdo): string
    {
        $latest = $pdo->query(
            "SELECT run_key
             FROM cailama_model_benchmark_observations
             GROUP BY run_key
             ORDER BY MAX(created_at) DESC, run_key DESC
             LIMIT 1"
        )->fetchColumn();

        return is_string($latest) ? $latest : '';
    }

    private function feedbackRunOverview(PDO $pdo, string $runKey): array
    {
        $params = [];
        $where = '';
        if ($runKey !== '') {
            $where = ' AND o.run_key = :run_key';
            $params['run_key'] = $runKey;
        }

        $stats = $pdo->prepare(
            "SELECT
                COUNT(*) AS imported_count,
                SUM(CASE WHEN f.id IS NULL THEN 1 ELSE 0 END) AS open_count,
                SUM(CASE WHEN f.id IS NULL THEN 0 ELSE 1 END) AS done_count
             FROM cailama_model_benchmark_observations o
             INNER JOIN cailama_model_benchmark_cases c ON c.id = o.case_id
             LEFT JOIN cailama_model_feedback f ON f.observation_id = o.id
             WHERE 1=1" . $where
        );
        $stats->execute($params);
        $statsRow = $stats->fetch();

        $runs = $pdo->query(
            "SELECT
                o.run_key,
                COUNT(*) AS imported_count,
                SUM(CASE WHEN f.id IS NULL THEN 1 ELSE 0 END) AS open_count,
                SUM(CASE WHEN f.id IS NULL THEN 0 ELSE 1 END) AS done_count,
                MIN(o.created_at) AS first_seen,
                MAX(o.created_at) AS last_seen
             FROM cailama_model_benchmark_observations o
             LEFT JOIN cailama_model_feedback f ON f.observation_id = o.id
             GROUP BY o.run_key
             ORDER BY last_seen DESC, o.run_key DESC
             LIMIT 50"
        )->fetchAll();

        return [
            is_array($statsRow) ? [
                'imported_count' => (int) ($statsRow['imported_count'] ?? 0),
                'open_count' => (int) ($statsRow['open_count'] ?? 0),
                'done_count' => (int) ($statsRow['done_count'] ?? 0),
            ] : ['imported_count' => 0, 'open_count' => 0, 'done_count' => 0],
            array_values(array_map(static function (array $row): array {
                return [
                    'run_key' => (string) ($row['run_key'] ?? ''),
                    'imported_count' => (int) ($row['imported_count'] ?? 0),
                    'open_count' => (int) ($row['open_count'] ?? 0),
                    'done_count' => (int) ($row['done_count'] ?? 0),
                    'first_seen' => (string) ($row['first_seen'] ?? ''),
                    'last_seen' => (string) ($row['last_seen'] ?? ''),
                ];
            }, $runs ?: [])),
        ];
    }

    private function openFeedbackObservations(PDO $pdo, string $runKey, int $limit, bool $includeModelLabels): array
    {
        $params = [];
        $where = 'f.id IS NULL';
        if ($runKey !== '') {
            $where .= ' AND o.run_key = :run_key';
            $params['run_key'] = $runKey;
        }

        $statement = $pdo->prepare(
            "SELECT
                o.id,
                o.case_id,
                o.run_key,
                o.model_label,
                o.duration_ms,
                o.input_tokens,
                o.thinking_tokens,
                o.output_tokens,
                o.total_tokens,
                o.model_usage_level,
                o.model_usage_weight,
                o.weighted_token_units,
                o.estimated_usage_units,
                o.artifact_ref,
                o.position_fen,
                o.side_to_move,
                o.position_label,
                o.task_query,
                o.system_prompt_excerpt,
                o.task_prompt_excerpt,
                o.expected_output_type,
                o.candidate_moves_excerpt,
                o.error_status,
                o.error_message,
                o.output_excerpt,
                o.created_at,
                c.case_key,
                c.area,
                c.task_label,
                c.task_summary,
                c.role_name,
                c.quality_question
             FROM cailama_model_benchmark_observations o
             INNER JOIN cailama_model_benchmark_cases c ON c.id = o.case_id
             LEFT JOIN cailama_model_feedback f ON f.observation_id = o.id
             WHERE " . $where . "
             ORDER BY o.created_at ASC, c.role_name, c.task_label, MD5(CONCAT(o.run_key, c.case_key, o.model_label, o.id))
             LIMIT :limit"
        );
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        $rows = [];
        foreach ($statement->fetchAll() as $row) {
            if (is_array($row)) {
                $rows[] = $this->exportObservation($row, $includeModelLabels);
            }
        }
        return $rows;
    }

    private function feedbackSummaryRows(PDO $pdo, string $runKey, bool $includeModelLabels): array
    {
        $params = [];
        $where = '';
        if ($runKey !== '') {
            $where = 'WHERE f.run_key = :run_key';
            $params['run_key'] = $runKey;
        }

        $roleStatement = $pdo->prepare(
            "SELECT
                c.role_name,
                COUNT(*) AS feedback_count,
                ROUND(AVG(f.quality_score), 2) AS quality_avg,
                ROUND(AVG(f.task_solution_score), 2) AS task_solution_avg,
                ROUND(AVG(f.duration_score), 2) AS duration_score_avg,
                ROUND(AVG(f.translation_score), 2) AS translation_avg,
                SUM(f.logic_error_level = 'major') AS major_logic_errors,
                SUM(f.logic_error_level = 'minor') AS minor_logic_errors,
                SUM(CASE WHEN COALESCE(o.error_status, '') <> '' THEN 1 ELSE 0 END) AS error_observations,
                ROUND(AVG(f.duration_ms), 0) AS duration_ms_avg,
                ROUND(AVG(f.total_tokens), 0) AS total_tokens_avg,
                ROUND(SUM(f.estimated_usage_units), 3) AS estimated_usage_units_sum
             FROM cailama_model_feedback f
             INNER JOIN cailama_model_benchmark_cases c ON c.id = f.case_id
             LEFT JOIN cailama_model_benchmark_observations o ON o.id = f.observation_id
             " . $where . "
             GROUP BY c.role_name
             ORDER BY c.role_name"
        );
        $roleStatement->execute($params);

        $modelStatement = $pdo->prepare(
            "SELECT
                f.run_key,
                c.role_name,
                f.model_label,
                COUNT(*) AS feedback_count,
                ROUND(AVG(f.quality_score), 2) AS quality_avg,
                ROUND(AVG(f.task_solution_score), 2) AS task_solution_avg,
                ROUND(AVG(f.duration_score), 2) AS duration_score_avg,
                ROUND(AVG(f.translation_score), 2) AS translation_avg,
                SUM(f.logic_error_level = 'major') AS major_logic_errors,
                SUM(f.logic_error_level = 'minor') AS minor_logic_errors,
                SUM(CASE WHEN COALESCE(o.error_status, '') <> '' THEN 1 ELSE 0 END) AS error_observations,
                ROUND(AVG(f.duration_ms), 0) AS duration_ms_avg,
                ROUND(AVG(f.total_tokens), 0) AS total_tokens_avg,
                ROUND(SUM(f.estimated_usage_units), 3) AS estimated_usage_units_sum
             FROM cailama_model_feedback f
             INNER JOIN cailama_model_benchmark_cases c ON c.id = f.case_id
             LEFT JOIN cailama_model_benchmark_observations o ON o.id = f.observation_id
             " . $where . "
             GROUP BY f.run_key, c.role_name, f.model_label
             ORDER BY c.role_name, quality_avg DESC, task_solution_avg DESC, major_logic_errors ASC, duration_score_avg DESC, duration_ms_avg ASC
             LIMIT 1000"
        );
        $modelStatement->execute($params);

        $observationWhere = '';
        if ($runKey !== '') {
            $observationWhere = ' AND o.run_key = :run_key';
        }
        $errorStatement = $pdo->prepare(
            "SELECT
                o.run_key,
                c.role_name,
                o.model_label,
                o.error_status,
                COUNT(*) AS error_count
             FROM cailama_model_benchmark_observations o
             INNER JOIN cailama_model_benchmark_cases c ON c.id = o.case_id
             WHERE o.error_status <> ''" . $observationWhere . "
             GROUP BY o.run_key, c.role_name, o.model_label, o.error_status
             ORDER BY c.role_name, error_count DESC, o.error_status, o.model_label
             LIMIT 1000"
        );
        $errorStatement->execute($params);

        $roles = [];
        foreach ($roleStatement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }
            $roles[] = [
                'role_name' => (string) $row['role_name'],
                'feedback_count' => (int) $row['feedback_count'],
                'quality_avg' => $row['quality_avg'] === null ? null : (float) $row['quality_avg'],
                'task_solution_avg' => $row['task_solution_avg'] === null ? null : (float) $row['task_solution_avg'],
                'duration_score_avg' => $row['duration_score_avg'] === null ? null : (float) $row['duration_score_avg'],
                'translation_avg' => $row['translation_avg'] === null ? null : (float) $row['translation_avg'],
                'major_logic_errors' => (int) $row['major_logic_errors'],
                'minor_logic_errors' => (int) $row['minor_logic_errors'],
                'error_observations' => (int) $row['error_observations'],
                'duration_ms_avg' => $row['duration_ms_avg'] === null ? null : (int) $row['duration_ms_avg'],
                'total_tokens_avg' => $row['total_tokens_avg'] === null ? null : (int) $row['total_tokens_avg'],
                'estimated_usage_units_sum' => $row['estimated_usage_units_sum'] === null ? null : (float) $row['estimated_usage_units_sum'],
            ];
        }

        $modelRoles = [];
        foreach ($modelStatement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }
            $candidate = $this->modelRoleCandidateCode((string) $row['run_key'], (string) $row['role_name'], (string) $row['model_label']);
            $export = [
                'run_key' => (string) $row['run_key'],
                'role_name' => (string) $row['role_name'],
                'candidate_code' => $candidate,
                'feedback_count' => (int) $row['feedback_count'],
                'quality_avg' => $row['quality_avg'] === null ? null : (float) $row['quality_avg'],
                'task_solution_avg' => $row['task_solution_avg'] === null ? null : (float) $row['task_solution_avg'],
                'duration_score_avg' => $row['duration_score_avg'] === null ? null : (float) $row['duration_score_avg'],
                'translation_avg' => $row['translation_avg'] === null ? null : (float) $row['translation_avg'],
                'major_logic_errors' => (int) $row['major_logic_errors'],
                'minor_logic_errors' => (int) $row['minor_logic_errors'],
                'error_observations' => (int) $row['error_observations'],
                'duration_ms_avg' => $row['duration_ms_avg'] === null ? null : (int) $row['duration_ms_avg'],
                'total_tokens_avg' => $row['total_tokens_avg'] === null ? null : (int) $row['total_tokens_avg'],
                'estimated_usage_units_sum' => $row['estimated_usage_units_sum'] === null ? null : (float) $row['estimated_usage_units_sum'],
            ];
            if ($includeModelLabels) {
                $export['model_label'] = (string) $row['model_label'];
            }
            $modelRoles[] = $export;
        }

        $errorClasses = [];
        foreach ($errorStatement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }
            $export = [
                'run_key' => (string) $row['run_key'],
                'role_name' => (string) $row['role_name'],
                'candidate_code' => $this->modelRoleCandidateCode((string) $row['run_key'], (string) $row['role_name'], (string) $row['model_label']),
                'error_status' => (string) $row['error_status'],
                'error_count' => (int) $row['error_count'],
                'is_contract_error' => in_array((string) $row['error_status'], self::CONTRACT_ERROR_STATUSES, true),
            ];
            if ($includeModelLabels) {
                $export['model_label'] = (string) $row['model_label'];
            }
            $errorClasses[] = $export;
        }

        return [$roles, $modelRoles, $errorClasses, $this->roleRecommendations($modelRoles, $errorClasses)];
    }

    private function modelRoleCandidateCode(string $runKey, string $roleName, string $modelLabel): string
    {
        return strtoupper(substr(hash('sha256', $runKey . '|' . $roleName . '|' . $modelLabel), 0, 12));
    }

    private function roleRecommendations(array $modelRoles, array $errorClasses): array
    {
        $errorsByRole = [];
        $errorsByCandidate = [];
        foreach ($errorClasses as $error) {
            if (!is_array($error)) {
                continue;
            }
            $role = (string) ($error['role_name'] ?? '');
            $candidate = (string) ($error['candidate_code'] ?? '');
            $status = (string) ($error['error_status'] ?? '');
            $count = (int) ($error['error_count'] ?? 0);
            if ($role === '' || $status === '') {
                continue;
            }
            $errorsByRole[$role][$status] = ($errorsByRole[$role][$status] ?? 0) + $count;
            if ($candidate !== '' && (bool) ($error['is_contract_error'] ?? false)) {
                $errorsByCandidate[$role][$candidate] = ($errorsByCandidate[$role][$candidate] ?? 0) + $count;
            }
        }

        $byRole = [];
        foreach ($modelRoles as $row) {
            if (!is_array($row)) {
                continue;
            }
            $role = (string) ($row['role_name'] ?? '');
            if ($role !== '') {
                $byRole[$role][] = $row;
            }
        }

        $recommendations = [];
        foreach ($byRole as $role => $rows) {
            usort($rows, function (array $left, array $right): int {
                return $this->recommendationScore($right) <=> $this->recommendationScore($left);
            });
            $excluded = [];
            $best = null;
            foreach ($rows as $row) {
                $candidate = (string) ($row['candidate_code'] ?? '');
                $contractErrors = (int) ($errorsByCandidate[$role][$candidate] ?? 0);
                $reasons = $this->exclusionReasons($row, $contractErrors);
                if ($reasons !== []) {
                    $excluded[] = [
                        'candidate_code' => $candidate,
                        'reasons' => $reasons,
                        'contract_error_count' => $contractErrors,
                    ] + (isset($row['model_label']) ? ['model_label' => (string) $row['model_label']] : []);
                    continue;
                }
                if ($best === null) {
                    $best = $this->candidateSummary($row);
                }
            }
            if ($best === null && $rows !== []) {
                $best = $this->candidateSummary($rows[0]) + ['review_required' => true];
            }
            $recommendations[] = [
                'role_name' => $role,
                'best_candidate' => $best,
                'excluded_candidates' => $excluded,
                'contract_errors' => $errorsByRole[$role] ?? new \stdClass(),
            ];
        }

        return $recommendations;
    }

    private function recommendationScore(array $row): float
    {
        $quality = $row['quality_avg'] === null ? 0.0 : (float) $row['quality_avg'];
        $task = $row['task_solution_avg'] === null ? 0.0 : (float) $row['task_solution_avg'];
        $duration = $row['duration_score_avg'] === null ? 0.0 : (float) $row['duration_score_avg'];
        return ($quality * 0.45) + ($task * 0.45) + ($duration * 0.10)
            - ((int) ($row['major_logic_errors'] ?? 0) * 0.75)
            - ((int) ($row['minor_logic_errors'] ?? 0) * 0.20);
    }

    private function exclusionReasons(array $row, int $contractErrors): array
    {
        $reasons = [];
        if ((int) ($row['major_logic_errors'] ?? 0) > 0) {
            $reasons[] = 'major_logic_errors';
        }
        if ($contractErrors > 0) {
            $reasons[] = 'contract_errors';
        }
        if ($row['quality_avg'] !== null && (float) $row['quality_avg'] < 3.0) {
            $reasons[] = 'low_quality';
        }
        if ($row['task_solution_avg'] !== null && (float) $row['task_solution_avg'] < 3.0) {
            $reasons[] = 'low_task_solution';
        }
        return $reasons;
    }

    private function candidateSummary(array $row): array
    {
        $summary = [
            'candidate_code' => (string) ($row['candidate_code'] ?? ''),
            'feedback_count' => (int) ($row['feedback_count'] ?? 0),
            'score' => round($this->recommendationScore($row), 3),
            'quality_avg' => $row['quality_avg'] ?? null,
            'task_solution_avg' => $row['task_solution_avg'] ?? null,
            'duration_score_avg' => $row['duration_score_avg'] ?? null,
            'major_logic_errors' => (int) ($row['major_logic_errors'] ?? 0),
            'minor_logic_errors' => (int) ($row['minor_logic_errors'] ?? 0),
            'error_observations' => (int) ($row['error_observations'] ?? 0),
        ];
        if (isset($row['model_label'])) {
            $summary['model_label'] = (string) $row['model_label'];
        }
        return $summary;
    }

    private function exportObservation(array $row, bool $includeModelLabel): array
    {
        $export = [
            'observation_id' => (int) $row['id'],
            'run_key' => (string) $row['run_key'],
            'candidate_code' => $this->candidateCode($row),
            'case_key' => (string) $row['case_key'],
            'area' => (string) $row['area'],
            'role_name' => (string) $row['role_name'],
            'task_label' => (string) $row['task_label'],
            'task_summary' => (string) $row['task_summary'],
            'quality_question' => (string) $row['quality_question'],
            'duration_ms' => $row['duration_ms'] === null ? null : (int) $row['duration_ms'],
            'input_tokens' => $row['input_tokens'] === null ? null : (int) $row['input_tokens'],
            'thinking_tokens' => $row['thinking_tokens'] === null ? null : (int) $row['thinking_tokens'],
            'output_tokens' => $row['output_tokens'] === null ? null : (int) $row['output_tokens'],
            'total_tokens' => $row['total_tokens'] === null ? null : (int) $row['total_tokens'],
            'model_usage_level' => (string) ($row['model_usage_level'] ?? ''),
            'model_usage_weight' => $row['model_usage_weight'] === null ? null : (int) $row['model_usage_weight'],
            'weighted_token_units' => $row['weighted_token_units'] === null ? null : (int) $row['weighted_token_units'],
            'estimated_usage_units' => $row['estimated_usage_units'] === null ? null : (string) $row['estimated_usage_units'],
            'artifact_ref' => (string) ($row['artifact_ref'] ?? ''),
            'position_fen' => (string) ($row['position_fen'] ?? ''),
            'side_to_move' => (string) ($row['side_to_move'] ?? ''),
            'position_label' => (string) ($row['position_label'] ?? ''),
            'task_query' => (string) ($row['task_query'] ?? ''),
            'system_prompt_excerpt' => (string) ($row['system_prompt_excerpt'] ?? ''),
            'task_prompt_excerpt' => (string) ($row['task_prompt_excerpt'] ?? ''),
            'expected_output_type' => (string) ($row['expected_output_type'] ?? ''),
            'candidate_moves_excerpt' => (string) ($row['candidate_moves_excerpt'] ?? ''),
            'error_status' => (string) ($row['error_status'] ?? ''),
            'error_message' => (string) ($row['error_message'] ?? ''),
            'output_excerpt' => (string) ($row['output_excerpt'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
        if ($includeModelLabel) {
            $export['model_label'] = (string) $row['model_label'];
        }
        return $export;
    }

    private function candidateCode(array $row): string
    {
        $digest = substr(hash(
            'sha256',
            (string) ($row['run_key'] ?? '') . '|' .
            (string) ($row['case_key'] ?? '') . '|' .
            (string) ($row['model_label'] ?? '') . '|' .
            (string) ($row['id'] ?? '')
        ), 0, 6);
        return strtoupper($digest);
    }

    private function normalizeFeedback(array $row): array
    {
        $observationId = $this->requiredPositiveInt($row, 'observation_id');
        $logicErrorLevel = $this->enumField($row, 'logic_error_level', ['none', 'minor', 'major', 'unknown'], 'unknown');
        $preferredOption = $this->enumField($row, 'preferred_option', ['a', 'b', 'tie', 'not_applicable'], 'not_applicable');

        return [
            'observation_id' => $observationId,
            'quality_score' => $this->requiredScore($row, 'quality_score'),
            'task_solution_score' => $this->requiredScore($row, 'task_solution_score'),
            'duration_score' => $this->requiredScore($row, 'duration_score'),
            'logic_error_level' => $logicErrorLevel,
            'preferred_option' => $preferredOption,
            'translation_score' => $this->optionalScore($row, 'translation_score'),
            'feedback_text' => $this->optionalNullableText($row, 'feedback_text', 5000),
            'improvement_note' => $this->optionalNullableText($row, 'improvement_note', 5000),
            'translation_note' => $this->optionalNullableText($row, 'translation_note', 5000),
        ];
    }

    private function loadFeedbackObservation(PDO $pdo, int $observationId): ?array
    {
        $statement = $pdo->prepare(
            "SELECT
                o.id,
                o.case_id,
                o.run_key,
                o.model_label,
                o.duration_ms,
                o.input_tokens,
                o.thinking_tokens,
                o.output_tokens,
                o.total_tokens,
                o.model_usage_level,
                o.model_usage_weight,
                o.weighted_token_units,
                o.estimated_usage_units,
                c.case_key,
                c.role_name,
                c.task_label
             FROM cailama_model_benchmark_observations o
             INNER JOIN cailama_model_benchmark_cases c ON c.id = o.case_id
             WHERE o.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $observationId]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    private function upsertApiFeedback(PDO $pdo, array $observation, array $values): void
    {
        $find = $pdo->prepare(
            "SELECT id
             FROM cailama_model_feedback
             WHERE observation_id = :observation_id
               AND user_id IS NULL
             ORDER BY id DESC
             LIMIT 1"
        );
        $find->execute(['observation_id' => (int) $observation['id']]);
        $existingId = $find->fetchColumn();

        $params = [
            'observation_id' => (int) $observation['id'],
            'case_id' => (int) $observation['case_id'],
            'user_id' => null,
            'run_key' => (string) $observation['run_key'],
            'model_label' => (string) $observation['model_label'],
            'duration_ms' => $observation['duration_ms'],
            'input_tokens' => $observation['input_tokens'],
            'thinking_tokens' => $observation['thinking_tokens'],
            'output_tokens' => $observation['output_tokens'],
            'total_tokens' => $observation['total_tokens'],
            'model_usage_level' => $observation['model_usage_level'],
            'model_usage_weight' => $observation['model_usage_weight'],
            'weighted_token_units' => $observation['weighted_token_units'],
            'estimated_usage_units' => $observation['estimated_usage_units'],
            'quality_score' => $values['quality_score'],
            'task_solution_score' => $values['task_solution_score'],
            'duration_score' => $values['duration_score'],
            'logic_error_level' => $values['logic_error_level'],
            'preferred_option' => $values['preferred_option'],
            'translation_score' => $values['translation_score'],
            'feedback_text' => $values['feedback_text'],
            'improvement_note' => $values['improvement_note'],
            'translation_note' => $values['translation_note'],
        ];

        if ($existingId !== false) {
            $statement = $pdo->prepare(
                "UPDATE cailama_model_feedback
                 SET observation_id = :observation_id,
                     case_id = :case_id,
                     user_id = :user_id,
                     run_key = :run_key,
                     model_label = :model_label,
                     duration_ms = :duration_ms,
                     input_tokens = :input_tokens,
                     thinking_tokens = :thinking_tokens,
                     output_tokens = :output_tokens,
                     total_tokens = :total_tokens,
                     model_usage_level = :model_usage_level,
                     model_usage_weight = :model_usage_weight,
                     weighted_token_units = :weighted_token_units,
                     estimated_usage_units = :estimated_usage_units,
                     quality_score = :quality_score,
                     task_solution_score = :task_solution_score,
                     duration_score = :duration_score,
                     logic_error_level = :logic_error_level,
                     preferred_option = :preferred_option,
                     translation_score = :translation_score,
                     feedback_text = :feedback_text,
                     improvement_note = :improvement_note,
                     translation_note = :translation_note
                 WHERE id = :id"
            );
            $params['id'] = (int) $existingId;
            $statement->execute($params);
            return;
        }

        $statement = $pdo->prepare(
            "INSERT INTO cailama_model_feedback
                (observation_id, case_id, user_id, run_key, model_label, duration_ms, input_tokens, thinking_tokens, output_tokens,
                 total_tokens, model_usage_level, model_usage_weight, weighted_token_units, estimated_usage_units,
                 quality_score, task_solution_score, duration_score, logic_error_level, preferred_option, translation_score,
                 feedback_text, improvement_note, translation_note)
             VALUES
                (:observation_id, :case_id, :user_id, :run_key, :model_label, :duration_ms, :input_tokens, :thinking_tokens, :output_tokens,
                 :total_tokens, :model_usage_level, :model_usage_weight, :weighted_token_units, :estimated_usage_units,
                 :quality_score, :task_solution_score, :duration_score, :logic_error_level, :preferred_option, :translation_score,
                 :feedback_text, :improvement_note, :translation_note)"
        );
        $statement->execute($params);
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
        if (!in_array($status, self::AUTO_CLOSE_ERROR_STATUSES, true)) {
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
            'invalid_json' => 'Automatisch: JSON-/Formatvertrag fehlgeschlagen.',
            'missing_required_field' => 'Automatisch: Pflichtfeld im Output fehlt.',
            'unexpected_tool' => 'Automatisch: Unerwarteter Tool-Aufruf.',
            'invalid_tool_call' => 'Automatisch: Tool-Aufruf ist nicht parsebar.',
            'missing_tool' => 'Automatisch: Erwartetes Tool wurde nicht genutzt.',
            'tool_argument_mismatch' => 'Automatisch: Tool-Argumente passen nicht zum Vertrag.',
            'wrong_role' => 'Automatisch: Router wählte die falsche Rolle.',
            'routing_role_mismatch' => 'Automatisch: Routing-Rolle passt nicht zum erwarteten Intent.',
            'routing_tool_mismatch' => 'Automatisch: Erwartete Routing-Tools fehlen oder passen nicht.',
            'boardtruth_conflict' => 'Automatisch: Antwort widerspricht der BoardTruth.',
            'empty_optional_field_reference' => 'Automatisch: Antwort referenziert ein leeres optionales Feld.',
            'guessed_fen' => 'Automatisch: Antwort erfindet oder rät eine FEN.',
            'rendered_board' => 'Automatisch: Antwort zeichnet statt referenziert ein Brett.',
            'missing_citation' => 'Automatisch: Quellen-/Provenienzvertrag fehlt.',
            'fake_engine_claim' => 'Automatisch: Nicht belegte Engine-Gewissheit.',
            'invalid_quality_band' => 'Automatisch: Unbekanntes Qualitätsband.',
            'empty_output' => 'Automatisch: Antwort fehlt.',
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

    private function optionalBoundedInt(array $row, string $key, int $min, int $max, int $default): int
    {
        $value = $row[$key] ?? null;
        if ($value === null || $value === '') {
            return $default;
        }
        if (!is_int($value) && !(is_string($value) && preg_match('/^\d+$/', $value))) {
            throw new \InvalidArgumentException('Invalid integer field: ' . $key);
        }
        return max($min, min((int) $value, $max));
    }

    private function requiredPositiveInt(array $row, string $key): int
    {
        $value = $this->nullableInt($row, $key);
        if ($value === null || $value < 1) {
            throw new \InvalidArgumentException('Invalid integer field: ' . $key);
        }
        return $value;
    }

    private function requiredScore(array $row, string $key): int
    {
        $value = $this->nullableInt($row, $key);
        if ($value === null || $value < 1 || $value > 5) {
            throw new \InvalidArgumentException('Invalid score field: ' . $key);
        }
        return $value;
    }

    private function optionalScore(array $row, string $key): ?int
    {
        $value = $this->nullableInt($row, $key);
        if ($value === null) {
            return null;
        }
        if ($value < 1 || $value > 5) {
            throw new \InvalidArgumentException('Invalid score field: ' . $key);
        }
        return $value;
    }

    private function enumField(array $row, string $key, array $allowed, string $default): string
    {
        $value = $this->optionalString($row, $key, 32);
        if ($value === '') {
            return $default;
        }
        if (!in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid enum field: ' . $key);
        }
        return $value;
    }

    private function optionalNullableText(array $row, string $key, int $maxLength): ?string
    {
        $value = $this->optionalString($row, $key, $maxLength);
        return $value === '' ? null : $value;
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
