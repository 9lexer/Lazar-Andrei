<?php

$host = 'db'; 
$port = 3306;
$db   = 'music_player'; 
$user = 'root'; 
$pass = 'root'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Dacă apare o eroare gravă de conexiune, oprim tot și trimitem eroare JSON
    // Astfel JavaScript-ul va ști să afișeze alerta
    http_response_code(500);
    echo json_encode(['error' => 'Eroare conexiune baza de date: ' . $e->getMessage()]);
    exit;
}
?>