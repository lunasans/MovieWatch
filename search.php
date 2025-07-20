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
  <div id="movie-<?= $movie['id'] ?>" class="card movie-card hover-lift">
    <div class="movie-info">
      <h2 id="title-<?= $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></h2>
      <p id="info-<?= $movie['id'] ?>" class="text-gray">
        <?= $count ?>x gesehen
        <?php if ($lastSeen): ?>
          – Zuletzt: <?= date("d.m.Y", strtotime($lastSeen)) ?>
        <?php endif; ?>
      </p>
    </div>
    <div class="movie-actions">
      <button onclick="openModal(
          <?= $movie['id'] ?>,
          '<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?>',
          <?= $count ?>,
          <?= $lastSeen ? "'".substr($lastSeen, 0, 10)."'" : "null" ?>
      )"
        class="btn btn-success btn-small hover-scale">
        ✏️
      </button>
      <button onclick="deleteMovie(<?= $movie['id'] ?>)"
        class="btn btn-danger btn-small hover-scale">
        🗑️
      </button>
    </div>
  </div>
<?php endforeach; ?>