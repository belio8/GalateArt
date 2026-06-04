<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Belum login.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$post_id = trim($body['post_id'] ?? '');
$user_id = $_SESSION['user_id'];

if (!$post_id) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'post_id wajib diisi.']);
    exit;
}

$existing = db_row($conn, "SELECT id FROM saves WHERE user_id = ? AND post_id = ?", "ss", [$user_id, $post_id]);

if ($existing) {
    db_execute($conn, "DELETE FROM saves WHERE id = ?", "s", [$existing['id']]);
    $saved = false;
} else {
    $id = uuid();
    db_execute($conn, "INSERT INTO saves (id, user_id, post_id) VALUES (?, ?, ?)", "sss", [$id, $user_id, $post_id]);
    $saved = true;
}

echo json_encode([
    'status' => 'ok',
    'saved' => $saved
]);
