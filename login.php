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
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="min-h-screen flex justify-center items-center">
  <div class="container">
    <div class="flex justify-center">
      <div class="w-full" style="max-width: 400px;">
        <form method="post" class="card">
          <h1 class="text-2xl font-bold text-center mb-4">Login</h1>

          <?php if ($error): ?>
            <div class="p-4 mb-4 rounded" style="background-color: var(--color-red); color: white;">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <div class="field">
            <label class="label">Benutzername</label>
            <input class="input" type="text" name="username" required>
          </div>

          <div class="field">
            <label class="label">Passwort</label>
            <input class="input" type="password" name="password" required>
          </div>

          <div class="field">
            <label class="flex items-center gap-2">
              <input type="checkbox" name="stay_logged_in">
              <span class="text-sm">Angemeldet bleiben</span>
            </label>
          </div>

          <div class="field">
            <button class="btn btn-primary w-full">Anmelden</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</body>
</html>