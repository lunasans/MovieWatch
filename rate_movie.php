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

// Benutzer-Authentifizierung prüfen
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

try {
    $userId = (int)$_SESSION['user_id'];
    
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
    $movieId = isset($data['id']) ? (int)$data['id'] : 0;
    $type = isset($data['type']) ? trim($data['type']) : '';
    
    if ($movieId <= 0) {
        throw new Exception('Ungültige Film-ID');
    }
    
    if (!in_array($type, ['like', 'neutral', 'dislike'])) {
        throw new Exception('Ungültiger Bewertungstyp');
    }
    
    // Prüfen ob Film existiert
    $stmt = $pdo->prepare("SELECT id FROM movies WHERE id = ?");
    $stmt->execute([$movieId]);
    if (!$stmt->fetch()) {
        throw new Exception('Film nicht gefunden');
    }
    
    // Transaktion starten
    $pdo->beginTransaction();
    
    try {
        // Prüfen, ob User schon bewertet hat
        $stmt = $pdo->prepare("SELECT id FROM movie_votes WHERE movie_id = ? AND user_id = ?");
        $stmt->execute([$movieId, $userId]);
        $existingVote = $stmt->fetch();
        
        if ($existingVote) {
            // Vorhandene Bewertung aktualisieren
            $stmt = $pdo->prepare("UPDATE movie_votes SET vote = ?, updated_at = NOW() WHERE movie_id = ? AND user_id = ?");
            $stmt->execute([$type, $movieId, $userId]);
        } else {
            // Neue Bewertung einfügen
            $stmt = $pdo->prepare("INSERT INTO movie_votes (movie_id, user_id, vote, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->execute([$movieId, $userId, $type]);
        }
        
        // Neue Zähler auslesen
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN vote = 'like' THEN 1 ELSE 0 END) AS likes,
                SUM(CASE WHEN vote = 'neutral' THEN 1 ELSE 0 END) AS neutral,
                SUM(CASE WHEN vote = 'dislike' THEN 1 ELSE 0 END) AS dislikes
            FROM movie_votes 
            WHERE movie_id = ?
        ");
        $stmt->execute([$movieId]);
        $counts = $stmt->fetch();
        
        if (!$counts) {
            throw new Exception('Fehler beim Abrufen der Bewertungen');
        }
        
        // Transaktion bestätigen
        $pdo->commit();
        
        // Erfolgreiche Antwort
        echo json_encode([
            'success' => true,
            'message' => 'Bewertung gespeichert',
            'likes' => (int)$counts['likes'],
            'neutral' => (int)$counts['neutral'],
            'dislikes' => (int)$counts['dislikes']
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