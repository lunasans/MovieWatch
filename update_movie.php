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
    $stmt = $pdo->prepare("UPDATE movies SET title = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$title, $id]);
    
    // Watch Logs aktualisieren
    $stmt = $pdo->prepare("DELETE FROM watch_logs WHERE movie_id = ?");
    $stmt->execute([$id]);
    
    if ($count > 0) {
        $watchDate = $date ?: date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO watch_logs (movie_id, watched_at, created_at) VALUES (?, ?, NOW())");
        for ($i = 0; $i < $count; $i++) {
            $stmt->execute([$id, $watchDate]);
        }
    }
    
    // Tags aktualisieren - Alte Tags entfernen
    $stmt = $pdo->prepare("DELETE FROM movie_tags WHERE movie_id = ?");
    $stmt->execute([$id]);
    
    // Tags verarbeiten - einheitliche Logik wie in add_movie.php
    $processedTags = [];
    if (!empty($tags)) {
        // Tags können als Array oder comma-separated String kommen
        if (is_array($tags)) {
            $tagArray = $tags;
        } else if (is_string($tags)) {
            $tagArray = array_map('trim', explode(',', $tags));
        } else {
            $tagArray = [];
        }
        
        // Tags bereinigen und validieren
        foreach ($tagArray as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName) || strlen($tagName) > 50) continue;
            
            // Nur alphanumerische Zeichen, Leerzeichen, Bindestriche und Umlaute erlauben
            if (!preg_match('/^[a-zA-Z0-9\s\-_äöüÄÖÜß]+$/', $tagName)) continue;
            
            $processedTags[] = $tagName;
        }
        
        // Duplikate entfernen
        $processedTags = array_unique($processedTags);
        
        foreach ($processedTags as $tagName) {
            // Tag erstellen oder finden
            $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
            $stmt->execute([$tagName]);
            $tag = $stmt->fetch();
            
            if (!$tag) {
                // Slug für URL-freundliche Darstellung erstellen
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $tagName));
                $slug = preg_replace('/-+/', '-', trim($slug, '-'));
                
                // Prüfen ob Slug schon existiert
                $originalSlug = $slug;
                $counter = 1;
                while (true) {
                    $stmt = $pdo->prepare("SELECT id FROM tags WHERE slug = ?");
                    $stmt->execute([$slug]);
                    if (!$stmt->fetch()) break;
                    $slug = $originalSlug . '-' . $counter++;
                }
                
                $stmt = $pdo->prepare("INSERT INTO tags (name, slug, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$tagName, $slug]);
                $tagId = $pdo->lastInsertId();
            } else {
                $tagId = $tag['id'];
            }
            
            // Tag mit Film verknüpfen
            $stmt = $pdo->prepare("INSERT IGNORE INTO movie_tags (movie_id, tag_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$id, $tagId]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Film erfolgreich aktualisiert',
        'data' => [
            'id' => $id,
            'title' => $title,
            'tags' => $processedTags
        ]
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>