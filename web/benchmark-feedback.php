<?php
declare(strict_types=1);

use CaiLama\WebApi\Auth\SessionManager;
use CaiLama\WebApi\Db\ConnectionFactory;

$config = require __DIR__ . '/api_app/init.php';
$session = new SessionManager($config['session'] ?? []);
$session->start();
$user = $session->currentUser();

if ($user === null) {
    header('Location: login.php', true, 303);
    exit;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post_string(string $key, int $maxLength): string
{
    $value = is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
    if (strlen($value) > $maxLength) {
        return substr($value, 0, $maxLength);
    }
    return $value;
}

function post_int_or_null(string $key): ?int
{
    $value = is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
    if ($value === '') {
        return null;
    }
    if (!preg_match('/^\d+$/', $value)) {
        return null;
    }
    return min((int) $value, 2147483647);
}

function query_int_or_null(string $key): ?int
{
    $value = is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
    if ($value === '') {
        return null;
    }
    if (!preg_match('/^\d+$/', $value)) {
        return null;
    }
    return min((int) $value, 2147483647);
}

function post_score(string $key): ?int
{
    $value = post_int_or_null($key);
    if ($value === null || $value < 1 || $value > 5) {
        return null;
    }
    return $value;
}

function nullable_text(string $value): ?string
{
    return $value === '' ? null : $value;
}

function candidate_label_from_values(string $runKey, string $caseKey, string $modelLabel, string $id = ''): string
{
    $digest = strtoupper(substr(hash('sha256', $runKey . '|' . $caseKey . '|' . $modelLabel . '|' . $id), 0, 6));
    return 'Kandidat ' . $digest;
}

function candidate_label(array $row): string
{
    return candidate_label_from_values(
        (string) ($row['run_key'] ?? 'feedback-summary'),
        (string) ($row['case_key'] ?? ''),
        (string) ($row['model_label'] ?? ''),
        (string) ($row['id'] ?? '')
    );
}

function load_benchmark_data(PDO $pdo): array
{
    $cases = $pdo->query(
        "SELECT id, case_key, area, role_name, model_a, model_b, task_label, task_summary, quality_question, status
         FROM cailama_model_benchmark_cases
         WHERE status <> 'archived'
         ORDER BY FIELD(status, 'active', 'hypothesis'), area, role_name, task_label"
    )->fetchAll();

    $summary = $pdo->query(
        "SELECT
            c.case_key,
            c.task_label,
            c.role_name,
            f.model_label,
            COUNT(*) AS feedback_count,
            ROUND(AVG(f.quality_score), 2) AS quality_avg,
            ROUND(AVG(f.task_solution_score), 2) AS task_solution_avg,
            ROUND(AVG(f.duration_ms), 0) AS duration_avg,
            SUM(COALESCE(f.thinking_tokens, 0)) AS thinking_tokens_total,
            SUM(COALESCE(f.output_tokens, 0)) AS output_tokens_total
         FROM cailama_model_feedback f
         INNER JOIN cailama_model_benchmark_cases c ON c.id = f.case_id
         GROUP BY c.case_key, c.task_label, c.role_name, f.model_label
         ORDER BY c.role_name, c.task_label, f.model_label"
    )->fetchAll();

    $recent = $pdo->query(
        "SELECT
            c.case_key,
            c.task_label,
            c.role_name,
            f.model_label,
            f.quality_score,
            f.task_solution_score,
            f.logic_error_level,
            f.preferred_option,
            f.duration_ms,
            f.thinking_tokens,
            f.output_tokens,
            f.created_at
         FROM cailama_model_feedback f
         INNER JOIN cailama_model_benchmark_cases c ON c.id = f.case_id
         ORDER BY f.created_at DESC
         LIMIT 12"
    )->fetchAll();

    $observations = $pdo->query(
        "SELECT
            o.id,
            o.run_key,
            o.model_label,
            o.duration_ms,
            o.input_tokens,
            o.thinking_tokens,
            o.output_tokens,
            o.artifact_ref,
            o.output_excerpt,
            o.created_at,
            c.id AS case_id,
            c.case_key,
            c.task_label,
            c.task_summary,
            c.role_name,
            c.quality_question
         FROM cailama_model_benchmark_observations o
         INNER JOIN cailama_model_benchmark_cases c ON c.id = o.case_id
         ORDER BY o.run_key DESC, c.role_name, MD5(CONCAT(o.run_key, c.case_key, o.model_label, o.id))
         LIMIT 30"
    )->fetchAll();

    return [$cases, $summary, $recent, $observations];
}

function load_benchmark_observation(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare(
        "SELECT
            o.id,
            o.run_key,
            o.model_label,
            o.duration_ms,
            o.input_tokens,
            o.thinking_tokens,
            o.output_tokens,
            o.artifact_ref,
            o.output_excerpt,
            o.created_at,
            c.id AS case_id,
            c.case_key,
            c.task_label,
            c.task_summary,
            c.role_name,
            c.quality_question
         FROM cailama_model_benchmark_observations o
         INNER JOIN cailama_model_benchmark_cases c ON c.id = o.case_id
         WHERE o.id = :id"
    );
    $statement->execute(['id' => $id]);
    $loaded = $statement->fetch();
    return is_array($loaded) ? $loaded : null;
}

$pdo = null;
$loadError = null;
$errors = [];
$cases = [];
$summary = [];
$recent = [];
$observations = [];
$selectedObservation = null;

try {
    $pdo = ConnectionFactory::fromConfig($config, 'cailama');
    [$cases, $summary, $recent, $observations] = load_benchmark_data($pdo);
    $selectedObservationId = query_int_or_null('observation_id');
    if ($selectedObservationId !== null) {
        $selectedObservation = load_benchmark_observation($pdo, $selectedObservationId);
    }
} catch (Throwable) {
    $loadError = 'Die Benchmark-Datenbank ist noch nicht bereit. Bitte zuerst das aktuelle Datenbankschema einspielen.';
}

if ($pdo !== null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null;
    $observationId = post_int_or_null('observation_id');
    $postedObservation = null;
    $caseId = post_int_or_null('case_id');
    $knownCaseIds = array_map(static fn (array $case): int => (int) $case['id'], $cases);
    $modelLabel = post_string('model_label', 120);
    $qualityScore = post_score('quality_score');
    $taskSolutionScore = post_score('task_solution_score');
    $logicErrorLevel = post_string('logic_error_level', 16);
    $preferredOption = post_string('preferred_option', 16);

    if (!$session->validateCsrf($token)) {
        $errors[] = 'Die Eingabe konnte nicht verarbeitet werden.';
    }
    if ($observationId !== null) {
        $postedObservation = load_benchmark_observation($pdo, $observationId);
        if ($postedObservation === null) {
            $errors[] = 'Der Benchmark-Lauf konnte nicht gefunden werden.';
        } else {
            $selectedObservation = $postedObservation;
            $caseId = (int) $postedObservation['case_id'];
            $modelLabel = (string) $postedObservation['model_label'];
        }
    }
    if ($caseId === null || !in_array($caseId, $knownCaseIds, true)) {
        $errors[] = 'Bitte einen gültigen Benchmark-Fall auswählen.';
    }
    if ($observationId === null && $modelLabel === '') {
        $errors[] = 'Bitte Modell oder Option angeben.';
    }
    if ($qualityScore === null) {
        $errors[] = 'Bitte die Qualitätsbewertung setzen.';
    }
    if ($taskSolutionScore === null) {
        $errors[] = 'Bitte die Aufgabenbewertung setzen.';
    }
    if (!in_array($logicErrorLevel, ['none', 'minor', 'major', 'unknown'], true)) {
        $errors[] = 'Bitte die Logikfehler-Einschätzung setzen.';
    }
    if (!in_array($preferredOption, ['a', 'b', 'tie', 'not_applicable'], true)) {
        $errors[] = 'Bitte die A/B-Präferenz setzen.';
    }

    if ($errors === []) {
        $userId = isset($user['id']) && ctype_digit((string) $user['id']) ? (int) $user['id'] : null;
        try {
            $statement = $pdo->prepare(
                "INSERT INTO cailama_model_feedback
                    (case_id, user_id, model_label, duration_ms, input_tokens, thinking_tokens, output_tokens,
                     quality_score, task_solution_score, logic_error_level, preferred_option, feedback_text, improvement_note)
                 VALUES
                    (:case_id, :user_id, :model_label, :duration_ms, :input_tokens, :thinking_tokens, :output_tokens,
                     :quality_score, :task_solution_score, :logic_error_level, :preferred_option, :feedback_text, :improvement_note)"
            );
            $statement->execute([
                'case_id' => $caseId,
                'user_id' => $userId,
                'model_label' => $modelLabel,
                'duration_ms' => post_int_or_null('duration_ms'),
                'input_tokens' => post_int_or_null('input_tokens'),
                'thinking_tokens' => post_int_or_null('thinking_tokens'),
                'output_tokens' => post_int_or_null('output_tokens'),
                'quality_score' => $qualityScore,
                'task_solution_score' => $taskSolutionScore,
                'logic_error_level' => $logicErrorLevel,
                'preferred_option' => $preferredOption,
                'feedback_text' => nullable_text(post_string('feedback_text', 5000)),
                'improvement_note' => nullable_text(post_string('improvement_note', 5000)),
            ]);
            header('Location: benchmark-feedback.php?saved=1', true, 303);
            exit;
        } catch (Throwable) {
            $errors[] = 'Das Feedback konnte nicht gespeichert werden.';
        }
    }
}

$feedbackCount = array_sum(array_map(static fn (array $row): int => (int) $row['feedback_count'], $summary));
$formCaseId = is_string($_POST['case_id'] ?? null)
    ? (string) $_POST['case_id']
    : (string) ($selectedObservation['case_id'] ?? '');
$formModelLabel = $selectedObservation === null && is_string($_POST['model_label'] ?? null)
    ? (string) $_POST['model_label']
    : '';
$formDurationMs = is_string($_POST['duration_ms'] ?? null)
    ? (string) $_POST['duration_ms']
    : (string) ($selectedObservation['duration_ms'] ?? '');
$formInputTokens = is_string($_POST['input_tokens'] ?? null)
    ? (string) $_POST['input_tokens']
    : (string) ($selectedObservation['input_tokens'] ?? '');
$formThinkingTokens = is_string($_POST['thinking_tokens'] ?? null)
    ? (string) $_POST['thinking_tokens']
    : (string) ($selectedObservation['thinking_tokens'] ?? '');
$formOutputTokens = is_string($_POST['output_tokens'] ?? null)
    ? (string) $_POST['output_tokens']
    : (string) ($selectedObservation['output_tokens'] ?? '');
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Geschütztes CaiLama Benchmark-Feedback">
  <meta name="robots" content="noindex,nofollow">
  <title>CaiLama - Benchmark-Feedback</title>
  <link rel="canonical" href="https://cailama.org/benchmark-feedback.php">
  <link rel="stylesheet" href="assets/styles.css">
  <link rel="icon" href="./favicon.ico" type="image/x-icon">
</head>
<body>
  <header class="site-header">
    <nav class="nav" aria-label="Hauptnavigation">
      <a class="brand" href="index.php">
        <img src="https://raw.githubusercontent.com/TotoBa/CaiLama/main/img/logo-small.png" alt="">
        <span>CaiLama</span>
      </a>
      <div class="nav-links">
        <a href="status.php">Status</a>
        <a href="projects.php">Projekte</a>
        <a href="architecture.php">Architektur</a>
        <a href="roadmap.php">Roadmap</a>
        <a href="operations.php">Betrieb</a>
        <a href="reference.php">Referenz</a>
        <a aria-current="page" href="account.php">Konto</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="page-hero">
      <div class="page-hero-inner">
        <p class="eyebrow">Geschützter Arbeitsbereich</p>
        <h1>Benchmark-Feedback</h1>
        <p class="page-lead">Bewertungen zu Modellrollen, Laufzeiten, Thinking-Tokens, Output-Tokens und Qualitätsurteilen werden in der CaiLama-Datenbank gesammelt.</p>
      </div>
    </section>

    <section>
      <div class="section-inner feedback-layout">
        <?php if (isset($_GET['saved'])): ?>
          <p class="notice" role="status">Feedback wurde gespeichert.</p>
        <?php endif; ?>
        <?php if ($loadError !== null): ?>
          <p class="notice error" role="alert"><?= h($loadError) ?></p>
        <?php else: ?>
          <?php foreach ($errors as $error): ?>
            <p class="notice error" role="alert"><?= h($error) ?></p>
          <?php endforeach; ?>

          <div class="grid-3 compact-metrics">
            <div class="metric">
              <strong><?= h((string) count($cases)) ?></strong>
              <p>aktive Benchmark-Fälle</p>
            </div>
            <div class="metric">
              <strong><?= h((string) $feedbackCount) ?></strong>
              <p>gespeicherte Feedbacks</p>
            </div>
            <div class="metric">
              <strong><?= h((string) count($observations)) ?></strong>
              <p>importierte Laufdaten</p>
            </div>
          </div>

          <?php if ($observations !== []): ?>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Zeitpunkt</th>
                    <th>Benchmark-Lauf</th>
                    <th>Kandidat</th>
                    <th>Metriken</th>
                    <th>Auszug</th>
                    <th>Aktion</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($observations as $observation): ?>
                    <tr>
                      <td><?= h((string) $observation['created_at']) ?></td>
                      <td><strong><?= h((string) $observation['role_name']) ?></strong><br><?= h((string) $observation['task_label']) ?><br><span class="muted"><?= h((string) $observation['run_key']) ?></span></td>
                      <td><?= h(candidate_label($observation)) ?></td>
                      <td>Dauer <?= h((string) ($observation['duration_ms'] ?? '-')) ?> ms<br>Input <?= h((string) ($observation['input_tokens'] ?? '-')) ?><br>Thinking <?= h((string) ($observation['thinking_tokens'] ?? '-')) ?><br>Output <?= h((string) ($observation['output_tokens'] ?? '-')) ?></td>
                      <td><?= h((string) ($observation['output_excerpt'] ?? '')) ?></td>
                      <td><a class="button light" href="benchmark-feedback.php?observation_id=<?= h((string) $observation['id']) ?>">Bewerten</a></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

          <?php if ($selectedObservation !== null): ?>
            <p class="notice" role="status">Benchmark-Lauf vorausgefüllt: <?= h(candidate_label($selectedObservation)) ?> · <?= h((string) $selectedObservation['task_label']) ?></p>
          <?php endif; ?>

          <form class="auth-panel feedback-form" method="post" action="benchmark-feedback.php">
            <input type="hidden" name="csrf_token" value="<?= h($session->csrfToken()) ?>">
            <?php if ($selectedObservation !== null): ?>
              <input type="hidden" name="observation_id" value="<?= h((string) $selectedObservation['id']) ?>">
              <div>
                <label for="blind_candidate">Kandidat</label>
                <input id="blind_candidate" type="text" readonly value="<?= h(candidate_label($selectedObservation)) ?>">
              </div>
              <div>
                <label for="selected_case">Benchmark-Fall</label>
                <input id="selected_case" type="text" readonly value="<?= h((string) $selectedObservation['role_name'] . ' - ' . (string) $selectedObservation['task_label']) ?>">
              </div>
            <?php else: ?>
              <div>
                <label for="case_id">Benchmark-Fall</label>
                <select id="case_id" name="case_id" required>
                  <option value="">Bitte auswählen</option>
                  <?php foreach ($cases as $case): ?>
                    <option value="<?= h((string) $case['id']) ?>" <?= ($formCaseId === (string) $case['id']) ? 'selected' : '' ?>>
                      <?= h((string) $case['role_name'] . ' - ' . (string) $case['task_label']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label for="model_label">Kandidat oder Option</label>
                <input id="model_label" name="model_label" maxlength="120" required value="<?= h($formModelLabel) ?>" placeholder="z. B. Kandidat A oder Option A">
              </div>
            <?php endif; ?>

            <div class="form-grid">
              <div>
                <label for="quality_score">Qualität</label>
                <select id="quality_score" name="quality_score" required>
                  <option value="">-</option>
                  <?php for ($score = 1; $score <= 5; $score++): ?>
                    <option value="<?= h((string) $score) ?>" <?= ((string) ($_POST['quality_score'] ?? '') === (string) $score) ? 'selected' : '' ?>><?= h((string) $score) ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div>
                <label for="task_solution_score">Aufgabe gelöst</label>
                <select id="task_solution_score" name="task_solution_score" required>
                  <option value="">-</option>
                  <?php for ($score = 1; $score <= 5; $score++): ?>
                    <option value="<?= h((string) $score) ?>" <?= ((string) ($_POST['task_solution_score'] ?? '') === (string) $score) ? 'selected' : '' ?>><?= h((string) $score) ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div>
                <label for="logic_error_level">Logikfehler</label>
                <select id="logic_error_level" name="logic_error_level" required>
                  <?php
                  $logicOptions = [
                      'unknown' => 'unklar',
                      'none' => 'keine',
                      'minor' => 'klein',
                      'major' => 'schwer',
                  ];
                  foreach ($logicOptions as $value => $label):
                  ?>
                    <option value="<?= h($value) ?>" <?= ((string) ($_POST['logic_error_level'] ?? 'unknown') === $value) ? 'selected' : '' ?>><?= h($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label for="preferred_option">A/B-Präferenz</label>
                <select id="preferred_option" name="preferred_option" required>
                  <?php
                  $preferenceOptions = [
                      'not_applicable' => 'nicht anwendbar',
                      'a' => 'Option A',
                      'b' => 'Option B',
                      'tie' => 'gleich gut',
                  ];
                  foreach ($preferenceOptions as $value => $label):
                  ?>
                    <option value="<?= h($value) ?>" <?= ((string) ($_POST['preferred_option'] ?? 'not_applicable') === $value) ? 'selected' : '' ?>><?= h($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-grid">
              <div>
                <label for="duration_ms">Dauer in ms</label>
                <input id="duration_ms" name="duration_ms" type="number" inputmode="numeric" min="0" step="1" value="<?= h($formDurationMs) ?>">
              </div>
              <div>
                <label for="input_tokens">Input-Tokens</label>
                <input id="input_tokens" name="input_tokens" type="number" inputmode="numeric" min="0" step="1" value="<?= h($formInputTokens) ?>">
              </div>
              <div>
                <label for="thinking_tokens">Thinking-Tokens</label>
                <input id="thinking_tokens" name="thinking_tokens" type="number" inputmode="numeric" min="0" step="1" value="<?= h($formThinkingTokens) ?>">
              </div>
              <div>
                <label for="output_tokens">Output-Tokens</label>
                <input id="output_tokens" name="output_tokens" type="number" inputmode="numeric" min="0" step="1" value="<?= h($formOutputTokens) ?>">
              </div>
            </div>

            <div>
              <label for="feedback_text">Feedback</label>
              <textarea id="feedback_text" name="feedback_text" rows="5" maxlength="5000" placeholder="Was war gut, schlecht oder fachlich falsch?"><?= h(is_string($_POST['feedback_text'] ?? null) ? $_POST['feedback_text'] : '') ?></textarea>
            </div>
            <div>
              <label for="improvement_note">Verbesserungshinweis</label>
              <textarea id="improvement_note" name="improvement_note" rows="4" maxlength="5000" placeholder="Welche Regel, Gewichtung oder Prompt-Änderung ergibt sich daraus?"><?= h(is_string($_POST['improvement_note'] ?? null) ? $_POST['improvement_note'] : '') ?></textarea>
            </div>
            <button class="button primary form-button" type="submit">Feedback speichern</button>
          </form>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Fall</th>
                  <th>Qualitätsfrage</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cases as $case): ?>
                  <tr>
                    <td><strong><?= h((string) $case['role_name']) ?></strong><br><?= h((string) $case['task_label']) ?><br><span class="muted"><?= h((string) $case['task_summary']) ?></span></td>
                    <td><?= h((string) $case['quality_question']) ?></td>
                    <td><span class="status-pill"><?= h((string) $case['status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <?php if ($summary !== []): ?>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Fall</th>
                    <th>Kandidat</th>
                    <th>Feedbacks</th>
                    <th>Ø Qualität</th>
                    <th>Ø Aufgabe</th>
                    <th>Ø Dauer</th>
                    <th>Tokens</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($summary as $row): ?>
                    <tr>
                      <td><?= h((string) $row['role_name']) ?><br><?= h((string) $row['task_label']) ?></td>
                      <td><?= h(candidate_label($row)) ?></td>
                      <td><?= h((string) $row['feedback_count']) ?></td>
                      <td><?= h((string) $row['quality_avg']) ?></td>
                      <td><?= h((string) $row['task_solution_avg']) ?></td>
                      <td><?= h((string) ($row['duration_avg'] ?? '-')) ?> ms</td>
                      <td>Thinking <?= h((string) $row['thinking_tokens_total']) ?><br>Output <?= h((string) $row['output_tokens_total']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

          <?php if ($recent !== []): ?>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Zeitpunkt</th>
                    <th>Fall</th>
                    <th>Kandidat</th>
                    <th>Bewertung</th>
                    <th>Tokens</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent as $row): ?>
                    <tr>
                      <td><?= h((string) $row['created_at']) ?></td>
                      <td><?= h((string) $row['role_name']) ?><br><?= h((string) $row['task_label']) ?></td>
                      <td><?= h(candidate_label($row)) ?></td>
                      <td>Qualität <?= h((string) $row['quality_score']) ?>, Aufgabe <?= h((string) $row['task_solution_score']) ?><br>Logik <?= h((string) $row['logic_error_level']) ?>, A/B <?= h((string) $row['preferred_option']) ?></td>
                      <td>Dauer <?= h((string) ($row['duration_ms'] ?? '-')) ?> ms<br>Thinking <?= h((string) ($row['thinking_tokens'] ?? '-')) ?>, Output <?= h((string) ($row['output_tokens'] ?? '-')) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama - Schachtraining als System</span>
      <a href="https://github.com/TotoBa/CaiLama-Master">Master-Repository</a>
      <a href="mailto:info@cailama.org">Kontakt</a>
    </div>
  </footer>
</body>
</html>
