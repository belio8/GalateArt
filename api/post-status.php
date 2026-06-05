<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$post_id = trim($_GET['post_id'] ?? '');
$user_id = $_SESSION['user_id'] ?? null;

if (!$post_id) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'post_id wajib diisi.']);
    exit;
}

$post = db_row($conn, "SELECT title, like_count, artist_id, description FROM posts WHERE id = ?", "s", [$post_id]);
if (!$post) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Post tidak ditemukan.']);
    exit;
}

$likes_count = (int)($post['like_count'] ?? 0);
$artist_id = $post['artist_id'] ?? null;

$liked = false;
$saved = false;
$is_following = false;

if ($user_id) {
    $like = db_row($conn, "SELECT id FROM likes WHERE user_id = ? AND post_id = ?", "ss", [$user_id, $post_id]);
    if ($like) $liked = true;

    $save = db_row($conn, "SELECT id FROM saves WHERE user_id = ? AND post_id = ?", "ss", [$user_id, $post_id]);
    if ($save) $saved = true;

    if ($artist_id) {
        $follow = db_row($conn, "SELECT id FROM follows WHERE follower_id = ? AND following_id = ?", "ss", [$user_id, $artist_id]);
        if ($follow) $is_following = true;
    }
}

echo json_encode([
    'status' => 'ok',
    'liked' => $liked,
    'saved' => $saved,
    'likes_count' => $likes_count,
    'artist_id' => $artist_id,
    'is_following' => $is_following,
    'description' => $post['description'] ?? '',
    'title' => $post['title'] ?? ''
]);
