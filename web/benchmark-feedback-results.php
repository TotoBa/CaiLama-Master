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

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function query_string(string $key, int $maxLength): string
{
    $value = is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
    if (strlen($value) > $maxLength) {
        return substr($value, 0, $maxLength);
    }
    return $value;
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

function filter_clause(array $filters, array &$params, string $prefix = ''): string
{
    $clauses = [];
    if ($filters['run_key'] !== '') {
        $clauses[] = $prefix . 'run_key = :run_key';
        $params['run_key'] = $filters['run_key'];
    }
    if ($filters['role_name'] !== '') {
        $clauses[] = 'c.role_name = :role_name';
        $params['role_name'] = $filters['role_name'];
    }
    if ($filters['case_key'] !== '') {
        $clauses[] = 'c.case_key = :case_key';
        $params['case_key'] = $filters['case_key'];
    }
    if ($filters['error_status'] !== '') {
        $clauses[] = $prefix . 'error_status = :error_status';
        $params['error_status'] = $filters['error_status'];
    }
    return $clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses);
}

function load_results(PDO $pdo, array $filters): array
{
    $params = [];
    $where = filter_clause($filters, $params, 'o.');

    $summarySql =
        "SELECT
            o.run_key,
            c.case_key,
            c.role_name,
            c.task_label,
            o.model_label,
            MIN(o.id) AS id,
            COUNT(DISTINCT o.id) AS observation_count,
            COUNT(f.id) AS feedback_count,
            ROUND(AVG(f.quality_score), 2) AS quality_avg,
            ROUND(AVG(f.task_solution_score), 2) AS task_solution_avg,
            ROUND(AVG(f.duration_score), 2) AS duration_score_avg,
            ROUND(AVG(f.translation_score), 2) AS translation_avg,
            SUM(f.logic_error_level = 'major') AS major_logic_errors,
            SUM(o.error_status <> '') AS error_observations,
            ROUND(AVG(o.duration_ms), 0) AS duration_avg,
            ROUND(AVG(o.thinking_tokens), 0) AS thinking_avg,
            ROUND(AVG(o.output_tokens), 0) AS output_avg,
            ROUND(AVG(o.total_tokens), 0) AS total_avg,
            MAX(o.model_usage_level) AS usage_level,
            SUM(o.weighted_token_units) AS weighted_token_units_sum,
            ROUND(SUM(o.estimated_usage_units), 3) AS estimated_usage_units_sum
         FROM cailama_model_benchmark_observations o
         INNER JOIN cailama_model_benchmark_cases c ON c.id = o.case_id
         LEFT JOIN cailama_model_feedback f ON f.observation_id = o.id"
        . $where .
        " GROUP BY o.run_key, c.case_key, c.role_name, c.task_label, o.model_label
          ORDER BY o.run_key DESC, c.role_name, c.task_label, MD5(CONCAT(o.run_key, c.case_key, o.model_label))
          LIMIT 200";
    $summary = $pdo->prepare($summarySql);
    $summary->execute($params);

    $feedbackParams = [];
    $feedbackWhere = filter_clause($filters, $feedbackParams, 'o.');
    $feedbackSql =
        "SELECT
            o.id,
            o.run_key,
            o.model_label,
            o.error_status,
            o.error_message,
            o.total_tokens,
            o.model_usage_level,
            o.weighted_token_units,
            o.estimated_usage_units,
            c.case_key,
            c.role_name,
            c.task_label,
            f.quality_score,
            f.task_solution_score,
            f.duration_score,
            f.translation_score,
            f.logic_error_level,
            f.preferred_option,
            f.feedback_text,
            f.improvement_note,
            f.translation_note,
            f.created_at
         FROM cailama_model_feedback f
         INNER JOIN cailama_model_benchmark_cases c ON c.id = f.case_id
         LEFT JOIN cailama_model_benchmark_observations o ON o.id = f.observation_id"
        . $feedbackWhere .
        " ORDER BY f.created_at DESC
          LIMIT 80";
    $feedback = $pdo->prepare($feedbackSql);
    $feedback->execute($feedbackParams);

    return [$summary->fetchAll(), $feedback->fetchAll()];
}

$filters = [
    'run_key' => query_string('run_key', 120),
    'role_name' => query_string('role_name', 80),
    'case_key' => query_string('case_key', 120),
    'error_status' => query_string('error_status', 40),
];
$loadError = null;
$summary = [];
$feedbackRows = [];

