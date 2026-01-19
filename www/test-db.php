<?php
// === CONFIGURAȚIE PENTRU MUSIC PLAYER ===

// 1. HOST: În PDF era 'mysql', dar în docker-compose-ul tău serviciul se numește 'db'
$host = 'db'; 

// 2. PORT: În interiorul rețelei Docker, portul este standard 3306 (nu 3307!)
$port = 3306;

// 3. DATABASE: Numele bazei de date creată în DataGrip
$db   = 'music_player';

// 4. USER & PASS: Definite în docker-compose.yml (Environment variables)
$user = 'root';
$pass = 'root';

$charset = 'utf8mb4';

// Data Source Name (DSN) - șirul de conectare
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

// Opțiuni pentru PDO (exact ca în PDF [cite: 326])
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Încercăm conectarea
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Dacă ajunge aici, înseamnă că nu a dat eroare
    echo "<h1>Succes! 🎉</h1>";
    echo "Conexiunea la baza de date <strong>$db</strong> a fost realizată cu succes.";
    
} catch (\PDOException $e) {
    // Dacă apare o eroare, o afișăm (adaptat din PDF [cite: 334])
    echo "<h1>Eroare :(</h1>";
    echo "Nu s-a putut conecta la baza de date. <br>";
    echo "Mesaj eroare: " . $e->getMessage();
}
?>