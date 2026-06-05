<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../components/bootstrap.php';
require_once __DIR__ . '/../config/Db.php';

require_login('artist');

$user = current_user();
$artist_id = $user['id'];

$post_id = $_GET['id'] ?? '';

if (empty($post_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Postingan tidak valid.']);
    exit;
}

global $conn;

// Ambil data post
$sql = "SELECT p.id, p.title, p.description, p.price, p.is_free, p.is_nsfw,
               COALESCE(GROUP_CONCAT(pt.tag ORDER BY pt.id SEPARATOR ' '), '') AS tags
        FROM posts p
        LEFT JOIN post_tags pt ON pt.post_id = p.id
        WHERE p.id = ? AND p.artist_id = ? AND p.status = 'active'
        GROUP BY p.id";

$post = db_row($conn, $sql, "ss", [$post_id, $artist_id]);

if (!$post) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Postingan tidak ditemukan atau Anda tidak memiliki akses.']);
    exit;
}

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'data' => [
        'id' => $post['id'],
        'title' => $post['title'],
        'description' => $post['description'],
        'price' => (float)$post['price'],
        'is_free' => (int)$post['is_free'] === 1,
        'is_nsfw' => (int)$post['is_nsfw'] === 1,
        'tags' => $post['tags']
    ]
]);
