<?php
require 'config/config.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

if (!isset($_GET['id'])) {
  die("Kein Film ausgewählt.");
}

$movieId = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
  die("Film nicht gefunden.");
}

$stmt = $pdo->prepare("SELECT * FROM watch_logs WHERE movie_id = ? ORDER BY watched_at DESC");
$stmt->execute([$movieId]);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <title>Gesehen am bearbeiten</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body class="min-h-screen p-6">
  <div class="container">
    <div style="max-width: 600px; margin: 0 auto;" class="card">
      <h1 class="text-2xl font-bold mb-4">
        <?= htmlspecialchars($movie['title']) ?> – Gesehen am
      </h1>
      <ul id="log-list">
        <?php foreach ($logs as $log): ?>
          <li id="log-<?= $log['id'] ?>" class="flex justify-between items-center mb-2 p-2 rounded"
            style="background-color: var(--bg-tertiary);">
            <div class="flex items-center gap-2">
              <input type="date" value="<?= $log['watched_at'] ?>" class="input" style="width: auto;"
                id="date-<?= $log['id'] ?>">
              <button onclick="saveDate(<?= $log['id'] ?>)" class="btn btn-success btn-small">
                💾 Speichern
              </button>
            </div>
            <button onclick="deleteLog(<?= $log['id'] ?>)" class="btn btn-danger btn-small">
              🗑️ Löschen
            </button>
          </li>
        <?php endforeach; ?>
      </ul>
      <a href="index.php" class="text-blue inline-block mt-4">← Zurück</a>
    </div>
  </div>

  <script>
    function saveDate(id) {
      const date = document.getElementById('date-' + id).value;
      fetch('update_watch.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, watched_at: date })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert('Datum gespeichert!');
          } else {
            alert('Fehler beim Speichern');
          }
        });
    }

    function deleteLog(id) {
      if (!confirm('Wirklich löschen?')) return;
      fetch('delete_watch.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById('log-' + id).remove();
          } else {
            alert('Fehler beim Löschen');
          }
        });
    }
  </script>

</body>

</html>