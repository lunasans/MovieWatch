<?php
require 'config/config.php';

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    $stmt = $pdo->query("SELECT * FROM movies ORDER BY created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE title LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$q%"]);
}

$movies = $stmt->fetchAll();

foreach ($movies as $movie):
    $stmt2 = $pdo->prepare("SELECT COUNT(*), MAX(watched_at) FROM watch_logs WHERE movie_id = ?");
    $stmt2->execute([$movie['id']]);
    $row = $stmt2->fetch();
    $count = $row['COUNT(*)'];
    $lastSeen = $row['MAX(watched_at)'];
?>
  <div class="bg-white dark:bg-gray-800 p-4 rounded shadow hover:shadow-lg hover:scale-[1.02] transform transition duration-200 flex justify-between items-center">
    <div>
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
        <?= htmlspecialchars($movie['title']) ?>
      </h2>
      <p class="text-gray-600 text-sm dark:text-gray-300">
        <?= $count ?>x gesehen
        <?php if ($lastSeen): ?>
          – Zuletzt: <?= date("d.m.Y", strtotime($lastSeen)) ?>
        <?php endif; ?>
      </p>
    </div>
    <div class="flex space-x-2">
      <button onclick="openModal(
          <?= $movie['id'] ?>,
          '<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?>',
          <?= $count ?>,
          <?= $lastSeen ? "'".substr($lastSeen, 0, 10)."'" : "null" ?>
      )"
        class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transform hover:scale-105 transition">
        ✏️
      </button>
      <button onclick="deleteMovie(<?= $movie['id'] ?>)"
        class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transform hover:scale-105 transition">
        🗑️
      </button>
    </div>
  </div>
<?php endforeach; ?>
