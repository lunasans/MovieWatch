<?php

require 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Film laden
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) {
    die("Film nicht gefunden.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    if ($title !== '') {
        $stmt = $pdo->prepare("UPDATE movies SET title = ? WHERE id = ?");
        $stmt->execute([$title, $id]);
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Film bearbeiten</title>
  <link rel="stylesheet" href="css/output.css">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <form method="post" class="bg-white p-6 rounded shadow max-w-sm w-full">
    <h1 class="text-xl font-bold mb-4">Film bearbeiten</h1>
    <input name="title" value="<?= htmlspecialchars($movie['title']) ?>" class="border w-full mb-4 p-2 rounded" required>
    <button class="bg-blue-500 text-white px-4 py-2 rounded w-full">Speichern</button>
    <a href="index.php" class="block text-center text-blue-500 mt-2">Zurück</a>
  </form>
</body>
</html>
