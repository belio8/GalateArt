<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan.']);
    exit;
}

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$status = trim($body['status'] ?? '');

$valid_statuses = ['open', 'closed', 'waitlist'];

if (!in_array($status, $valid_statuses, true)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Status tidak valid.']);
    exit;
}

// Update DB
$result = db_execute(
    $conn,
    "UPDATE artist_profiles SET commission_status = ? WHERE user_id = ?",
    "ss",
    [$status, $user_id]
);

if ($result < 0) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate status komisi.']);
    exit;
}

echo json_encode([
    'status' => 'ok',
    'message' => 'Status komisi berhasil diupdate.',
    'new_status' => $status
]);
