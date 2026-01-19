<?php
// www/playlist_api.php
error_reporting(0); // Ascundem erorile PHP vizuale pentru a nu strica JSON-ul
ini_set('display_errors', 0);
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Trebuie să fii logat!']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents("php://input"), true);

try {
    // === 1. DETALII PLAYLIST ===
    if ($action === 'get_details') {
        $playlistId = $_GET['id'];
        
        $stmt = $pdo->prepare("SELECT p.*, (p.creator_id = ? OR EXISTS(SELECT 1 FROM playlist_collaborators WHERE playlist_id = p.playlist_id AND user_id = ?)) as has_access FROM playlists p WHERE p.playlist_id = ?");
        $stmt->execute([$userId, $userId, $playlistId]);
        $playlist = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$playlist || !$playlist['has_access']) {
            echo json_encode(['success' => false, 'message' => 'Acces interzis sau playlist inexistent.']);
            exit;
        }

        // Melodii
        $stmtSongs = $pdo->prepare("SELECT s.* FROM songs s JOIN playlist_songs ps ON s.song_id = ps.song_id WHERE ps.playlist_id = ? ORDER BY ps.added_at DESC");
        $stmtSongs->execute([$playlistId]);
        
        // Colaboratori
        $stmtCollab = $pdo->prepare("SELECT u.username FROM users u JOIN playlist_collaborators pc ON u.user_id = pc.user_id WHERE pc.playlist_id = ?");
        $stmtCollab->execute([$playlistId]);
        
        echo json_encode([
            'success' => true,
            'playlist' => $playlist,
            'songs' => $stmtSongs->fetchAll(PDO::FETCH_ASSOC),
            'collaborators' => $stmtCollab->fetchAll(PDO::FETCH_COLUMN),
            'is_owner' => ($playlist['creator_id'] == $userId)
        ]);
        exit;
    }

    // === VERIFICĂRI PERMISIUNI ===
    $playlistId = $input['playlist_id'] ?? 0;
    if (!$playlistId) throw new Exception("Playlist ID lipsă.");

    // === 2. ADĂUGARE COLABORATOR ===
    if ($action === 'add_collab') {
        $username = trim($input['username'] ?? '');
        if (!$username) throw new Exception("Nume utilizator lipsă.");

        // Căutăm userul
        $uStmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $uStmt->execute([$username]);
        $friend = $uStmt->fetch();

        if (!$friend) throw new Exception("Utilizatorul '$username' nu există.");
        if ($friend['user_id'] == $userId) throw new Exception("Nu te poți adăuga pe tine.");

        // Verificăm dacă e deja colaborator
        $check = $pdo->prepare("SELECT id FROM playlist_collaborators WHERE playlist_id = ? AND user_id = ?");
        $check->execute([$playlistId, $friend['user_id']]);
        if ($check->fetch()) throw new Exception("Este deja colaborator.");

        $ins = $pdo->prepare("INSERT INTO playlist_collaborators (playlist_id, user_id) VALUES (?, ?)");
        $ins->execute([$playlistId, $friend['user_id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    // === 3. ȘTERGERE COLABORATOR ===
    if ($action === 'remove_collab') {
        $username = trim($input['username'] ?? '');
        $del = $pdo->prepare("DELETE FROM playlist_collaborators WHERE playlist_id = ? AND user_id = (SELECT user_id FROM users WHERE username = ? LIMIT 1)");
        $del->execute([$playlistId, $username]);
        echo json_encode(['success' => true]);
        exit;
    }

    // === 4. ADĂUGARE/ȘTERGERE MELODII & PLAYLIST ===
    if ($action === 'add_song') {
        $sId = $input['song_id'];
        // Evităm duplicatele
        $chk = $pdo->prepare("SELECT id FROM playlist_songs WHERE playlist_id=? AND song_id=?");
        $chk->execute([$playlistId, $sId]);
        if(!$chk->fetch()) {
            $pdo->prepare("INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)")->execute([$playlistId, $sId]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
    if ($action === 'remove_song') {
        $pdo->prepare("DELETE FROM playlist_songs WHERE playlist_id=? AND song_id=?")->execute([$playlistId, $input['song_id']]);
        echo json_encode(['success' => true]);
        exit;
    }
    if ($action === 'delete_playlist') {
        $pdo->prepare("DELETE FROM playlists WHERE playlist_id=?")->execute([$playlistId]);
        echo json_encode(['success' => true]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>