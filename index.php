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

<!-- Animierte Hintergrund-Kreise -->
<div class="circle circle-one"></div>
<div class="circle circle-two"></div>

<div class="container">
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <h1 class="app-title">
                <i class="bi bi-film"></i>
                MovieWatch
            </h1>

            <div class="header-actions">
                <!-- Dark Mode Toggle -->
                <label class="dark-mode-toggle">
                    <input type="checkbox" id="darkModeSwitch" class="toggle-input">
                    <span class="toggle-switch"></span>
                    <span style="font-size: 0.9rem; font-weight: 500;">Dark Mode</span>
                </label>

                <!-- Actions -->
                <button onclick="openAddModal()" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Film hinzufügen
                </button>

                <a href="logout.php" class="btn btn-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $totalMovies ?></div>
            <div class="stat-label">Filme insgesamt</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $totalWatches ?></div>
            <div class="stat-label">Sichtungen insgesamt</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $todayWatches ?></div>
            <div class="stat-label">Heute geguckt</div>
        </div>
    </div>

    <!-- Search -->
    <div class="search-container">
        <input id="search" class="search-input" type="text" placeholder="🔍 Filme suchen..."
            oninput="searchMovies(this.value)">
    </div>

    <!-- Main Content -->
    <div class="main-grid">
        <!-- Movies List -->
        <div class="movies-grid" id="film-list">
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
                        <p id="info-<?= $movie['id'] ?>">
                            <?= $count ?>x
                            gesehen<?= $lastLog ? ' – Zuletzt: ' . date("d.m.Y", strtotime($lastLog['watched_at'])) : '' ?>
                        </p>
                    </div>

                    <div class="movie-actions">
                        <!-- Rating Buttons -->
                        <div class="rating-buttons">
                            <button onclick="rateMovie(<?= $movie['id'] ?>, 'like')" class="rating-btn like"
                                id="like-btn-<?= $movie['id'] ?>">
                                <i class="bi bi-hand-thumbs-up"></i>
                                <span id="like-count-<?= $movie['id'] ?>"><?= (int) $movie['likes'] ?></span>
                            </button>
                            <button onclick="rateMovie(<?= $movie['id'] ?>, 'neutral')" class="rating-btn neutral"
                                id="neutral-btn-<?= $movie['id'] ?>">
                                <i class="bi bi-dash-circle"></i>
                                <span id="neutral-count-<?= $movie['id'] ?>"><?= (int) $movie['neutral'] ?></span>
                            </button>
                            <button onclick="rateMovie(<?= $movie['id'] ?>, 'dislike')" class="rating-btn dislike"
                                id="dislike-btn-<?= $movie['id'] ?>">
                                <i class="bi bi-hand-thumbs-down"></i>
                                <span id="dislike-count-<?= $movie['id'] ?>"><?= (int) $movie['dislikes'] ?></span>
                            </button>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button
                                onclick="openModal(<?= $movie['id'] ?>, '<?= htmlspecialchars($movie['title'], ENT_QUOTES) ?>', <?= $count ?>, <?= $lastLog ? "'" . $lastLog['watched_at'] . "'" : "null" ?>)"
                                class="btn btn-secondary btn-small">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="movie.php?id=<?= $movie['id'] ?>" class="btn btn-secondary btn-small"
                                title="Sichtungen bearbeiten">
                                <i class="bi bi-calendar-event"></i>
                            </a>
                            <button onclick="deleteMovie(<?= $movie['id'] ?>)" class="btn btn-danger btn-small">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="card">
                <h3 style="margin-bottom: var(--spacing-md); color: var(--clr-accent);">
                    <i class="bi bi-info-circle"></i>
                    Schnellzugriff
                </h3>
                <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                    <button onclick="showTopRated()" class="btn btn-secondary w-full">
                        <i class="bi bi-star-fill"></i>
                        Top bewertete Filme
                    </button>
                    <button onclick="showRecentlyWatched()" class="btn btn-secondary w-full">
                        <i class="bi bi-clock-history"></i>
                        Zuletzt gesehen
                    </button>
                    <button onclick="showUnwatched()" class="btn btn-secondary w-full">
                        <i class="bi bi-eye-slash"></i>
                        Noch nicht gesehen
                    </button>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: var(--spacing-md); color: var(--clr-accent);">
                    <i class="bi bi-graph-up"></i>
                    Aktivität
                </h3>
                <div style="text-align: center;">
                    <p style="color: var(--clr-text-muted); margin-bottom: var(--spacing-sm);">
                        Diese Woche
                    </p>
                    <div style="font-size: 2rem; font-weight: 700; color: var(--clr-accent);">
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM watch_logs WHERE watched_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                        $stmt->execute();
                        echo $stmt->fetchColumn();
                        ?>
                    </div>
                    <p style="color: var(--clr-text-muted); font-size: 0.9rem;">
                        Filme geschaut
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="toast"></div>

<!-- Theme Switcher -->
<div class="theme-switcher" id="themeSwitcher"></div>

<!-- Modals -->
<?php require 'inc/modals.php'; ?>

<!-- Footer -->
<?php require 'inc/footer.php'; ?>

<script src="js/main.js"></script>
</body>

</html>