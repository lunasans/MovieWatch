<?php
// Filme laden
$stmt = $pdo->query("
  SELECT 
    m.id,
    m.title,
    (SELECT COUNT(*) FROM movie_votes WHERE movie_id = m.id AND vote = 'like') AS likes,
    (SELECT COUNT(*) FROM movie_votes WHERE movie_id = m.id AND vote = 'neutral') AS neutral,
    (SELECT COUNT(*) FROM movie_votes WHERE movie_id = m.id AND vote = 'dislike') AS dislikes
  FROM movies m
");
$movies = $stmt->fetchAll();

// Statistik
$stmt = $pdo->query("SELECT COUNT(*) FROM movies");
$totalMovies = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM watch_logs");
$totalWatches = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM watch_logs WHERE DATE(watched_at) = CURDATE()");
$stmt->execute();
$todayWatches = $stmt->fetchColumn();
?>