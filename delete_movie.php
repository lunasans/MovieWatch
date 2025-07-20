<?php
require 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);

if (!$id) {
    echo json_encode(['success'=>false]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
$stmt->execute([$id]);

$stmt = $pdo->prepare("DELETE FROM watch_logs WHERE movie_id = ?");
$stmt->execute([$id]);

echo json_encode(['success'=>true]);
