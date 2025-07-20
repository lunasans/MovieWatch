<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$type = $data['type'] ?? '';

if (!$id || !in_array($type, ['like','neutral','dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Eingaben']);
    exit;
}

// Prüfen, ob User schon bewertet hat
$stmt = $pdo->prepare("SELECT id FROM movie_votes WHERE movie_id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$existingVote = $stmt->fetchColumn();

if ($existingVote) {
    // Vorhandene Bewertung aktualisieren
    $stmt = $pdo->prepare("UPDATE movie_votes SET vote = ? WHERE movie_id = ? AND user_id = ?");
    $stmt->execute([$type, $id, $user_id]);
} else {
    // Neue Bewertung einfügen
    $stmt = $pdo->prepare("INSERT INTO movie_votes (movie_id, user_id, vote) VALUES (?, ?, ?)");
    $stmt->execute([$id, $user_id, $type]);
}

// Neue Zähler auslesen
$stmt = $pdo->prepare("SELECT 
    (SELECT COUNT(*) FROM movie_votes WHERE movie_id = ? AND vote = 'like') AS likes,
    (SELECT COUNT(*) FROM movie_votes WHERE movie_id = ? AND vote = 'neutral') AS neutral,
    (SELECT COUNT(*) FROM movie_votes WHERE movie_id = ? AND vote = 'dislike') AS dislikes
");
$stmt->execute([$id, $id, $id]);
$row = $stmt->fetch();

echo json_encode([
    'success' => true,
    'likes' => (int)$row['likes'],
    'neutral' => (int)$row['neutral'],
    'dislikes' => (int)$row['dislikes'],
]);
