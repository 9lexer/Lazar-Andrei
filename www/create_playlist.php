<?php
// www/create_playlist.php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Auth required']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Numele este obligatoriu']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO playlists (creator_id, name) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $name]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>