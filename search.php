<?php
require 'config/config.php';

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    $stmt = $pdo->query("
        SELECT m.*, 
               vms.watch_count,
               vms.last_watched,
               vms.likes,
               vms.neutral,
               vms.dislikes
        FROM movies m
        LEFT JOIN view_movie_stats vms ON m.id = vms.id
        ORDER BY m.created_at DESC
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT DISTINCT m.*, 
               vms.watch_count,
               vms.last_watched,
               vms.likes,
               vms.neutral,
               vms.dislikes
        FROM movies m
        LEFT JOIN view_movie_stats vms ON m.id = vms.id
        LEFT JOIN movie_tags mt ON m.id = mt.movie_id
        LEFT JOIN tags t ON mt.tag_id = t.id
        WHERE m.title LIKE ? 
           OR t.name LIKE ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute(["%$q%", "%$q%"]);
}

$movies = $stmt->fetchAll();

foreach ($movies as $movie):
    // Watch Logs laden
    $stmt2 = $pdo->prepare("SELECT COUNT(*), MAX(watched_at) FROM watch_logs WHERE movie_id = ?");
    $stmt2->execute([$movie['id']]);
    $row = $stmt2->fetch();
    $count = $row['COUNT(*)'];
    $lastSeen = $row['MAX(watched_at)'];
    
    // Tags für diesen Film laden
    $stmt3 = $pdo->prepare("
        SELECT t.name 
        FROM tags t 
        JOIN movie_tags mt ON t.id = mt.tag_id 
        WHERE mt.movie_id = ? 
        ORDER BY t.name
    ");
    $stmt3->execute([$movie['id']]);
    $movieTags = $stmt3->fetchAll(PDO::FETCH_COLUMN);
?>
  <div id="movie-<?= $movie['id'] ?>" class="card movie-card hover-lift">
    <div class="movie-info">
      <h2 id="title-<?= $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></h2>
      <p id="info-<?= $movie['id'] ?>" class="text-gray">
        <?= $count ?>x gesehen
        <?php if ($lastSeen): ?>
          – Zuletzt: <?= date("d.m.Y", strtotime($lastSeen)) ?>
        <?php endif; ?>
        <?php if (!empty($movieTags)): ?>
          | Tags: <?= htmlspecialchars(implode(', ', $movieTags)) ?>
        <?php endif; ?>
      </p>
    </div>
    <div class="movie-actions">
      <!-- Rating Buttons -->
      <div class="rating-buttons">
        <button onclick="rateMovie(<?= $movie['id'] ?>, 'like')" 
                class="rating-btn like" id="like-btn-<?= $movie['id'] ?>">
          <i class="bi bi-hand-thumbs-up"></i>
          <span id="like-count-<?= $movie['id'] ?>"><?= (int)($movie['likes'] ?? 0) ?></span>
        </button>
        <button onclick="rateMovie(<?= $movie['id'] ?>, 'neutral')" 
                class="rating-btn neutral" id="neutral-btn-<?= $movie['id'] ?>">
          <i class="bi bi-dash-circle"></i>
          <span id="neutral-count-<?= $movie['id'] ?>"><?= (int)($movie['neutral'] ?? 0) ?></span>
        </button>
        <button onclick="rateMovie(<?= $movie['id'] ?>, 'dislike')" 
                class="rating-btn dislike" id="dislike-btn-<?= $movie['id'] ?>">
          <i class="bi bi-hand-thumbs-down"></i>
          <span id="dislike-count-<?= $movie['id'] ?>"><?= (int)($movie['dislikes'] ?? 0) ?></span>
        </button>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex gap-2">
        <button onclick="openModal(
            <?= $movie['id'] ?>,
            '<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?>',
            <?= $count ?>,
            <?= $lastSeen ? "'".substr($lastSeen, 0, 10)."'" : "null" ?>
        )"
          class="btn btn-secondary btn-small hover-scale">
          <i class="bi bi-pencil"></i>
        </button>
        <a href="movie.php?id=<?= $movie['id'] ?>" 
           class="btn btn-secondary btn-small" 
           title="Sichtungen bearbeiten">
          <i class="bi bi-calendar-event"></i>
        </a>
        <button onclick="deleteMovie(<?= $movie['id'] ?>)"
          class="btn btn-danger btn-small hover-scale">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>
  </div>
<?php endforeach; ?>