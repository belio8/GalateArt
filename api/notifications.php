<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Auth check ──────────────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'status'        => 'ok',
        'notifications' => [],
        'message'       => 'Belum login.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$user_id = $_SESSION['user_id'];

// ── Fetch notifications ─────────────────────────────────────
$notifications = db_query(
    $conn,
    "SELECT id, text, type, is_read, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 20",
    "s",
    [$user_id]
);

// ── Format output ───────────────────────────────────────────
$result = [];
foreach ($notifications as $n) {
    $result[] = [
        'id'      => $n['id'],
        'text'    => $n['text'],
        'type'    => $n['type'],
        'is_read' => (bool) $n['is_read'],
        'time'    => format_notif_time($n['created_at']),
    ];
}

// Count unread
$unreadRows = db_query(
    $conn,
    "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0",
    "s",
    [$user_id]
);
$unreadCount = $unreadRows[0]['cnt'] ?? 0;

echo json_encode([
    'status'        => 'ok',
    'unread_count'  => (int) $unreadCount,
    'notifications' => $result,
], JSON_UNESCAPED_UNICODE);

// ── Helper ──────────────────────────────────────────────────
function format_notif_time(string $datetime): string
{
    $ts   = strtotime($datetime);
    $diff = time() - $ts;

    if ($diff < 60)    return 'Baru saja';
    if ($diff < 3600)  return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';

    return date('d/m/Y', $ts);
}
