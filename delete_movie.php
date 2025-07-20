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
    
    if ($id <= 0) {
        throw new Exception('Ungültige Film-ID');
    }
    
    // Prüfen ob Film existiert
    $stmt = $pdo->prepare("SELECT id, title FROM movies WHERE id = ?");
    $stmt->execute([$id]);
    $movie = $stmt->fetch();
    
    if (!$movie) {
        throw new Exception('Film nicht gefunden');
    }
    
    // Transaktion starten
    $pdo->beginTransaction();
    
    try {
        // Alle verknüpften Daten löschen (in der richtigen Reihenfolge wegen Foreign Keys)
        
        // 1. Movie Tags löschen
        $stmt = $pdo->prepare("DELETE FROM movie_tags WHERE movie_id = ?");
        $stmt->execute([$id]);
        
        // 2. Watch Logs löschen
        $stmt = $pdo->prepare("DELETE FROM watch_logs WHERE movie_id = ?");
        $stmt->execute([$id]);
        
        // 3. Movie Votes löschen
        $stmt = $pdo->prepare("DELETE FROM movie_votes WHERE movie_id = ?");
        $stmt->execute([$id]);
        
        // 4. Film selbst löschen
        $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->execute([$id]);
        
        // Prüfen ob Film tatsächlich gelöscht wurde
        if ($stmt->rowCount() === 0) {
            throw new Exception('Film konnte nicht gelöscht werden');
        }
        
        // Transaktion bestätigen
        $pdo->commit();
        
        // Erfolgreiche Antwort
        echo json_encode([
            'success' => true,
            'message' => 'Film erfolgreich gelöscht',
            'data' => [
                'id' => $id,
                'title' => $movie['title']
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