<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
}

if (empty($_SESSION['user_id'])) {
    json_response(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu.'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = trim($data['post_id'] ?? '');
$userId = $_SESSION['user_id'];

if (!$postId) {
    json_response(['status' => 'error', 'message' => 'post_id wajib diisi.'], 400);
}

// Ambil data post
$post = db_row($conn, "SELECT id, artist_id, price, is_free, title FROM posts WHERE id = ? AND status = 'active'", "s", [$postId]);
if (!$post) {
    json_response(['status' => 'error', 'message' => 'Post tidak ditemukan.'], 404);
}

// Tidak bisa beli postingan sendiri
if ($post['artist_id'] === $userId) {
    json_response(['status' => 'error', 'message' => 'Tidak bisa membeli postingan sendiri.'], 422);
}

// Cek apakah sudah pernah dibeli
$existing = db_row($conn, "SELECT id FROM post_purchases WHERE user_id = ? AND post_id = ?", "ss", [$userId, $postId]);
if ($existing) {
    json_response(['status' => 'error', 'message' => 'Anda sudah membeli karya ini.'], 422);
}

// Cek apakah sudah ada di cart
$inCart = db_row($conn, "SELECT id FROM cart_items WHERE user_id = ? AND item_type = 'post' AND post_id = ?", "ss", [$userId, $postId]);
if ($inCart) {
    json_response(['status' => 'error', 'message' => 'Karya ini sudah ada di keranjang Anda.'], 422);
}

$price = (float)$post['price'];

// Buat order dengan tipe 'post'
$orderId = uuid();
$result = db_execute(
    $conn,
    "INSERT INTO orders (id, order_type, buyer_id, artist_id, post_id, description, total_price, status)
     VALUES (?, 'post', ?, ?, ?, ?, ?, 'pending')",
    "sssssd",
    [$orderId, $userId, $post['artist_id'], $postId, 'Pembelian karya: ' . $post['title'], $price]
);

if ($result < 0) {
    json_response(['status' => 'error', 'message' => 'Gagal membuat order.'], 500);
}

// Tambahkan ke cart
$cartId = uuid();
db_execute(
    $conn,
    "INSERT INTO cart_items (id, user_id, order_id, item_type, post_id) VALUES (?, ?, ?, 'post', ?)",
    "ssss",
    [$cartId, $userId, $orderId, $postId]
);

// Notifikasi ke artis
$notifId = uuid();
$buyerUsername = $_SESSION['username'] ?? 'Someone';
$notifText = "@{$buyerUsername} menambahkan karya \"{$post['title']}\" ke keranjang";

db_execute(
    $conn,
    "INSERT INTO notifications (id, user_id, text, type, ref_id) VALUES (?, ?, ?, 'order', ?)",
    "ssss",
    [$notifId, $post['artist_id'], $notifText, $orderId]
);

json_response([
    'status' => 'ok',
    'message' => 'Karya berhasil ditambahkan ke keranjang!',
    'cart_id' => $cartId,
    'order_id' => $orderId,
    'price' => $price,
]);
