<?php
// www/get_music.php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Dacă nu e logat, returnăm o listă goală
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    // Selectăm toate melodiile userului, cele mai noi primele
    $stmt = $pdo->prepare("SELECT * FROM songs WHERE user_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$userId]);
    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($songs);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>