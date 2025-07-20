<?php
// ===== update_movie.php =====
ini_set('display_errors', 0);
error_reporting(0);
require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $id = (int)($data['id'] ?? 0);
    $title = trim($data['title'] ?? '');
    $count = (int)($data['count'] ?? 0);
    $date = $data['date'] ?? null;
    $tags = $data['tags'] ?? [];
    
    if ($id <= 0) throw new Exception('Ungültige Film-ID');
    if (empty($title)) throw new Exception('Titel ist erforderlich');
    
    $pdo->beginTransaction();
    
    // Film aktualisieren
    $stmt = $pdo->prepare("UPDATE movies SET title = ? WHERE id = ?");
    $stmt->execute([$title, $id]);
    
    // Watch Logs aktualisieren
    $stmt = $pdo->prepare("DELETE FROM watch_logs WHERE movie_id = ?");
    $stmt->execute([$id]);
    
    if ($count > 0) {
        $watchDate = $date ?: date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO watch_logs (movie_id, watched_at) VALUES (?, ?)");
        for ($i = 0; $i < $count; $i++) {
            $stmt->execute([$id, $watchDate]);
        }
    }
    
    // Tags aktualisieren
    $stmt = $pdo->prepare("DELETE FROM movie_tags WHERE movie_id = ?");
    $stmt->execute([$id]);
    
    if (!empty($tags) && is_array($tags)) {
        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;
            
            $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
            $stmt->execute([$tagName]);
            $tag = $stmt->fetch();
            
            if (!$tag) {
                $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
                $stmt->execute([$tagName]);
                $tagId = $pdo->lastInsertId();
            } else {
                $tagId = $tag['id'];
            }
            
            $stmt = $pdo->prepare("INSERT IGNORE INTO movie_tags (movie_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$id, $tagId]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Film erfolgreich aktualisiert'
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>