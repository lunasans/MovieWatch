<?php
require 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("Kein Film ausgewählt.");
}

$movieId = (int)$_GET['id'];

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
  <link rel="stylesheet" href="css/output.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen p-6">
  <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-4 rounded shadow">
    <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">
      <?= htmlspecialchars($movie['title']) ?> – Gesehen am
    </h1>
    <ul id="log-list">
      <?php foreach ($logs as $log): ?>
        <li id="log-<?= $log['id'] ?>" class="flex items-center justify-between mb-2">
          <div class="flex items-center space-x-2">
            <input type="date" value="<?= $log['watched_at'] ?>"
                   class="border p-1 rounded bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100"
                   id="date-<?= $log['id'] ?>">
            <button onclick="saveDate(<?= $log['id'] ?>)"
                    class="bg-green-500 text-white px-2 py-1 rounded text-sm hover:bg-green-600 transition">
              💾 Speichern
            </button>
          </div>
          <button onclick="deleteLog(<?= $log['id'] ?>)"
                  class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition">
            🗑️ Löschen
          </button>
        </li>
      <?php endforeach; ?>
    </ul>
    <a href="index.php" class="text-blue-500 hover:underline mt-4 inline-block">Zurück</a>
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