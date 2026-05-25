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

function latest_run_key(PDO $pdo): string
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

function load_feedback_list(PDO $pdo, string $runKey): array
{
    $params = [];
    $whereRun = '';
    if ($runKey !== '') {
        $whereRun = ' AND o.run_key = :run_key';
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
         WHERE 1=1" . $whereRun
    );
    $stats->execute($params);

    $observations = $pdo->prepare(
        "SELECT
            o.id,
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
            o.position_fen,
            o.side_to_move,
            o.position_label,
            o.task_prompt_excerpt,
            o.expected_output_type,
            o.candidate_moves_excerpt,
            o.error_status,
            o.error_message,
            o.output_excerpt,
            o.created_at,
            c.case_key,
            c.task_label,
            c.task_summary,
            c.role_name,
            c.quality_question
         FROM cailama_model_benchmark_observations o
         INNER JOIN cailama_model_benchmark_cases c ON c.id = o.case_id
         LEFT JOIN cailama_model_feedback f ON f.observation_id = o.id
         WHERE f.id IS NULL" . $whereRun . "
         ORDER BY o.created_at ASC, c.role_name, c.task_label, MD5(CONCAT(o.run_key, c.case_key, o.model_label, o.id))"
    );
    $observations->execute($params);

    $runs = $pdo->query(
        "SELECT
            run_key,
            COUNT(*) AS imported_count,
            MIN(created_at) AS first_seen,
            MAX(created_at) AS last_seen
         FROM cailama_model_benchmark_observations
         GROUP BY run_key
         ORDER BY last_seen DESC, run_key DESC"
    )->fetchAll();

    $statsRow = $stats->fetch();
    return [
        is_array($statsRow) ? $statsRow : ['imported_count' => 0, 'open_count' => 0, 'done_count' => 0],
        $observations->fetchAll(),
        $runs,
    ];
}

$loadError = null;
$runKey = query_string('run_key', 120);
$stats = ['imported_count' => 0, 'open_count' => 0, 'done_count' => 0];
$observations = [];
$runs = [];

try {
    $pdo = ConnectionFactory::fromConfig($config, 'cailama');
    if ($runKey === '') {
        $runKey = latest_run_key($pdo);
    }
    [$stats, $observations, $runs] = load_feedback_list($pdo, $runKey);
} catch (Throwable) {
    $loadError = 'Die Benchmark-Datenbank ist noch nicht bereit. Bitte zuerst das aktuelle Datenbankschema einspielen.';
}
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
<body class="benchmark-feedback-page">
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
    <section class="page-hero compact-page-hero">
      <div class="page-hero-inner">
        <p class="eyebrow">Geschützter Arbeitsbereich</p>
        <h1>Benchmark-Feedback</h1>
        <p class="page-lead">Offene Beobachtungen aus dem aktuellen Lauf. Bewertete Fälle verschwinden automatisch aus dieser Liste.</p>
      </div>
    </section>

    <section class="feedback-section">
      <div class="feedback-workspace">
        <?php if (isset($_GET['saved'])): ?>
          <p class="notice" role="status">Feedback wurde gespeichert. Die Liste wurde aktualisiert.</p>
        <?php endif; ?>
        <?php if ($loadError !== null): ?>
          <p class="notice error" role="alert"><?= h($loadError) ?></p>
        <?php else: ?>
          <div class="feedback-toolbar">
            <form class="feedback-run-picker" method="get" action="benchmark-feedback.php">
              <label for="run_key">Lauf</label>
              <select id="run_key" name="run_key" onchange="this.form.submit()">
                <?php foreach ($runs as $run): ?>
                  <option value="<?= h((string) $run['run_key']) ?>" <?= ((string) $run['run_key'] === $runKey) ? 'selected' : '' ?>>
                    <?= h((string) $run['run_key']) ?> · <?= h((string) $run['imported_count']) ?> importiert
                  </option>
                <?php endforeach; ?>
              </select>
              <noscript><button class="button light" type="submit">Lauf laden</button></noscript>
            </form>
            <div class="feedback-toolbar-actions">
              <?php if ($observations !== []): ?>
                <a class="button primary" href="benchmark-feedback-item.php?play=1&amp;run_key=<?= h(rawurlencode($runKey)) ?>">Playmodus starten</a>
              <?php endif; ?>
              <a class="button light" href="benchmark-feedback-results.php?run_key=<?= h(rawurlencode($runKey)) ?>">Auswertung</a>
            </div>
          </div>

          <div class="grid-3 compact-metrics">
            <div class="metric">
              <strong><?= h((string) (int) $stats['imported_count']) ?></strong>
              <p>importierte Beobachtungen</p>
            </div>
            <div class="metric">
              <strong><?= h((string) (int) $stats['open_count']) ?></strong>
              <p>offen für Feedback</p>
            </div>
            <div class="metric">
              <strong><?= h((string) (int) $stats['done_count']) ?></strong>
              <p>bereits bewertet</p>
            </div>
          </div>

          <?php if ($observations === []): ?>
            <p class="notice" role="status">Für diesen Lauf gibt es aktuell keine offenen Feedback-Fälle.</p>
          <?php else: ?>
            <div class="table-wrap feedback-table-wrap">
              <table class="feedback-table">
                <thead>
                  <tr>
                    <th>Zeit</th>
                    <th>Rolle und Fall</th>
                    <th>Aufgabe</th>
                    <th>Kandidat</th>
                    <th>Metriken</th>
                    <th>Aktion</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($observations as $observation): ?>
                    <tr>
                      <td><?= h((string) $observation['created_at']) ?></td>
                      <td>
                        <strong><?= h((string) $observation['role_name']) ?></strong><br>
                        <?= h((string) $observation['task_label']) ?><br>
                        <span class="muted"><?= h((string) (($observation['position_label'] ?? '') ?: $observation['run_key'])) ?></span>
                      </td>
                      <td>
                        <?= h((string) (($observation['task_prompt_excerpt'] ?? '') ?: $observation['task_summary'])) ?>
                        <?php if ((string) ($observation['error_status'] ?? '') !== ''): ?>
                          <br><span class="status-pill error-pill"><?= h((string) $observation['error_status']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td><?= h(candidate_label($observation)) ?></td>
                      <td>
                        Dauer <?= h((string) ($observation['duration_ms'] ?? '-')) ?> ms<br>
                        Input <?= h((string) ($observation['input_tokens'] ?? '-')) ?> ·
                        Thinking <?= h((string) ($observation['thinking_tokens'] ?? '-')) ?> ·
                        Output <?= h((string) ($observation['output_tokens'] ?? '-')) ?><br>
                        Gesamt <?= h((string) ($observation['total_tokens'] ?? '-')) ?> ·
                        Verbrauch <?= h((string) (($observation['model_usage_level'] ?? '') ?: '-')) ?>
                      </td>
                      <td>
                        <a class="button light table-action" href="benchmark-feedback-item.php?observation_id=<?= h((string) $observation['id']) ?>&amp;run_key=<?= h(rawurlencode($runKey)) ?>">Bewerten</a>
                      </td>
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
