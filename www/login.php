<?php
// www/login.php
session_start(); // Pornim sesiunea
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        die("Completează user și parolă!");
    }

    // 1. Căutăm utilizatorul în baza de date
    $stmt = $pdo->prepare("SELECT user_id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 2. Verificăm parola criptată
    if ($user && password_verify($password, $user['password_hash'])) {
        // SUCCES! Utilizatorul este autentificat.
        
        // Salvăm datele în sesiune (ca să știe și dashboard.php cine ești)
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        // Trimitem la Dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        // Eșec
        echo "Nume sau parolă greșită! <a href='login.html'>Mai încearcă</a>";
    }
}
?>