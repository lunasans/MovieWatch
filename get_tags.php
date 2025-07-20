<?php
require 'config/config.php';

$stmt = $pdo->query("SELECT name FROM tags ORDER BY name ASC");
$tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(array_map(fn($tag) => ['value' => $tag], $tags));
