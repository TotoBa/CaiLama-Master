<?php
declare(strict_types=1);

use CaiLama\WebApi\Auth\SessionManager;

require __DIR__ . '/_private_api.php';
$config = cailama_api_config();
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

$checks = [
    'Website-Session' => 'ok',
    'Web-API konfiguriert' => (($config['web_api']['base_url'] ?? '') !== '') ? 'ok' : 'fehlt',
    'Origin konfiguriert' => (($config['origin']['base_url'] ?? '') !== '') ? 'ok' : 'fehlt',
    'Profil verknüpft' => !empty($user['profile_key']) ? 'ok' : 'fehlt',
];
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>CaiLama - App Status</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <main class="section-inner auth-layout">
    <h1>App Status</h1>
    <dl class="account-list">
      <?php foreach ($checks as $label => $state): ?>
        <div>
          <dt><?= h($label) ?></dt>
          <dd><?= h($state) ?></dd>
        </div>
      <?php endforeach; ?>
    </dl>
    <p><a href="app.php">Zur App</a> · <a href="account.php">Konto</a></p>
  </main>
</body>
</html>
