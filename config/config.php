<?php
$host = 'localhost';
$db   = 'moviewatch';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("DB-Verbindung fehlgeschlagen: " . $e->getMessage());
}

// Session nur starten, wenn sie nicht läuft
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
