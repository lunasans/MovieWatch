<?php
ini_set('display_errors', 0);
error_reporting(0);
require 'config/config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->query("SELECT DISTINCT name FROM tags ORDER BY name ASC");
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $tagList = [];
    foreach ($tags as $tag) {
        if (!empty(trim($tag)) && !preg_match('/^[\[{]/', $tag)) {
            $tagList[] = ['value' => trim($tag)];
        }
    }
    
    echo json_encode($tagList);
    
} catch (Exception $e) {
    echo json_encode([]);
}
?>