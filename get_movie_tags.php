<?php
ini_set('display_errors', 0);
error_reporting(0);
require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['movie_id'])) {
    echo json_encode(['success' => false, 'message' => 'Movie ID required']);
    exit;
}

$movieId = (int)$_GET['movie_id'];

if ($movieId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Movie ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT t.name
        FROM tags t
        JOIN movie_tags mt ON t.id = mt.tag_id
        WHERE mt.movie_id = ?
        ORDER BY t.name ASC
    ");
    $stmt->execute([$movieId]);
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'tags' => $tags
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'tags' => []
    ]);
}
?>