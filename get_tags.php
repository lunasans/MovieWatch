<?php
// Error Reporting ausschalten für JSON Response
ini_set('display_errors', 0);
error_reporting(0);

require 'config/config.php';

// Content-Type Header setzen
header('Content-Type: application/json; charset=utf-8');

try {
    // Tags aus der Datenbank laden
    $stmt = $pdo->prepare("SELECT DISTINCT name FROM tags ORDER BY name ASC");
    $stmt->execute();
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Für Tagify-Format konvertieren
    $tagList = [];
    foreach ($tags as $tag) {
        if (!empty(trim($tag))) {
            $tagList[] = ['value' => trim($tag)];
        }
    }
    
    // JSON-Response senden
    echo json_encode($tagList);
    
} catch (Exception $e) {
    // Fehler-Antwort
    echo json_encode([
        'error' => $e->getMessage(),
        'tags' => [] // Fallback: leeres Array
    ]);
}
?>