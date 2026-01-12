<?php
// www/upload.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Trebuie să fii logat!']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['music_file'])) {
    
    $file = $_FILES['music_file'];
    $userId = $_SESSION['user_id'];
    
    // Preluăm datele din formular
    $title = $_POST['title'] ?? pathinfo($file['name'], PATHINFO_FILENAME);
    $artist = $_POST['artist'] ?? 'Unknown Artist';
    $album = $_POST['album'] ?? 'Unknown Album';

    $uploadDir = 'uploads/';
    
    // Permisiuni (pentru siguranță)
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileName = time() . "_" . basename($file['name']); // Nume unic
    $targetFilePath = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowedTypes = ['mp3', 'wav', 'ogg', 'm4a'];

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO songs (user_id, title, artist, album, file_path) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $title, $artist, $album, $targetFilePath]);
                
                echo json_encode(['success' => true, 'message' => 'Melodie încărcată!']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Eroare DB: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Eroare la scriere pe disc.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Format invalid.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nu s-a trimis fișierul.']);
}
?>