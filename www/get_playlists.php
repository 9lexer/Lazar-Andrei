<?php
// www/get_playlists.php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // === AICI ESTE SCHIMBAREA ===
    // Selectăm playlist-urile unde:
    // 1. Ești creatorul (creator_id = TU)
    // 2. SAU ești în lista de colaboratori (playlist_id e în tabela collaborators unde user_id = TU)
    $sql = "
        SELECT p.* FROM playlists p
        WHERE p.creator_id = ? 
        OR p.playlist_id IN (
            SELECT playlist_id FROM playlist_collaborators WHERE user_id = ?
        )
        ORDER BY p.created_at DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $userId]);
    
    $playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($playlists);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>