try {
    $pdo = ConnectionFactory::fromConfig($config, 'cailama');
    [$summary, $feedbackRows] = load_results($pdo, $filters);
} catch (Throwable) {
    $loadError = 'Die Benchmark-Datenbank ist noch nicht bereit. Bitte zuerst das aktuelle Datenbankschema einspielen.';
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Geschützte CaiLama Benchmark-Auswertung">
  <meta name="robots" content="noindex,nofollow">
  <title>CaiLama - Benchmark-Auswertung</title>
  <link rel="canonical" href="https://cailama.org/benchmark-feedback-results.php">
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
        <a href="account.php">Konto</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="page-hero">
      <div class="page-hero-inner">
        <p class="eyebrow">Geschützter Arbeitsbereich</p>
        <h1>Benchmark-Auswertung</h1>
        <p class="page-lead">Blind aggregierte Feedback- und Laufdaten nach Lauf, Rolle, Fall und Kandidat.</p>
      </div>
    </section>

    <section>
      <div class="section-inner feedback-layout">
        <?php if ($loadError !== null): ?>
          <p class="notice error" role="alert"><?= h($loadError) ?></p>
        <?php else: ?>
          <form class="auth-panel feedback-form" method="get" action="benchmark-feedback-results.php">
            <div class="form-grid">
              <div>
                <label for="run_key">Run-Key</label>
                <input id="run_key" name="run_key" value="<?= h($filters['run_key']) ?>">
              </div>
              <div>
                <label for="role_name">Rolle</label>
                <input id="role_name" name="role_name" value="<?= h($filters['role_name']) ?>">
              </div>
              <div>
                <label for="case_key">Fall</label>
                <input id="case_key" name="case_key" value="<?= h($filters['case_key']) ?>">
              </div>
              <div>
                <label for="error_status">Fehlerstatus</label>
                <input id="error_status" name="error_status" value="<?= h($filters['error_status']) ?>">
              </div>
            </div>
            <button class="button primary form-button" type="submit">Filtern</button>
          </form>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Lauf/Fall</th>
                  <th>Kandidat</th>
                  <th>Feedback</th>
                  <th>Bewertung</th>
                  <th>Metriken</th>
                  <th>Fehler</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($summary as $row): ?>
                  <tr>
                    <td><strong><?= h((string) $row['role_name']) ?></strong><br><?= h((string) $row['task_label']) ?><br><span class="muted"><?= h((string) $row['run_key']) ?></span></td>
                    <td><?= h(candidate_label($row)) ?></td>
                    <td><?= h((string) $row['feedback_count']) ?> Feedbacks<br><?= h((string) $row['observation_count']) ?> Beobachtungen</td>
                    <td>Qualität <?= h((string) ($row['quality_avg'] ?? '-')) ?><br>Aufgabe <?= h((string) ($row['task_solution_avg'] ?? '-')) ?><br>Dauer <?= h((string) ($row['duration_score_avg'] ?? '-')) ?><br>Übersetzung <?= h((string) ($row['translation_avg'] ?? '-')) ?><br>schwere Logikfehler <?= h((string) ($row['major_logic_errors'] ?? 0)) ?></td>
                    <td>Dauer Ø <?= h((string) ($row['duration_avg'] ?? '-')) ?> ms<br>Thinking Ø <?= h((string) ($row['thinking_avg'] ?? '-')) ?><br>Output Ø <?= h((string) ($row['output_avg'] ?? '-')) ?><br>Gesamt Ø <?= h((string) ($row['total_avg'] ?? '-')) ?></td>
                    <td><?= h((string) ($row['error_observations'] ?? 0)) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <?php if ($feedbackRows !== []): ?>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Zeitpunkt</th>
                    <th>Fall</th>
                    <th>Kandidat</th>
                    <th>Bewertung</th>
                    <th>Notizen</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($feedbackRows as $row): ?>
                    <tr>
                      <td><?= h((string) $row['created_at']) ?></td>
                      <td><?= h((string) $row['role_name']) ?><br><?= h((string) $row['task_label']) ?></td>
                      <td><?= h(candidate_label($row)) ?></td>
                      <td>Qualität <?= h((string) $row['quality_score']) ?>, Aufgabe <?= h((string) $row['task_solution_score']) ?><?= $row['duration_score'] !== null ? ', Dauer ' . h((string) $row['duration_score']) : '' ?><?= $row['translation_score'] !== null ? ', Übersetzung ' . h((string) $row['translation_score']) : '' ?><br>Logik <?= h((string) $row['logic_error_level']) ?>, A/B <?= h((string) $row['preferred_option']) ?><br>Gesamt <?= h((string) ($row['total_tokens'] ?? '-')) ?></td>
                      <td><?= h((string) ($row['feedback_text'] ?? '')) ?><br><span class="muted"><?= h((string) ($row['improvement_note'] ?? '')) ?></span><?php if ((string) ($row['translation_note'] ?? '') !== ''): ?><br><span class="muted">Übersetzung: <?= h((string) $row['translation_note']) ?></span><?php endif; ?></td>
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
