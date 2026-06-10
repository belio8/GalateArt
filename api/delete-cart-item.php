<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
}

if (empty($_SESSION['user_id'])) {
    json_response(['status' => 'error', 'message' => 'Belum login.'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);
$cartId = trim($data['cart_id'] ?? '');

if (!$cartId) {
    json_response(['status' => 'error', 'message' => 'ID Cart tidak valid.'], 400);
}

$user_id = $_SESSION['user_id'];

// Get cart item info
$cartRow = db_row($conn, "SELECT order_id, item_type, post_id FROM cart_items WHERE id = ? AND user_id = ?", "ss", [$cartId, $user_id]);

if (!$cartRow) {
    json_response(['status' => 'error', 'message' => 'Item tidak ditemukan atau Anda tidak memiliki akses.'], 404);
}

$orderId = $cartRow['order_id'];
$itemType = $cartRow['item_type'];

// Begin transaction
$conn->begin_transaction();

try {
    // Delete from cart_items
    $stmt1 = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt1->bind_param("s", $cartId);
    $stmt1->execute();
    $stmt1->close();
    
    // Delete the associated pending order
    if ($orderId) {
        $stmt2 = $conn->prepare("DELETE FROM orders WHERE id = ? AND status = 'pending'");
        $stmt2->bind_param("s", $orderId);
        $stmt2->execute();
        $stmt2->close();
    }
    
    $conn->commit();
    json_response(['status' => 'ok', 'message' => 'Item berhasil dihapus.']);
} catch (Exception $e) {
    $conn->rollback();
    json_response(['status' => 'error', 'message' => 'Gagal menghapus item dari keranjang.'], 500);
}
