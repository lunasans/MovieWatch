<?php
// Error Reporting ausschalten für JSON Response
ini_set('display_errors', 0);
error_reporting(0);

require 'config/config.php';

// Content-Type Header setzen
header('Content-Type: application/json; charset=utf-8');

// Nur POST-Requests erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Input-Daten lesen
    $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new Exception('Keine Daten empfangen');
    }
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Ungültiges JSON: ' . json_last_error_msg());
    }
    
    // Parameter validieren
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $title = isset($data['title']) ? trim($data['title']) : '';
    $count = isset($data['count']) ? (int)$data['count'] : 0;
    $date = isset($data['date']) ? $data['date'] : null;
    $tags = isset($data['tags']) ? $data['tags'] : [];
    
    // Validierung
    if ($id <= 0) {
        throw new Exception('Ungültige Film-ID');
    }
    
    if (empty($title)) {
        throw new Exception('Titel darf nicht leer sein');
    }
    
    if ($count < 0) {
        throw new Exception('Anzahl Sichtungen kann nicht negativ sein');
    }
    
    // Prüfen ob Film existiert
    $stmt = $pdo->prepare("SELECT id FROM movies WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Film nicht gefunden');
    }
    
    // Transaktion starten
    $pdo->beginTransaction();
    
    try {
        // Film-Titel aktualisieren
        $stmt = $pdo->prepare("UPDATE movies SET title = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $id]);
        
        // Alle bestehenden Sichtungen löschen
        $stmt = $pdo->prepare("DELETE FROM watch_logs WHERE movie_id = ?");
        $stmt->execute([$id]);
        
        // Neue Sichtungen hinzufügen
        if ($count > 0) {
            $watchDate = !empty($date) ? $date : date('Y-m-d');
            
            // Datum validieren
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $watchDate)) {
                $watchDate = date('Y-m-d');
            }
            
            $stmt = $pdo->prepare("INSERT INTO watch_logs (movie_id, watched_at, created_at) VALUES (?, ?, NOW())");
            for ($i = 0; $i < $count; $i++) {
                $stmt->execute([$id, $watchDate]);
            }
        }
        
        // Tags verarbeiten
        // Alle bestehenden Tag-Verknüpfungen löschen
        $stmt = $pdo->prepare("DELETE FROM movie_tags WHERE movie_id = ?");
        $stmt->execute([$id]);
        
        if (!empty($tags) && is_array($tags)) {
            $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
            $insertTagStmt = $pdo->prepare("INSERT INTO tags (name, created_at) VALUES (?, NOW())");
            $linkTagStmt = $pdo->prepare("INSERT INTO movie_tags (movie_id, tag_id) VALUES (?, ?)");
            
            foreach ($tags as $tagName) {
                $tagName = trim($tagName);
                if (empty($tagName)) continue;
                
                // Tag suchen oder erstellen
                $stmt->execute([$tagName]);
                $tag = $stmt->fetch();
                
                if (!$tag) {
                    $insertTagStmt->execute([$tagName]);
                    $tagId = $pdo->lastInsertId();
                } else {
                    $tagId = $tag['id'];
                }
                
                // Tag mit Film verknüpfen
                $linkTagStmt->execute([$id, $tagId]);
            }
        }
        
        // Transaktion bestätigen
        $pdo->commit();
        
        // Erfolgreiche Antwort
        echo json_encode([
            'success' => true,
            'message' => 'Film erfolgreich aktualisiert',
            'data' => [
                'id' => $id,
                'title' => $title,
                'count' => $count,
                'date' => $date
            ]
        ]);
        
    } catch (Exception $e) {
        // Transaktion rückgängig machen
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    // Fehler-Antwort
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>