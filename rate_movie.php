<?php
ini_set('display_errors', 0);
error_reporting(0);
require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $movieId = (int) ($data['id'] ?? 0);
    $vote = trim($data['type'] ?? '');
    $userId = (int) $_SESSION['user_id'];

    if ($movieId <= 0)
        throw new Exception('Ungültige Film-ID');
    if (!in_array($vote, ['like', 'neutral', 'dislike']))
        throw new Exception('Ungültiger Vote-Typ');

    // Vote einfügen oder aktualisieren
    $stmt = $pdo->prepare("SELECT id FROM movie_votes WHERE movie_id = ? AND user_id = ?");
    $stmt->execute([$movieId, $userId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE movie_votes SET vote = ? WHERE movie_id = ? AND user_id = ?");
        $stmt->execute([$vote, $movieId, $userId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO movie_votes (movie_id, user_id, vote) VALUES (?, ?, ?)");
        $stmt->execute([$movieId, $userId, $vote]);
    }

    // Aktuelle Zähler abrufen
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN vote = 'like' THEN 1 ELSE 0 END) as likes,
            SUM(CASE WHEN vote = 'neutral' THEN 1 ELSE 0 END) as neutral,
            SUM(CASE WHEN vote = 'dislike' THEN 1 ELSE 0 END) as dislikes
        FROM movie_votes 
        WHERE movie_id = ?
    ");
    $stmt->execute([$movieId]);
    $counts = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Bewertung gespeichert',
        'likes' => (int) $counts['likes'],
        'neutral' => (int) $counts['neutral'],
        'dislikes' => (int) $counts['dislikes']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>