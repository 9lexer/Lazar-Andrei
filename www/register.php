<?php
// www/register.php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Preluăm datele trimise de formular
    // Folosim operatorul ?? '' pentru a evita erori dacă câmpul lipsește
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2. Validare simplă
    if (empty($username) || empty($email) || empty($password)) {
        die("Te rog să completezi toate câmpurile!");
    }

    // 3. Verificăm dacă userul SAU emailul există deja
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        die("Eroare: Numele de utilizator sau emailul este deja folosit.");
    }

    // 4. Criptăm parola (Obligatoriu pentru securitate)
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // 5. Inserăm în baza de date
    try {
        $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $passwordHash]);

        // Redirecționăm utilizatorul către pagina de login
        header("Location: login.html");
        exit;

    } catch (PDOException $e) {
        echo "Eroare la baza de date: " . $e->getMessage();
    }
}
?>