<?php
declare(strict_types=1);

use CaiLama\WebApi\Auth\AuthService;
use CaiLama\WebApi\Auth\SessionManager;
use CaiLama\WebApi\Db\ConnectionFactory;

$config = require __DIR__ . '/api_app/init.php';
$session = new SessionManager($config['session'] ?? []);
$session->start();

if ($session->currentUser() !== null) {
    header('Location: account.php', true, 303);
    exit;
}

$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null;
    $email = is_string($_POST['email'] ?? null) ? $_POST['email'] : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if (!$session->validateCsrf($token)) {
        $error = 'Die Anmeldung konnte nicht verarbeitet werden.';
    } elseif (!$session->canAttemptLogin(
        (int) ($config['auth']['max_attempts_per_session'] ?? 5),
        (int) ($config['auth']['attempt_window_seconds'] ?? 600),
    )) {
        $error = 'Zu viele Anmeldeversuche. Bitte spaeter erneut versuchen.';
    } else {
        try {
            $pdo = ConnectionFactory::fromConfig($config, 'auth');
            $auth = new AuthService($pdo, $config['auth'] ?? []);
            $user = $auth->authenticate($email, $password);
            if ($user !== null) {
                $session->clearLoginFailures();
                $session->login($user);
                header('Location: account.php', true, 303);
                exit;
            }
            $session->recordLoginFailure((int) ($config['auth']['attempt_window_seconds'] ?? 600));
            $error = 'E-Mail oder Passwort ist ungueltig.';
        } catch (Throwable) {
            $error = 'Der Login ist vorbereitet, aber noch nicht konfiguriert.';
        }
    }
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="CaiLama Login">
  <meta name="robots" content="noindex,follow">
  <title>CaiLama - Login</title>
  <link rel="canonical" href="https://cailama.org/login.php">
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
        <a href="projects.php">Projekte</a>
        <a href="architecture.php">Architektur</a>
        <a href="roadmap.php">Roadmap</a>
        <a href="operations.php">Betrieb</a>
        <a href="reference.php">Referenz</a>
        <a aria-current="page" href="login.php">Login</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="page-hero">
      <div class="page-hero-inner">
        <p class="eyebrow">CaiLama Konto</p>
        <h1>Login</h1>
        <p class="page-lead">Sitzung fuer geschuetzte CaiLama-Webfunktionen.</p>
      </div>
    </section>

    <section>
      <div class="section-inner auth-layout">
        <form class="auth-panel" method="post" action="login.php" autocomplete="on">
          <input type="hidden" name="csrf_token" value="<?= h($session->csrfToken()) ?>">
          <?php if ($error !== null): ?>
            <p class="notice error" role="alert"><?= h($error) ?></p>
          <?php endif; ?>
          <label for="email">E-Mail</label>
          <input id="email" name="email" type="email" autocomplete="username" required maxlength="190" value="<?= h(is_string($_POST['email'] ?? null) ? $_POST['email'] : '') ?>">
          <label for="password">Passwort</label>
          <input id="password" name="password" type="password" autocomplete="current-password" required>
          <button class="button primary form-button" type="submit">Einloggen</button>
        </form>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="section-inner">
      <span>CaiLama - Schachtraining als System</span>
      <a href="https://github.com/TotoBa/CaiLama-Master">Master-Repository</a>
    </div>
  </footer>
</body>
</html>
