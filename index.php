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
    <div class="level mb-5">
      <div class="level-left">
        <h1 class="title">🎬 Meine Filme</h1>
      </div>
      <div class="level-right">
        <form method="post">
          <label class="checkbox mr-4">
            <input type="checkbox" id="darkModeSwitch"> Darkmode
          </label>
          <a href="logout.php" class="button is-danger is-light">Logout</a>
        </form>
      </div>
    </div>

    <!-- Hinzufügen-Button -->
    <div class="mb-4">
      <button onclick="openAddModal()" class="button is-primary">+ Film hinzufügen</button>
    </div>

    <!-- Suchfeld -->
    <div class="field mb-5">
      <div class="control">
        <input id="search" class="input" type="text" placeholder="🔍 Filme suchen..." oninput="searchMovies(this.value)">
      </div>
    </div>

    <!-- Grid -->
    <div class="columns is-multiline">
      <div class="column is-two-thirds">
        <?php foreach ($movies as $movie): ?>
          <?php
          $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM watch_logs WHERE movie_id = ?");
          $stmt2->execute([$movie['id']]);
          $count = $stmt2->fetchColumn();

          $stmt3 = $pdo->prepare("SELECT id, watched_at FROM watch_logs WHERE movie_id = ? ORDER BY watched_at DESC LIMIT 1");
          $stmt3->execute([$movie['id']]);
          $lastLog = $stmt3->fetch();
          ?>
          <div class="box">
            <div class="level">
              <div class="level-left">
                <h2 id="title-<?= $movie['id'] ?>" class="title is-5"><?= htmlspecialchars($movie['title']) ?></h2>
              </div>
              <div class="level-right buttons are-small">
                <button onclick="rateMovie(<?= $movie['id'] ?>, 'like')" class="button is-success" id="like-btn-<?= $movie['id'] ?>">
                  👍 <span id="like-count-<?= $movie['id'] ?>" class="ml-1"><?= (int)$movie['likes'] ?></span>
                </button>
                <button onclick="rateMovie(<?= $movie['id'] ?>, 'neutral')" class="button is-warning" id="neutral-btn-<?= $movie['id'] ?>">
                  😐 <span id="neutral-count-<?= $movie['id'] ?>" class="ml-1"><?= (int)$movie['neutral'] ?></span>
                </button>
                <button onclick="rateMovie(<?= $movie['id'] ?>, 'dislike')" class="button is-danger" id="dislike-btn-<?= $movie['id'] ?>">
                  👎 <span id="dislike-count-<?= $movie['id'] ?>" class="ml-1"><?= (int)$movie['dislikes'] ?></span>
                </button>
                <button onclick="openModal(<?= $movie['id'] ?>, '<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?>', <?= $count ?>, <?= $lastLog ? "'" . $lastLog['watched_at'] . "'" : "null" ?>)" class="button is-info">✏️</button>
                <button onclick="deleteMovie(<?= $movie['id'] ?>)" class="button is-danger">🗑️</button>
              </div>
            </div>
            <p id="info-<?= $movie['id'] ?>" class="has-text-grey is-size-7 mt-2">
              <?= $count ?>x gesehen<?= $lastLog ? ' – Zuletzt: ' . date("d.m.Y", strtotime($lastLog['watched_at'])) : '' ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Statistik -->
      <div class="column is-one-third">
        <div class="box has-text-centered">
          <p class="title is-4 has-text-link"><?= $totalMovies ?></p>
          <p class="subtitle is-6">Filme insgesamt</p>
        </div>
        <div class="box has-text-centered">
          <p class="title is-4 has-text-success"><?= $totalWatches ?></p>
          <p class="subtitle is-6">Sichtungen insgesamt</p>
        </div>
        <div class="box has-text-centered">
          <p class="title is-4 has-text-purple"><?= $todayWatches ?></p>
          <p class="subtitle is-6">Heute geguckt</p>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="js/main.js"></script>
</body>
</html>
