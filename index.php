<?php
require 'config/config.php';
require 'inc/func.php';
require 'inc/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'header.php';
?>

<section class="section">
  <div class="container">
    <div class="flex justify-between items-center mb-6">
      <h1 class="title">🎬 Meine Filme</h1>
      <div class="flex items-center gap-4">
        <label class="dark-mode-toggle">
          <input type="checkbox" id="darkModeSwitch" class="toggle-input">
          <span class="toggle-slider"></span>
          <span class="toggle-label">Dark Mode</span>
        </label>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      </div>
    </div>

    <!-- Hinzufügen-Button -->
    <div class="mb-4">
      <button onclick="openAddModal()" class="btn btn-primary">+ Film hinzufügen</button>
    </div>

    <!-- Suchfeld -->
    <div class="field mb-6">
      <input id="search" class="input" type="text" placeholder="🔍 Filme suchen..." oninput="searchMovies(this.value)">
    </div>

    <!-- Grid Layout -->
    <div class="grid lg-grid-cols-3">
      <!-- Filme Liste -->
      <div id="film-list">
        <?php foreach ($movies as $movie): ?>
          <?php
          $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM watch_logs WHERE movie_id = ?");
          $stmt2->execute([$movie['id']]);
          $count = $stmt2->fetchColumn();

          $stmt3 = $pdo->prepare("SELECT id, watched_at FROM watch_logs WHERE movie_id = ? ORDER BY watched_at DESC LIMIT 1");
          $stmt3->execute([$movie['id']]);
          $lastLog = $stmt3->fetch();
          ?>
          <div id="movie-<?= $movie['id'] ?>" class="card movie-card">
            <div class="movie-info">
              <h2 id="title-<?= $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></h2>
              <p id="info-<?= $movie['id'] ?>" class="text-gray">
                <?= $count ?>x gesehen<?= $lastLog ? ' – Zuletzt: ' . date("d.m.Y", strtotime($lastLog['watched_at'])) : '' ?>
              </p>
            </div>
            <div class="movie-actions">
              <!-- Rating Buttons -->
              <div class="rating-buttons">
                <button onclick="rateMovie(<?= $movie['id'] ?>, 'like')" 
                        class="rating-btn like" id="like-btn-<?= $movie['id'] ?>">
                  👍 <span id="like-count-<?= $movie['id'] ?>"><?= (int)$movie['likes'] ?></span>
                </button>
                <button onclick="rateMovie(<?= $movie['id'] ?>, 'neutral')" 
                        class="rating-btn neutral" id="neutral-btn-<?= $movie['id'] ?>">
                  😐 <span id="neutral-count-<?= $movie['id'] ?>"><?= (int)$movie['neutral'] ?></span>
                </button>
                <button onclick="rateMovie(<?= $movie['id'] ?>, 'dislike')" 
                        class="rating-btn dislike" id="dislike-btn-<?= $movie['id'] ?>">
                  👎 <span id="dislike-count-<?= $movie['id'] ?>"><?= (int)$movie['dislikes'] ?></span>
                </button>
              </div>
              
              <!-- Action Buttons -->
              <div class="flex gap-2">
                <button onclick="openModal(<?= $movie['id'] ?>, '<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?>', <?= $count ?>, <?= $lastLog ? "'" . $lastLog['watched_at'] . "'" : "null" ?>)" 
                        class="btn btn-success btn-small">✏️</button>
                <button onclick="deleteMovie(<?= $movie['id'] ?>)" 
                        class="btn btn-danger btn-small">🗑️</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Statistik Sidebar -->
      <div class="flex flex-col gap-4">
        <div class="stat-card">
          <div class="stat-number stat-blue"><?= $totalMovies ?></div>
          <div class="stat-label">Filme insgesamt</div>
        </div>
        <div class="stat-card">
          <div class="stat-number stat-green"><?= $totalWatches ?></div>
          <div class="stat-label">Sichtungen insgesamt</div>
        </div>
        <div class="stat-card">
          <div class="stat-number stat-purple"><?= $todayWatches ?></div>
          <div class="stat-label">Heute geguckt</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Toast Notification -->
<div id="toast" class="toast" style="display: none;"></div>

<?php require 'inc/modals.php'; ?>

<script src="js/main.js"></script>
</body>
</html>