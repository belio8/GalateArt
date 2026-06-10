<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
}

if (empty($_SESSION['user_id'])) {
    json_response(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.'], 403);
}

$artistUserId = $_SESSION['user_id'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$orderId = trim($body['order_id'] ?? '');
$status = trim($body['status'] ?? '');

if (!$orderId || !in_array($status, ['confirmed', 'cancelled'])) {
    json_response(['status' => 'error', 'message' => 'Data tidak valid.'], 400);
}

// Ensure the order belongs to the logged-in artist
$order = db_row($conn, "SELECT * FROM orders WHERE id = ? AND artist_id = ?", "ss", [$orderId, $artistUserId]);
if (!$order) {
    json_response(['status' => 'error', 'message' => 'Order tidak ditemukan atau Anda tidak memiliki akses.'], 404);
}

if ($order['status'] !== 'pending') {
    json_response(['status' => 'error', 'message' => 'Order ini sudah tidak pending.'], 400);
}

// Update the order status
$res = db_execute($conn, "UPDATE orders SET status = ? WHERE id = ?", "ss", [$status, $orderId]);
if ($res >= 0) {
    // Optionally send notification to the buyer
    $notifId = uuid();
    $artistUsername = $_SESSION['username'] ?? 'Artist';
    $actionStr = $status === 'confirmed' ? 'menerima' : 'menolak';
    $notifText = "@{$artistUsername} {$actionStr} commission request Anda.";
    
    db_execute(
        $conn,
        "INSERT INTO notifications (id, user_id, text, type, ref_id) VALUES (?, ?, ?, 'order', ?)",
        "ssss",
        [$notifId, $order['buyer_id'], $notifText, $orderId]
    );

    json_response(['status' => 'ok', 'message' => 'Status order berhasil diperbarui.']);
} else {
    json_response(['status' => 'error', 'message' => 'Gagal mengubah status order.'], 500);
}
