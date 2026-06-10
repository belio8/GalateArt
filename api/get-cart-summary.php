<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    json_response(['status' => 'error', 'message' => 'Belum login.'], 401);
}

$user_id = $_SESSION['user_id'];

// Ambil item terakhir dari cart (simplifikasi untuk payment flow saat ini)
// Di masa depan bisa dilooping untuk ambil total semua item
$cartItems = db_query("SELECT c.id, c.order_id, c.item_type, c.post_id, o.total_price, o.artist_id 
                       FROM cart_items c 
                       LEFT JOIN orders o ON c.order_id = o.id 
                       WHERE c.user_id = ? 
                       ORDER BY c.added_at DESC LIMIT 1", [$user_id]);

if (empty($cartItems)) {
    json_response(['status' => 'error', 'message' => 'Keranjang kosong.'], 400);
}

$item = $cartItems[0];
$artist_username = 'artis_lokal';
$title = 'Commission / Aset';
$badge_text = 'Item Terpilih';

// Ambil info artist
$artist = db_row($conn, "SELECT username FROM users WHERE id = ?", "s", [$item['artist_id']]);
if ($artist) {
    $artist_username = $artist['username'];
}

if ($item['item_type'] === 'post') {
    $post = db_row($conn, "SELECT title FROM posts WHERE id = ?", "s", [$item['post_id']]);
    if ($post) {
        $title = $post['title'];
        $badge_text = 'Digital Asset';
    }
} else {
    // Commission
    $order = db_row($conn, "SELECT t.name FROM orders o JOIN commission_tiers t ON o.tier_id = t.id WHERE o.id = ?", "s", [$item['order_id']]);
    if ($order) {
        $title = 'Commission: ' . $order['name'];
        $badge_text = 'Custom Commission';
    }
}

json_response([
    'status' => 'ok',
    'data' => [
        'total_price' => $item['total_price'],
        'title' => $title,
        'artist_username' => $artist_username,
        'badge_text' => $badge_text
    ]
]);
