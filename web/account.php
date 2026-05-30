<?php
declare(strict_types=1);

use CaiLama\WebApi\Auth\SessionManager;
use CaiLama\WebApi\Auth\UserProfileService;
use CaiLama\WebApi\Db\ConnectionFactory;

require __DIR__ . '/_private_api.php';
$config = cailama_api_config();
$session = new SessionManager($config['session'] ?? []);
$session->start();
$user = $session->currentUser();

if ($user === null) {
    header('Location: login.php', true, 303);
    exit;
}

try {
    $pdo = ConnectionFactory::fromConfig($config, 'cailama');
    $user = (new UserProfileService($pdo, $config['auth'] ?? []))->attachProfile($user);
} catch (Throwable) {
    // Profile lookup is optional for account display.
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
  <meta name="description" content="CaiLama Konto">
  <meta name="robots" content="noindex,nofollow">
  <title>CaiLama - Konto</title>
  <link rel="canonical" href="https://cailama.org/account.php">
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
        <p class="eyebrow">CaiLama Konto</p>
        <h1>Konto</h1>
        <p class="page-lead"><?= h((string) (($user['display_name'] ?? '') ?: ($user['login_name'] ?? ''))) ?></p>
      </div>
    </section>

    <section>
      <div class="section-inner auth-layout">
        <div class="auth-panel">
          <dl class="account-list">
            <div>
              <dt>Login</dt>
              <dd><?= h((string) ($user['login_name'] ?? '')) ?></dd>
            </div>
            <div>
              <dt>CaiLama-Profil</dt>
              <dd>
                <?php if (!empty($user['profile_key'])): ?>
                  <?= h((string) ($user['player_display_name'] ?? '')) ?>
                  (Training: <?= h((string) ($user['training_name'] ?? '')) ?>)
                <?php else: ?>
                  nicht verknüpft
                <?php endif; ?>
              </dd>
            </div>
            <div>
              <dt>Datenbank</dt>
              <dd><?= ($config['databases']['cailama']['enabled'] ?? false) ? 'konfiguriert' : 'nicht konfiguriert' ?></dd>
            </div>
          </dl>
          <a class="button light form-button" href="app.php">CaiLama App öffnen</a>
          <a class="button light form-button" href="benchmark-feedback.php">Benchmark-Feedback öffnen</a>
          <a class="button light form-button" href="benchmark-feedback-results.php">Benchmark-Auswertung öffnen</a>
          <form method="post" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?= h($session->csrfToken()) ?>">
            <button class="button light form-button" type="submit">Abmelden</button>
          </form>
        </div>
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
