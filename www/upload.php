<?php
// www/upload.php

// 1. OPRIT AFISAREA ERORILOR HTML
ini_set('display_errors', 0);
error_reporting(0);

session_start();
require 'db.php';

header('Content-Type: application/json');

function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Trebuie să fii logat!');
}

$userId = $_SESSION['user_id'];

// === LOGICA DE ȘTERGERE (NOUĂ) ===
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $songId = $_POST['song_id'] ?? 0;

    if (!$songId) sendResponse(false, 'ID melodie invalid.');

    try {
        // 1. Găsim fișierul ca să îl ștergem fizic, dar verificăm că aparține user-ului!
        $stmt = $pdo->prepare("SELECT file_path FROM songs WHERE song_id = ? AND user_id = ?");
        $stmt->execute([$songId, $userId]);
        $song = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$song) {
            sendResponse(false, 'Melodia nu există sau nu ai dreptul să o ștergi.');
        }

        // 2. Ștergem fișierul de pe disc
        if (file_exists($song['file_path'])) {
            unlink($song['file_path']);
        }

        // 3. Ștergem înregistrarea din baza de date
        $delStmt = $pdo->prepare("DELETE FROM songs WHERE song_id = ?");
        $delStmt->execute([$songId]);

        sendResponse(true, 'Melodie ștearsă cu succes!');

    } catch (Exception $e) {
        sendResponse(false, 'Eroare server: ' . $e->getMessage());
    }
}

// === LOGICA DE UPLOAD (VECHE, NEATINSĂ) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['music_file'])) {
    
    $file = $_FILES['music_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        sendResponse(false, 'Eroare upload: ' . $file['error']);
    }
    
    $title = $_POST['title'] ?? pathinfo($file['name'], PATHINFO_FILENAME);
    $artist = $_POST['artist'] ?? 'Unknown Artist';
    $album = $_POST['album'] ?? 'Unknown Album';

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($file['name'])); 
    $targetFilePath = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    if (in_array($fileType, ['mp3', 'wav', 'ogg', 'm4a'])) {
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO songs (user_id, title, artist, album, file_path) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $title, $artist, $album, $targetFilePath]);
                sendResponse(true, 'Upload reușit!');
            } catch (PDOException $e) {
                unlink($targetFilePath);
                sendResponse(false, 'Database Error');
            }
        } else {
            sendResponse(false, 'Eroare la scrierea pe disc.');
        }
    } else {
        sendResponse(false, 'Format invalid.');
    }
}
?>