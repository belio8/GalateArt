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

$user_id = $_SESSION['user_id'];

// Ambil semua item di cart user ini
$cartItems = db_query("SELECT id, order_id, item_type, post_id FROM cart_items WHERE user_id = ?", [$user_id]);

if (empty($cartItems)) {
    json_response(['status' => 'error', 'message' => 'Keranjang kosong.'], 400);
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$method = $body['method'] ?? 'unknown';
$promoCode = strtoupper(trim($body['promo_code'] ?? ''));

$conn->begin_transaction();
try {
    $orderCode = '';
    foreach ($cartItems as $item) {
        $orderId = $item['order_id'];
        $itemType = $item['item_type'];
        $postId = $item['post_id'];

        // Update status order
        if ($orderId) {
            $newStatus = ($itemType === 'post') ? 'completed' : 'confirmed';
            
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("ss", $newStatus, $orderId);
            $stmt->execute();
            $stmt->close();

            // Ambil total_price dari orders
            $orderRow = db_row($conn, "SELECT total_price FROM orders WHERE id = ?", "s", [$orderId]);
            $basePrice = (float)($orderRow['total_price'] ?? 0);
            
            // Kalkulasi Fees & Discounts
            $platformFee = round($basePrice * 0.05);
            $discountAmount = 0;
            
            if ($promoCode) {
                // Validasi promo ulang di backend
                $promo = db_row($conn, "SELECT discount_percent, expires_at FROM promo_codes WHERE code = ? AND is_active = 1", "s", [$promoCode]);
                $usage = db_row($conn, "SELECT COUNT(*) as cnt FROM payments WHERE user_id = ? AND promo_code = ? AND status = 'success'", "ss", [$user_id, $promoCode]);
                
                if ($promo && (!$promo['expires_at'] || strtotime($promo['expires_at']) >= time()) && (!$usage || $usage['cnt'] == 0)) {
                    $pct = $promo['discount_percent'];
                    $discountAmount = round(($basePrice + $platformFee) * ($pct / 100));
                } else {
                    $promoCode = null; // Promo tidak valid, hapus dari catatan
                }
            }
            
            $totalPaid = $basePrice + $platformFee - $discountAmount;
            
            // Catat di tabel payments
            $paymentId = uuid();
            $orderCode = 'GAL-' . strtoupper(substr(md5($paymentId), 0, 6));
            $sqlPay = "INSERT INTO payments (id, order_id, user_id, order_code, amount, platform_fee, discount_amount, total_paid, method, promo_code, status, paid_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'success', CURRENT_TIMESTAMP)";
            
            $stmtPay = $conn->prepare($sqlPay);
            $stmtPay->bind_param("ssssddddss", $paymentId, $orderId, $user_id, $orderCode, $basePrice, $platformFee, $discountAmount, $totalPaid, $method, $promoCode);
            $stmtPay->execute();
            $stmtPay->close();

            if ($itemType === 'post' && $postId) {
                // Catat pembelian post
                $purchaseId = uuid();
                
                $stmt2 = $conn->prepare("INSERT INTO post_purchases (id, user_id, post_id, price_paid) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE price_paid = price_paid");
                $stmt2->bind_param("sssd", $purchaseId, $user_id, $postId, $basePrice);
                $stmt2->execute();
                $stmt2->close();
                
                // Notifikasi ke artis pemilik karya
                $postOwner = db_row($conn, "SELECT artist_id, title FROM posts WHERE id = ?", "s", [$postId]);
                if ($postOwner && $postOwner['artist_id'] !== $user_id) {
                    $artistId = $postOwner['artist_id'];
                    $title = $postOwner['title'];
                    $notifId = uuid();
                    $text = "Karya aset Anda '$title' baru saja dibeli.";
                    
                    $stmtNotif = $conn->prepare("INSERT INTO notifications (id, user_id, text, type, ref_id) VALUES (?, ?, ?, 'order', ?)");
                    $stmtNotif->bind_param("ssss", $notifId, $artistId, $text, $orderId);
                    $stmtNotif->execute();
                    $stmtNotif->close();
                }
            }
        }

        // Hapus dari cart
        $stmt3 = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
        $stmt3->bind_param("s", $item['id']);
        $stmt3->execute();
        $stmt3->close();
    }

    $conn->commit();
    json_response(['status' => 'ok', 'message' => 'Pembayaran berhasil dikonfirmasi.', 'order_code' => $orderCode]);
} catch (Exception $e) {
    $conn->rollback();
    json_response(['status' => 'error', 'message' => 'Terjadi kesalahan saat memproses pembayaran.'], 500);
}
