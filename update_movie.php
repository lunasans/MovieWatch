<?php
require 'config/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$title = trim($data['title'] ?? '');
$count = (int)($data['count'] ?? 0);
$date = $data['date'] ?? null;
$tags = trim($data['tags'] ?? '');

if (!$id || $title === '') {
    echo json_encode(['success' => false, 'message' => 'Fehlende Daten']);
    exit;
}

// Titel aktualisieren
$stmt = $pdo->prepare("UPDATE movies SET title = ? WHERE id = ?");
$stmt->execute([$title, $id]);

// Sichtungen aktualisieren
$stmt = $pdo->prepare("DELETE FROM watch_logs WHERE movie_id = ?");
$stmt->execute([$id]);

for ($i = 0; $i < $count; $i++) {
    $watched_at = $date ?: date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO watch_logs (movie_id, watched_at) VALUES (?, ?)");
    $stmt->execute([$id, $watched_at]);
}

// Tags löschen und neu setzen
$stmt = $pdo->prepare("DELETE FROM movie_tags WHERE movie_id = ?");
$stmt->execute([$id]);

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
        $stmt->execute([$id, $tagId]);
    }
}

echo json_encode(['success' => true]);
