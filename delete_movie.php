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
    
    $id = (int)($data['id'] ?? 0);
    
    if ($id <= 0) throw new Exception('Ungültige Film-ID');
    
    $pdo->beginTransaction();
    
    // Alle verknüpften Daten löschen
    $stmt = $pdo->prepare("DELETE FROM movie_tags WHERE movie_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM watch_logs WHERE movie_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM movie_votes WHERE movie_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Film nicht gefunden');
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Film erfolgreich gelöscht'
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>