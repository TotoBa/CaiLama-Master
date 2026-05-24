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

function request_string(array $source, string $key, int $maxLength): string
{
    $value = is_string($source[$key] ?? null) ? trim($source[$key]) : '';
    if (strlen($value) > $maxLength) {
        return substr($value, 0, $maxLength);
    }
    return $value;
}

function request_int_or_null(array $source, string $key): ?int
{
    $value = is_string($source[$key] ?? null) ? trim($source[$key]) : '';
    if ($value === '' || !preg_match('/^\d+$/', $value)) {
        return null;
    }
    return min((int) $value, 2147483647);
}

function post_score(string $key): ?int
{
    $value = request_int_or_null($_POST, $key);
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

function benchmark_piece_config(array $config): array
{
    $feedback = is_array($config['benchmark_feedback'] ?? null) ? $config['benchmark_feedback'] : [];
    $sets = is_array($feedback['piece_sets'] ?? null) ? $feedback['piece_sets'] : [];
    return [
        'baseUrl' => is_string($feedback['piece_asset_base_url'] ?? null) ? trim($feedback['piece_asset_base_url']) : '',
        'sets' => $sets,
        'defaultSet' => is_string($feedback['default_piece_set'] ?? null) ? trim($feedback['default_piece_set']) : '',
    ];
}

function observation_select_sql(string $extraWhere): string
{
    return
        "SELECT
            o.id,
            o.case_id,
            o.run_key,
            o.model_label,
            o.duration_ms,
            o.input_tokens,
            o.thinking_tokens,
            o.output_tokens,
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
         WHERE " . $extraWhere;
}

function load_observation(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare(observation_select_sql('o.id = :id'));
    $statement->execute(['id' => $id]);
    $row = $statement->fetch();
    return is_array($row) ? $row : null;
}

function load_next_open_observation(PDO $pdo, string $runKey): ?array
{
    $where = 'f.id IS NULL';
    $params = [];
    if ($runKey !== '') {
        $where .= ' AND o.run_key = :run_key';
        $params['run_key'] = $runKey;
    }
    $statement = $pdo->prepare(
        observation_select_sql($where) .
        " ORDER BY o.created_at ASC, c.role_name, c.task_label, MD5(CONCAT(o.run_key, c.case_key, o.model_label, o.id))
          LIMIT 1"
    );
    $statement->execute($params);
    $row = $statement->fetch();
    return is_array($row) ? $row : null;
}

function feedback_exists(PDO $pdo, int $observationId): bool
{
    $statement = $pdo->prepare(
        "SELECT 1
         FROM cailama_model_feedback
         WHERE observation_id = :observation_id
         LIMIT 1"
    );
    $statement->execute(['observation_id' => $observationId]);
    return $statement->fetchColumn() !== false;
}

function upsert_feedback(PDO $pdo, ?int $userId, array $observation, array $values): void
{
    $find = $pdo->prepare(
        "SELECT id
         FROM cailama_model_feedback
         WHERE observation_id = :observation_id
           AND ((user_id IS NULL AND :user_id_is_null = 1) OR user_id = :user_id)
         ORDER BY id DESC
         LIMIT 1"
    );
    $find->execute([
        'observation_id' => (int) $observation['id'],
        'user_id' => $userId,
        'user_id_is_null' => $userId === null ? 1 : 0,
    ]);
    $existingId = $find->fetchColumn();

    $params = [
        'observation_id' => (int) $observation['id'],
        'case_id' => (int) $observation['case_id'],
        'user_id' => $userId,
        'run_key' => (string) $observation['run_key'],
        'model_label' => (string) $observation['model_label'],
        'duration_ms' => $observation['duration_ms'],
        'input_tokens' => $observation['input_tokens'],
        'thinking_tokens' => $observation['thinking_tokens'],
        'output_tokens' => $observation['output_tokens'],
        'quality_score' => $values['quality_score'],
        'task_solution_score' => $values['task_solution_score'],
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
                 quality_score = :quality_score,
                 task_solution_score = :task_solution_score,
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
             quality_score, task_solution_score, logic_error_level, preferred_option, translation_score, feedback_text, improvement_note, translation_note)
         VALUES
            (:observation_id, :case_id, :user_id, :run_key, :model_label, :duration_ms, :input_tokens, :thinking_tokens, :output_tokens,
             :quality_score, :task_solution_score, :logic_error_level, :preferred_option, :translation_score, :feedback_text, :improvement_note, :translation_note)"
    );
    $statement->execute($params);
}

