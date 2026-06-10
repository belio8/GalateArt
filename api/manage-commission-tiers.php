<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
}

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    json_response(['status' => 'error', 'message' => 'Hanya artist yang dapat mengatur tier.'], 403);
}

$artistUserId = $_SESSION['user_id'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

// Get artist_profile ID
$artistProfile = db_row($conn, "SELECT id FROM artist_profiles WHERE user_id = ?", "s", [$artistUserId]);
if (!$artistProfile) {
    json_response(['status' => 'error', 'message' => 'Profil artist tidak ditemukan.'], 404);
}
$apId = $artistProfile['id'];

if ($action === 'get') {
    $tiers = db_query(
        $conn,
        "SELECT * FROM commission_tiers WHERE artist_id = ? ORDER BY price ASC",
        "s",
        [$apId]
    );
    json_response(['status' => 'ok', 'tiers' => $tiers]);

} elseif ($action === 'save') {
    $tierId = trim($body['tier_id'] ?? '');
    $name = trim($body['name'] ?? '');
    $price = (float)($body['price'] ?? 0);
    $desc = trim($body['description'] ?? '');

    if (!$name || $price < 0) {
        json_response(['status' => 'error', 'message' => 'Nama dan harga tier valid wajib diisi.'], 400);
    }

    if ($tierId) {
        // Update
        $res = db_execute(
            $conn,
            "UPDATE commission_tiers SET name = ?, price = ?, description = ? WHERE id = ? AND artist_id = ?",
            "sdsss",
            [$name, $price, $desc, $tierId, $apId]
        );
        if ($res >= 0) {
            json_response(['status' => 'ok', 'message' => 'Tier berhasil diperbarui.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Gagal memperbarui tier.'], 500);
        }
    } else {
        // Insert
        $newId = uuid();
        $res = db_execute(
            $conn,
            "INSERT INTO commission_tiers (id, artist_id, name, price, description) VALUES (?, ?, ?, ?, ?)",
            "sssds",
            [$newId, $apId, $name, $price, $desc]
        );
        if ($res >= 0) {
            json_response(['status' => 'ok', 'message' => 'Tier berhasil ditambahkan.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Gagal menambahkan tier.'], 500);
        }
    }

} elseif ($action === 'delete') {
    $tierId = trim($body['tier_id'] ?? '');
    if (!$tierId) {
        json_response(['status' => 'error', 'message' => 'ID Tier tidak valid.'], 400);
    }

    $res = db_execute($conn, "DELETE FROM commission_tiers WHERE id = ? AND artist_id = ?", "ss", [$tierId, $apId]);
    if ($res >= 0) {
        json_response(['status' => 'ok', 'message' => 'Tier berhasil dihapus.']);
    } else {
        json_response(['status' => 'error', 'message' => 'Gagal menghapus tier.'], 500);
    }

} else {
    json_response(['status' => 'error', 'message' => 'Aksi tidak valid.'], 400);
}
