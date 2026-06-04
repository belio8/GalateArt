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
$post_id = trim($body['post_id'] ?? '');
$user_id = $_SESSION['user_id'];

if (!$post_id) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'post_id wajib diisi.']);
    exit;
}

$existing = db_row($conn, "SELECT id FROM likes WHERE user_id = ? AND post_id = ?", "ss", [$user_id, $post_id]);

if ($existing) {
    db_execute($conn, "DELETE FROM likes WHERE id = ?", "s", [$existing['id']]);
    db_execute($conn, "UPDATE posts SET like_count = like_count - 1 WHERE id = ? AND like_count > 0", "s", [$post_id]);
    $liked = false;
} else {
    $id = uuid();
    db_execute($conn, "INSERT INTO likes (id, user_id, post_id) VALUES (?, ?, ?)", "sss", [$id, $user_id, $post_id]);
    db_execute($conn, "UPDATE posts SET like_count = like_count + 1 WHERE id = ?", "s", [$post_id]);
    $liked = true;
}

$post = db_row($conn, "SELECT like_count FROM posts WHERE id = ?", "s", [$post_id]);

echo json_encode([
    'status' => 'ok',
    'liked' => $liked,
    'likes_count' => (int)($post['like_count'] ?? 0)
]);
