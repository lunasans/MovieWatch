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
    
    // Tags verarbeiten - vereinheitlichte Logik
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
            $stmt->execute([$movieId, $tagId]);
        }
    }
    
    $pdo->commit();
    
    // Erfolgreiche Antwort mit Tags
    echo json_encode([
        'success' => true,
        'message' => 'Film erfolgreich hinzugefügt',
        'id' => $movieId,
        'data' => [
            'id' => $movieId,
            'title' => $title,
            'tags' => $processedTags
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