$playMode = request_string($_GET, 'play', 1) === '1' || request_string($_POST, 'play', 1) === '1';
$runKey = request_string($_GET, 'run_key', 120);
if ($runKey === '') {
    $runKey = request_string($_POST, 'run_key', 120);
}
$observationId = request_int_or_null($_GET, 'observation_id');
if ($observationId === null) {
    $observationId = request_int_or_null($_POST, 'observation_id');
}

$loadError = null;
$errors = [];
$observation = null;
$alreadyRated = false;
$pieceConfig = benchmark_piece_config($config);

try {
    $pdo = ConnectionFactory::fromConfig($config, 'cailama');

    if ($observationId !== null) {
        $observation = load_observation($pdo, $observationId);
    } elseif ($playMode) {
        $observation = load_next_open_observation($pdo, $runKey);
    }

    if ($observation !== null) {
        $runKey = (string) $observation['run_key'];
        $alreadyRated = feedback_exists($pdo, (int) $observation['id']);
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $token = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null;
        if (!$session->validateCsrf($token)) {
            $errors[] = 'Die Eingabe konnte nicht verarbeitet werden.';
        }
        if ($observation === null) {
            $errors[] = 'Der Benchmark-Fall konnte nicht gefunden werden.';
        }

        $qualityScore = post_score('quality_score');
        $taskSolutionScore = post_score('task_solution_score');
        $translationScore = post_score('translation_score');
        $logicErrorLevel = request_string($_POST, 'logic_error_level', 16);
        $preferredOption = request_string($_POST, 'preferred_option', 16);

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

        if ($errors === [] && $observation !== null) {
            $userId = isset($user['id']) && ctype_digit((string) $user['id']) ? (int) $user['id'] : null;
            upsert_feedback($pdo, $userId, $observation, [
                'quality_score' => $qualityScore,
                'task_solution_score' => $taskSolutionScore,
                'logic_error_level' => $logicErrorLevel,
                'preferred_option' => $preferredOption,
                'translation_score' => $translationScore,
                'feedback_text' => nullable_text(request_string($_POST, 'feedback_text', 5000)),
                'improvement_note' => nullable_text(request_string($_POST, 'improvement_note', 5000)),
                'translation_note' => nullable_text(request_string($_POST, 'translation_note', 5000)),
            ]);

            $target = $playMode
                ? 'benchmark-feedback-item.php?play=1&saved=1&run_key=' . rawurlencode($runKey)
                : 'benchmark-feedback.php?saved=1&run_key=' . rawurlencode($runKey);
            header('Location: ' . $target, true, 303);
            exit;
        }
    }
} catch (Throwable) {
    $loadError = 'Die Benchmark-Datenbank ist noch nicht bereit. Bitte zuerst das aktuelle Datenbankschema einspielen.';
}

