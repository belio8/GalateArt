<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Belum login.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$artist_id = trim($body['artist_id'] ?? '');
$user_id = $_SESSION['user_id'];

if (!$artist_id) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'artist_id wajib diisi.']);
    exit;
}

// Tidak bisa follow diri sendiri
if ($artist_id === $user_id) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Tidak bisa follow diri sendiri.']);
    exit;
}

$existing = db_row($conn, "SELECT id FROM follows WHERE follower_id = ? AND following_id = ?", "ss", [$user_id, $artist_id]);

if ($existing) {
    db_execute($conn, "DELETE FROM follows WHERE id = ?", "s", [$existing['id']]);
    $following = false;
} else {
    $id = uuid();
    db_execute($conn, "INSERT INTO follows (id, follower_id, following_id) VALUES (?, ?, ?)", "sss", [$id, $user_id, $artist_id]);
    $following = true;
}

echo json_encode([
    'status' => 'ok',
    'following' => $following
]);
