<?php
ini_set('display_errors', 0);
error_reporting(0);
require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    // Bereinige zuerst ungültige Tag-Einträge
    $pdo->exec("DELETE FROM tags WHERE name = '' OR name IS NULL OR name LIKE '[%' OR name LIKE '{%'");
    
    // Hole alle gültigen Tags
    $stmt = $pdo->query("
        SELECT DISTINCT name 
        FROM tags 
        WHERE name IS NOT NULL 
        AND name != '' 
        AND LENGTH(TRIM(name)) > 0
        AND name NOT REGEXP '^[\\[\\{]'
        ORDER BY usage_count DESC, name ASC
    ");
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $tagList = [];
    foreach ($tags as $tag) {
        $tagName = trim($tag);
        // Zusätzliche Validierung
        if (!empty($tagName) && 
            strlen($tagName) <= 50 && 
            preg_match('/^[a-zA-Z0-9\s\-_äöüÄÖÜß]+$/', $tagName)) {
            $tagList[] = ['value' => $tagName];
        }
    }
    
    echo json_encode($tagList);
    
} catch (Exception $e) {
    error_log('Tag fetch error: ' . $e->getMessage());
    echo json_encode([]);
}
?>