<?php
// MovieWatch - Funktionen für neue Datenbankstruktur

// Filme mit allen relevanten Daten laden (mit optimierter Abfrage)
$stmt = $pdo->query("
    SELECT 
        m.id,
        m.title,
        m.original_title,
        m.release_year,
        m.personal_rating,
        m.is_favorite,
        m.watch_status,
        m.created_at,
        m.updated_at,
        vms.watch_count,
        vms.last_watched,
        vms.avg_watch_rating,
        vms.likes,
        vms.neutral,
        vms.dislikes,
        vms.tag_count
    FROM movies m
    LEFT JOIN view_movie_stats vms ON m.id = vms.id
    ORDER BY m.created_at DESC
");
$movies = $stmt->fetchAll();

// Statistiken für Dashboard
try {
    // Gesamtanzahl Filme
    $stmt = $pdo->query("SELECT COUNT(*) FROM movies");
    $totalMovies = $stmt->fetchColumn();

    // Gesamtanzahl Sichtungen
    $stmt = $pdo->query("SELECT COUNT(*) FROM watch_logs");
    $totalWatches = $stmt->fetchColumn();

    // Heute gesehene Filme
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM watch_logs WHERE DATE(watched_at) = CURDATE()");
    $stmt->execute();
    $todayWatches = $stmt->fetchColumn();

    // Diese Woche gesehene Filme
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM watch_logs WHERE watched_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $stmt->execute();
    $weekWatches = $stmt->fetchColumn();

    // Anzahl Favoriten
    $stmt = $pdo->query("SELECT COUNT(*) FROM movies WHERE is_favorite = 1");
    $totalFavorites = $stmt->fetchColumn();

    // Anzahl verschiedener Tags
    $stmt = $pdo->query("SELECT COUNT(*) FROM tags WHERE usage_count > 0");
    $totalTags = $stmt->fetchColumn();

    // Durchschnittliche persönliche Bewertung
    $stmt = $pdo->query("SELECT ROUND(AVG(personal_rating), 1) FROM movies WHERE personal_rating IS NOT NULL");
    $avgPersonalRating = $stmt->fetchColumn() ?? 0;

    // Letzte Aktivitäten (für Sidebar)
    $stmt = $pdo->prepare("
        SELECT 
            m.title,
            wl.watched_at,
            wl.rating
        FROM watch_logs wl
        JOIN movies m ON wl.movie_id = m.id
        ORDER BY wl.watched_at DESC, wl.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recentWatches = $stmt->fetchAll();

    // Top bewertete Filme (für Sidebar)
    $stmt = $pdo->prepare("
        SELECT 
            m.title,
            m.personal_rating,
            vms.likes,
            vms.avg_watch_rating
        FROM movies m
        LEFT JOIN view_movie_stats vms ON m.id = vms.id
        WHERE m.personal_rating IS NOT NULL
        ORDER BY m.personal_rating DESC, vms.likes DESC
        LIMIT 5
    ");
    $stmt->execute();
    $topRatedMovies = $stmt->fetchAll();

    // Beliebte Tags (für Tag Cloud)
    $stmt = $pdo->prepare("
        SELECT 
            name,
            color,
            usage_count
        FROM tags
        WHERE usage_count > 0
        ORDER BY usage_count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $popularTags = $stmt->fetchAll();

    // Filme nach Status
    $stmt = $pdo->query("
        SELECT 
            watch_status,
            COUNT(*) as count
        FROM movies
        GROUP BY watch_status
    ");
    $moviesByStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (Exception $e) {
    // Fallback-Werte wenn Queries fehlschlagen
    $totalMovies = 0;
    $totalWatches = 0;
    $todayWatches = 0;
    $weekWatches = 0;
    $totalFavorites = 0;
    $totalTags = 0;
    $avgPersonalRating = 0;
    $recentWatches = [];
    $topRatedMovies = [];
    $popularTags = [];
    $moviesByStatus = [];

    error_log('MovieWatch func.php error: ' . $e->getMessage());
}

// Hilfsfunktionen
function getMovieById($pdo, $id)
{
    $stmt = $pdo->prepare("
        SELECT 
            m.*,
            vms.watch_count,
            vms.last_watched,
            vms.avg_watch_rating,
            vms.likes,
            vms.neutral,
            vms.dislikes
        FROM movies m
        LEFT JOIN view_movie_stats vms ON m.id = vms.id
        WHERE m.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getMovieTags($pdo, $movieId)
{
    $stmt = $pdo->prepare("
        SELECT t.id, t.name, t.color, t.slug
        FROM tags t
        JOIN movie_tags mt ON t.id = mt.tag_id
        WHERE mt.movie_id = ?
        ORDER BY t.name
    ");
    $stmt->execute([$movieId]);
    return $stmt->fetchAll();
}

function getWatchLogs($pdo, $movieId)
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM watch_logs
        WHERE movie_id = ?
        ORDER BY watched_at DESC, created_at DESC
    ");
    $stmt->execute([$movieId]);
    return $stmt->fetchAll();
}

function formatDate($date)
{
    if (!$date)
        return '';
    return date('d.m.Y', strtotime($date));
}

function formatDateTime($datetime)
{
    if (!$datetime)
        return '';
    return date('d.m.Y H:i', strtotime($datetime));
}

function getWatchStatusBadge($status)
{
    $badges = [
        'not_watched' => ['class' => 'secondary', 'text' => 'Nicht gesehen'],
        'watched' => ['class' => 'success', 'text' => 'Gesehen'],
        'want_to_watch' => ['class' => 'primary', 'text' => 'Watchlist'],
        'watching' => ['class' => 'warning', 'text' => 'Schaue gerade']
    ];

    return $badges[$status] ?? ['class' => 'secondary', 'text' => 'Unbekannt'];
}
?>