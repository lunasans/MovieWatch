<?php
ini_set('display_errors', 0);
error_reporting(0);

require 'config/config.php';

// Header setzen BEVOR irgendein Output
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = file_get_contents('php://input');

    if (empty($input)) {
        throw new Exception('Keine Daten empfangen');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Ungültiges JSON: ' . json_last_error_msg());
    }

    $title = trim($data['title'] ?? '');
    $tags = $data['tags'] ?? '';

    if (empty($title)) {
        throw new Exception('Titel ist erforderlich');
    }

    $pdo->beginTransaction();

    // Film hinzufügen
    $stmt = $pdo->prepare("INSERT INTO movies (title, created_at) VALUES (?, NOW())");
    $stmt->execute([$title]);
    $movieId = $pdo->lastInsertId();

    if (!$movieId) {
        throw new Exception('Fehler beim Erstellen des Films');
    }

    // Tags verarbeiten (falls vorhanden)
    if (!empty($tags)) {
        $tagArray = is_string($tags) ? array_map('trim', explode(',', $tags)) : [];
        $tagArray = array_unique(array_filter($tagArray, function ($tag) {
            return !empty($tag) && strlen($tag) <= 50;
        }));

        foreach ($tagArray as $tagName) {
            // Tag erstellen oder finden
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

            // Tag mit Film verknüpfen
            $stmt = $pdo->prepare("INSERT IGNORE INTO movie_tags (movie_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$movieId, $tagId]);
        }
    }

    $pdo->commit();

    // Erfolgreiche Antwort
    echo json_encode([
        'success' => true,
        'message' => 'Film erfolgreich hinzugefügt',
        'id' => $movieId,
        'data' => [
            'id' => $movieId,
            'title' => $title
        ]
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Fehler-Antwort
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>