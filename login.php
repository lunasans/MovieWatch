<?php
require 'config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stayLoggedIn = isset($_POST['stay_logged_in']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];

        if ($stayLoggedIn) {
            $token = bin2hex(random_bytes(32));
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, token, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user['id'], $token, $ip, $agent]);

            setcookie('remember_token', $token, time() + (365 * 24 * 60 * 60), "/");
        }

        header('Location: index.php');
        exit;
    } else {
        $error = 'Ungültige Anmeldedaten';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body class="has-background-light">
  <section class="section">
    <div class="container">
      <div class="columns is-centered">
        <div class="column is-4">
          <form method="post" class="box">
            <h1 class="title is-4 has-text-centered">Login</h1>

            <?php if ($error): ?>
              <div class="notification is-danger">
                <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>

            <div class="field">
              <label class="label">Benutzername</label>
              <div class="control">
                <input class="input" type="text" name="username" required>
              </div>
            </div>

            <div class="field">
              <label class="label">Passwort</label>
              <div class="control">
                <input class="input" type="password" name="password" required>
              </div>
            </div>

            <div class="field">
              <label class="checkbox">
                <input type="checkbox" name="stay_logged_in" style="margin-right: 6px;">
                Angemeldet bleiben
              </label>
            </div>

            <div class="field">
              <div class="control">
                <button class="button is-primary is-fullwidth">Anmelden</button>
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>
  </section>
</body>
</html>