$scoreOptions = [
    1 => 'unbrauchbar',
    2 => 'schwach',
    3 => 'brauchbar',
    4 => 'gut',
    5 => 'stark',
];
$taskOptions = [
    1 => 'nicht gelöst',
    2 => 'kaum gelöst',
    3 => 'teilweise gelöst',
    4 => 'gut gelöst',
    5 => 'voll gelöst',
];
$logicOptions = [
    'unknown' => 'unklar',
    'none' => 'keine Logikfehler',
    'minor' => 'kleine Logikfehler',
    'major' => 'schwere Logikfehler',
];
$preferenceOptions = [
    'not_applicable' => 'nicht anwendbar',
    'a' => 'Option A besser',
    'b' => 'Option B besser',
    'tie' => 'gleich gut',
];
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Geschütztes CaiLama Benchmark-Feedback">
  <meta name="robots" content="noindex,nofollow">
  <title>CaiLama - Feedback-Fall</title>
  <link rel="canonical" href="https://cailama.org/benchmark-feedback-item.php">
  <link rel="stylesheet" href="assets/styles.css">
  <link rel="icon" href="./favicon.ico" type="image/x-icon">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="benchmark-feedback-page">
  <header class="site-header">
    <nav class="nav" aria-label="Hauptnavigation">
      <a class="brand" href="index.php">
        <img src="https://raw.githubusercontent.com/TotoBa/CaiLama/main/img/logo-small.png" alt="">
        <span>CaiLama</span>
      </a>
      <div class="nav-links">
        <a href="benchmark-feedback.php">Feedback</a>
        <a href="benchmark-feedback-results.php">Auswertung</a>
        <a href="account.php">Konto</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="feedback-section">
      <div class="feedback-workspace feedback-item-workspace">
        <?php if (isset($_GET['saved'])): ?>
          <p class="notice" role="status">Feedback gespeichert. Nächster offener Fall wurde geladen.</p>
        <?php endif; ?>
        <?php if ($loadError !== null): ?>
          <p class="notice error" role="alert"><?= h($loadError) ?></p>
        <?php elseif ($observation === null): ?>
          <p class="notice" role="status">Für diesen Lauf gibt es aktuell keinen offenen Feedback-Fall.</p>
          <div class="button-row">
            <a class="button light" href="benchmark-feedback.php?run_key=<?= h(rawurlencode($runKey)) ?>">Zur Liste</a>
            <a class="button light" href="benchmark-feedback-results.php?run_key=<?= h(rawurlencode($runKey)) ?>">Zur Auswertung</a>
          </div>
        <?php else: ?>
          <?php foreach ($errors as $error): ?>
            <p class="notice error" role="alert"><?= h($error) ?></p>
          <?php endforeach; ?>
          <?php if ($alreadyRated && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST'): ?>
            <p class="notice" role="status">Dieser Fall hat bereits Feedback und wird in der offenen Liste nicht mehr angezeigt.</p>
          <?php endif; ?>

          <div class="feedback-item-header">
            <div>
              <p class="eyebrow">Blindes Feedback</p>
              <h1><?= h(candidate_label($observation)) ?></h1>
              <p class="page-lead"><?= h((string) $observation['role_name']) ?> · <?= h((string) $observation['task_label']) ?></p>
            </div>
            <div class="button-row">
              <a class="button light" href="benchmark-feedback.php?run_key=<?= h(rawurlencode($runKey)) ?>">Zur Liste</a>
              <?php if ($playMode): ?>
                <a class="button light" href="benchmark-feedback-item.php?play=1&amp;run_key=<?= h(rawurlencode($runKey)) ?>">Überspringen</a>
              <?php endif; ?>
            </div>
          </div>

          <div class="feedback-item-grid">
            <article class="auth-panel benchmark-observation-detail">
              <div>
                <h2>Aufgabe</h2>
                <p><?= h((string) (($observation['task_prompt_excerpt'] ?? '') ?: $observation['task_summary'])) ?></p>
                <?php if ((string) ($observation['quality_question'] ?? '') !== ''): ?>
                  <p><strong>Bewertungsfrage:</strong> <?= h((string) $observation['quality_question']) ?></p>
                <?php endif; ?>
              </div>

              <div class="detail-grid">
                <div>
                  <strong>Erwarteter Output</strong>
                  <span><?= h((string) (($observation['expected_output_type'] ?? '') ?: 'nicht angegeben')) ?></span>
                </div>
                <div>
                  <strong>Position</strong>
                  <span><?= h((string) (($observation['position_label'] ?? '') ?: 'keine Positionskennung')) ?></span>
                </div>
                <div>
                  <strong>Metriken</strong>
                  <span>Dauer <?= h((string) ($observation['duration_ms'] ?? '-')) ?> ms · Input <?= h((string) ($observation['input_tokens'] ?? '-')) ?> · Thinking <?= h((string) ($observation['thinking_tokens'] ?? '-')) ?> · Output <?= h((string) ($observation['output_tokens'] ?? '-')) ?></span>
                </div>
                <div>
                  <strong>Fehlerstatus</strong>
                  <span><?= h((string) (($observation['error_status'] ?? '') ?: 'kein Fehlerstatus')) ?></span>
                </div>
              </div>

              <?php if ((string) ($observation['candidate_moves_excerpt'] ?? '') !== ''): ?>
                <div>
                  <strong>Kandidatenzüge</strong>
                  <p><?= h((string) $observation['candidate_moves_excerpt']) ?></p>
                </div>
              <?php endif; ?>
              <?php if ((string) ($observation['error_message'] ?? '') !== ''): ?>
                <p class="notice error"><?= h((string) $observation['error_message']) ?></p>
              <?php endif; ?>

              <div>
                <strong>Antwortauszug</strong>
                <pre class="output-excerpt"><?= h((string) ($observation['output_excerpt'] ?? '')) ?></pre>
              </div>

              <div
                class="feedback-board"
                data-fen="<?= h((string) ($observation['position_fen'] ?? '')) ?>"
                data-side="<?= h((string) ($observation['side_to_move'] ?? '')) ?>"
                data-piece-config="<?= h(json_encode($pieceConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}') ?>"
              >
                <div class="board-toolbar">
                  <strong>Stellung</strong>
                  <button class="button light board-flip" type="button">Drehen</button>
                </div>
                <p class="board-empty">Keine Stellung hinterlegt.</p>
                <div class="chess-board" aria-label="Benchmark-Stellung"></div>
              </div>
            </article>

            <form class="auth-panel feedback-form fast-feedback-form" method="post" action="benchmark-feedback-item.php">
              <input type="hidden" name="csrf_token" value="<?= h($session->csrfToken()) ?>">
              <input type="hidden" name="observation_id" value="<?= h((string) $observation['id']) ?>">
              <input type="hidden" name="run_key" value="<?= h($runKey) ?>">
              <input type="hidden" name="play" value="<?= $playMode ? '1' : '0' ?>">

              <h2>Bewertung</h2>

              <fieldset class="choice-group">
                <legend>Qualität</legend>
                <?php foreach ($scoreOptions as $score => $label): ?>
                  <label><input type="radio" name="quality_score" value="<?= h((string) $score) ?>" required> <?= h($label) ?></label>
                <?php endforeach; ?>
              </fieldset>

              <fieldset class="choice-group">
                <legend>Aufgabe gelöst</legend>
                <?php foreach ($taskOptions as $score => $label): ?>
                  <label><input type="radio" name="task_solution_score" value="<?= h((string) $score) ?>" required> <?= h($label) ?></label>
                <?php endforeach; ?>
              </fieldset>

              <fieldset class="choice-group optional-choice-group">
                <legend>Übersetzung</legend>
                <?php foreach ($scoreOptions as $score => $label): ?>
                  <label><input type="radio" name="translation_score" value="<?= h((string) $score) ?>"> <?= h($label) ?></label>
                <?php endforeach; ?>
                <label><input type="radio" name="translation_score" value=""> nicht bewerten</label>
              </fieldset>

              <div class="form-grid two">
                <div>
                  <label for="logic_error_level">Logikfehler</label>
                  <select id="logic_error_level" name="logic_error_level" required>
                    <?php foreach ($logicOptions as $value => $label): ?>
                      <option value="<?= h($value) ?>"><?= h($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label for="preferred_option">A/B-Präferenz</label>
                  <select id="preferred_option" name="preferred_option" required>
                    <?php foreach ($preferenceOptions as $value => $label): ?>
                      <option value="<?= h($value) ?>"><?= h($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div>
                <label for="feedback_text">Feedback</label>
                <textarea id="feedback_text" name="feedback_text" rows="4" maxlength="5000" placeholder="Was war fachlich gut oder falsch?"><?= h(request_string($_POST, 'feedback_text', 5000)) ?></textarea>
              </div>
              <div>
                <label for="improvement_note">Verbesserungshinweis</label>
                <textarea id="improvement_note" name="improvement_note" rows="3" maxlength="5000" placeholder="Welche Regel, Gewichtung oder Prompt-Änderung folgt daraus?"><?= h(request_string($_POST, 'improvement_note', 5000)) ?></textarea>
              </div>
              <div>
                <label for="translation_note">Hinweis zur Übersetzung</label>
                <textarea id="translation_note" name="translation_note" rows="2" maxlength="5000" placeholder="Nur falls Übersetzung oder deutsche Ausgabe bewertet wurde."><?= h(request_string($_POST, 'translation_note', 5000)) ?></textarea>
              </div>

              <button class="button primary form-button" type="submit"><?= $playMode ? 'Speichern und weiter' : 'Feedback speichern' ?></button>
            </form>
          </div>
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
  <script>
  (function ($) {
    const unicodePieces = {
      wK: "♔", wQ: "♕", wR: "♖", wB: "♗", wN: "♘", wP: "♙",
      bK: "♚", bQ: "♛", bR: "♜", bB: "♝", bN: "♞", bP: "♟"
    };
    const pieceMap = { p: "P", r: "R", n: "N", b: "B", q: "Q", k: "K" };

    function parseFen(fen) {
      const parts = String(fen || "").trim().split(/\s+/);
      if (parts.length < 2) {
        return null;
      }
      const ranks = parts[0].split("/");
      if (ranks.length !== 8) {
        return null;
      }
      return ranks.map(function (rank) {
        const squares = [];
        for (const ch of rank) {
          if (/^[1-8]$/.test(ch)) {
            for (let i = 0; i < Number(ch); i += 1) {
              squares.push("");
            }
          } else if (/^[prnbqkPRNBQK]$/.test(ch)) {
            const color = ch === ch.toUpperCase() ? "w" : "b";
            squares.push(color + pieceMap[ch.toLowerCase()]);
          } else {
            return null;
          }
        }
        return squares.length === 8 ? squares : null;
      });
    }

    function pieceUrl(piece, config) {
      if (!config || !config.baseUrl || !config.defaultSet || !config.sets || !config.sets[config.defaultSet]) {
        return "";
      }
      return String(config.baseUrl).replace(/\/$/, "") + "/" + String(config.sets[config.defaultSet]).replace(/^\/|\/$/g, "") + "/" + piece + ".svg";
    }

    function renderBoard($wrap, flipped) {
      const rows = parseFen($wrap.data("fen"));
      const $board = $wrap.find(".chess-board");
      if (!rows) {
        $wrap.find(".board-empty").show();
        $board.empty().hide();
        return;
      }
      let config = {};
      try {
        config = JSON.parse($wrap.attr("data-piece-config") || "{}");
      } catch (error) {
        config = {};
      }
      $wrap.find(".board-empty").hide();
      $board.empty().show();
      const rankOrder = flipped ? [7, 6, 5, 4, 3, 2, 1, 0] : [0, 1, 2, 3, 4, 5, 6, 7];
      const fileOrder = flipped ? [7, 6, 5, 4, 3, 2, 1, 0] : [0, 1, 2, 3, 4, 5, 6, 7];
      rankOrder.forEach(function (r) {
        fileOrder.forEach(function (f) {
          const piece = rows[r][f];
          const $square = $("<div>", { "class": "board-square " + (((r + f) % 2 === 0) ? "light-square" : "dark-square") });
          if (piece) {
            const url = pieceUrl(piece, config);
            if (url) {
              $("<img>", { src: url, alt: piece }).appendTo($square);
            } else {
              $("<span>", { text: unicodePieces[piece] || piece }).appendTo($square);
            }
          }
          $board.append($square);
        });
      });
    }

    $(".feedback-board").each(function () {
      const $wrap = $(this);
      const side = String($wrap.data("side") || "").toLowerCase();
      let flipped = side === "b" || side === "black";
      renderBoard($wrap, flipped);
      $wrap.find(".board-flip").on("click", function () {
        flipped = !flipped;
        renderBoard($wrap, flipped);
      });
    });
  })(jQuery);
  </script>
</body>
</html>
