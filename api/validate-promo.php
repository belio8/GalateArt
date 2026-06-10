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
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$code = trim(strtoupper($body['code'] ?? ''));

if (!$code) {
    json_response(['status' => 'error', 'message' => 'Kode promo wajib diisi.'], 400);
}

// Cek apakah kode valid dan aktif
$promo = db_row($conn, "SELECT discount_percent, expires_at FROM promo_codes WHERE code = ? AND is_active = 1", "s", [$code]);

if (!$promo) {
    json_response(['status' => 'error', 'message' => 'Kode promo tidak valid atau tidak aktif.'], 400);
}

// Cek kadaluwarsa
if ($promo['expires_at'] && strtotime($promo['expires_at']) < time()) {
    json_response(['status' => 'error', 'message' => 'Kode promo sudah kadaluwarsa.'], 400);
}

// Cek single-use per akun (maksimal 1 kali berhasil)
$usage = db_row($conn, "SELECT COUNT(*) as cnt FROM payments WHERE user_id = ? AND promo_code = ? AND status = 'success'", "ss", [$user_id, $code]);

if ($usage && $usage['cnt'] > 0) {
    json_response(['status' => 'error', 'message' => 'Kode promo ini hanya bisa digunakan maksimal 1 kali per akun.'], 400);
}

// Jika lolos semua validasi
json_response([
    'status' => 'ok',
    'message' => "Promo berhasil diterapkan!",
    'discount_percent' => (int)$promo['discount_percent']
]);
