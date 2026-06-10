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

$post = db_row($conn, "SELECT title, like_count, artist_id, description, price, is_free, source_file_url FROM posts WHERE id = ?", "s", [$post_id]);
if (!$post) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Post tidak ditemukan.']);
    exit;
}

$likes_count = (int)($post['like_count'] ?? 0);
$artist_id = $post['artist_id'] ?? null;
$price = (float)($post['price'] ?? 0);
$is_free = (int)($post['is_free'] ?? 0);
$has_source = !empty($post['source_file_url']);

$liked = false;
$saved = false;
$is_following = false;
$is_purchased = false;
$is_owner = false;
$is_in_cart = false;

if ($user_id) {
    $like = db_row($conn, "SELECT id FROM likes WHERE user_id = ? AND post_id = ?", "ss", [$user_id, $post_id]);
    if ($like) $liked = true;

    $save = db_row($conn, "SELECT id FROM saves WHERE user_id = ? AND post_id = ?", "ss", [$user_id, $post_id]);
    if ($save) $saved = true;

    if ($artist_id) {
        $follow = db_row($conn, "SELECT id FROM follows WHERE follower_id = ? AND following_id = ?", "ss", [$user_id, $artist_id]);
        if ($follow) $is_following = true;
    }

    // Cek apakah user adalah pemilik (artis pembuat)
    if ($user_id === $artist_id) {
        $is_owner = true;
    }

    // Cek apakah sudah pernah dibeli
    $purchase = db_row($conn, "SELECT id FROM post_purchases WHERE user_id = ? AND post_id = ?", "ss", [$user_id, $post_id]);
    if ($purchase) $is_purchased = true;

    // Cek apakah sudah ada di cart
    $inCart = db_row($conn, "SELECT id FROM cart_items WHERE user_id = ? AND item_type = 'post' AND post_id = ?", "ss", [$user_id, $post_id]);
    if ($inCart) $is_in_cart = true;
}

echo json_encode([
    'status' => 'ok',
    'liked' => $liked,
    'saved' => $saved,
    'likes_count' => $likes_count,
    'artist_id' => $artist_id,
    'is_following' => $is_following,
    'description' => $post['description'] ?? '',
    'title' => $post['title'] ?? '',
    // Purchase info
    'price' => $price,
    'is_free' => $is_free,
    'has_source' => $has_source,
    'is_purchased' => $is_purchased,
    'is_owner' => $is_owner,
    'is_in_cart' => $is_in_cart,
]);
