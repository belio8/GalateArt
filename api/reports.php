<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Auth check ──────────────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Belum login. Silakan login terlebih dahulu.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Only accept POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Method tidak diizinkan. Gunakan POST.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Parse body ──────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true) ?? [];

$reporter_id = $_SESSION['user_id'];
$target_type = trim($body['target_type'] ?? '');  // 'post' or 'account'
$target_id   = trim($body['target_id'] ?? '');
$target_title = trim($body['target_title'] ?? '');
$reason      = trim($body['reason'] ?? '');

// ── Validate ────────────────────────────────────────────────
$valid_types   = ['post', 'account'];
$valid_reasons = ['sensitive', 'hashtag', 'ai', 'harass', 'hate', 'misrep', 'other'];

if (!in_array($target_type, $valid_types, true)) {
    http_response_code(422);
    echo json_encode([
        'status'  => 'error',
        'message' => 'target_type harus berupa "post" atau "account".',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($reason, $valid_reasons, true)) {
    http_response_code(422);
    echo json_encode([
        'status'  => 'error',
        'message' => 'reason tidak valid.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Insert into database ────────────────────────────────────
$id = uuid();

$target_user_id = null;
$target_post_id = null;

if ($target_type === 'account') {
    // target_id is the user_id or username; try to resolve
    // First try as user_id
    $user = db_row($conn, "SELECT id FROM users WHERE id = ? LIMIT 1", "s", [$target_id]);
    if (!$user) {
        // Try as username
        $clean_name = ltrim($target_id, '@');
        $user = db_row($conn, "SELECT id FROM users WHERE username = ? LIMIT 1", "s", [$clean_name]);
    }
    $target_user_id = $user['id'] ?? null;
} else {
    // target_type === 'post'
    // target_id might be a post id
    $post = db_row($conn, "SELECT id FROM posts WHERE id = ? LIMIT 1", "s", [$target_id]);
    $target_post_id = $post['id'] ?? null;
}

$result = db_execute(
    $conn,
    "INSERT INTO reports (id, reporter_id, target_user_id, target_post_id, target_type, reason, status, created_at)
     VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())",
    "ssssss",
    [$id, $reporter_id, $target_user_id, $target_post_id, $target_type, $reason]
);

if ($result < 0) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal menyimpan laporan.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'status'  => 'ok',
    'message' => 'Laporan berhasil dikirim. Tim admin akan meninjau.',
    'data'    => [
        'id'          => $id,
        'target_type' => $target_type,
        'reason'      => $reason,
    ],
], JSON_UNESCAPED_UNICODE);
