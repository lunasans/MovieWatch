<?php
require 'config/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$title = trim($data['title'] ?? '');
$tags = trim($data['tags'] ?? '');

if ($title === '') {
    echo json_encode(['success' => false, 'message' => 'Kein Titel angegeben']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO movies (title) VALUES (?)");
$stmt->execute([$title]);
$movie_id = $pdo->lastInsertId();

// Tags speichern
if ($tags !== '') {
    $tagArray = array_unique(array_filter(array_map('trim', explode(',', $tags))));
    foreach ($tagArray as $tagName) {
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

        $stmt = $pdo->prepare("INSERT INTO movie_tags (movie_id, tag_id) VALUES (?, ?)");
        $stmt->execute([$movie_id, $tagId]);
    }
}

echo json_encode(['success' => true, 'id' => $movie_id]);
