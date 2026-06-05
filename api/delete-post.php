<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../components/bootstrap.php';
require_once __DIR__ . '/../config/Db.php';

require_login('artist');

$user = current_user();
$artist_id = $user['id'];

// Get JSON payload
$data = json_decode(file_get_contents('php://input'), true);
$post_id = $data['post_id'] ?? '';

if (empty($post_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Postingan tidak valid.']);
    exit;
}

global $conn;

// Verifikasi kepemilikan postingan
$post = db_row($conn, "SELECT id FROM posts WHERE id = ? AND artist_id = ?", "ss", [$post_id, $artist_id]);

if (!$post) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki akses untuk menghapus postingan ini.']);
    exit;
}

// Soft delete dengan update status
$result = db_execute($conn, "UPDATE posts SET status = 'deleted' WHERE id = ?", "s", [$post_id]);

if ($result === -1) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus postingan.']);
    exit;
}

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Postingan berhasil dihapus.'
]);
