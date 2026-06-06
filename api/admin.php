<?php
/**
 * Admin API — serves dashboard data from the database.
 *
 * GET  ?action=stats          → dashboard stat counters
 * GET  ?action=reports[&status=pending|approved|rejected]  → list reports
 * GET  ?action=posts[&q=search]     → list posts
 * GET  ?action=accounts[&q=search]  → list accounts
 *
 * POST action=update_report   { id, status }          → approve/reject a report
 * POST action=delete_post     { id }                  → soft-delete (set removed)
 * POST action=toggle_ban      { id }                  → ban/unban user
 */

require_once __DIR__ . '/../components/bootstrap.php';
require_once __DIR__ . '/../config/Db.php';

header('Content-Type: application/json; charset=utf-8');

// ── Auth guard: admin only ──────────────────────────────────
$user = current_user();
if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = '';
$body   = [];

if ($method === 'GET') {
    $action = trim($_GET['action'] ?? '');
} elseif ($method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = trim($body['action'] ?? '');
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// ════════════════════════════════════════════════════════════
//  STATS
// ════════════════════════════════════════════════════════════
if ($action === 'stats') {
    $pending  = db_query("SELECT COUNT(*) AS c FROM reports WHERE status = 'pending'");
    $approved = db_query("SELECT COUNT(*) AS c FROM reports WHERE status = 'approved'");
    $posts    = db_query("SELECT COUNT(*) AS c FROM posts WHERE status != 'removed'");
    $accounts = db_query("SELECT COUNT(*) AS c FROM users");

    echo json_encode([
        'status' => 'ok',
        'data'   => [
            'pending'  => (int)($pending[0]['c']  ?? 0),
            'approved' => (int)($approved[0]['c'] ?? 0),
            'posts'    => (int)($posts[0]['c']    ?? 0),
            'accounts' => (int)($accounts[0]['c'] ?? 0),
        ],
    ]);
    exit;
}

// ════════════════════════════════════════════════════════════
//  REPORTS LIST
// ════════════════════════════════════════════════════════════
if ($action === 'reports') {
    $statusFilter = trim($_GET['status'] ?? 'all');

    $where  = '';
    $params = [];
    if (in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
        $where  = 'WHERE r.status = ?';
        $params = [$statusFilter];
    }

    $sql = "SELECT r.id,
                   r.target_type,
                   r.reason,
                   r.message,
                   r.status,
                   r.created_at,
                   CASE
                       WHEN r.target_type = 'post' THEN COALESCE(p.title, '(postingan dihapus)')
                       ELSE COALESCE(CONCAT('@', u_target.username), '(akun dihapus)')
                   END AS target_title,
                   CASE
                       WHEN r.target_type = 'post' THEN r.target_post_id
                       ELSE r.target_user_id
                   END AS target_id,
                   u_reporter.username AS reporter_username
            FROM reports r
            LEFT JOIN posts p            ON p.id = r.target_post_id
            LEFT JOIN users u_target     ON u_target.id = r.target_user_id
            LEFT JOIN users u_reporter   ON u_reporter.id = r.reporter_id
            $where
            ORDER BY r.created_at DESC";

    $rows = db_query($sql, $params);

    $reports = [];
    foreach ($rows as $row) {
        $reports[] = [
            'id'           => $row['id'],
            'type'         => $row['target_type'],
            'targetId'     => $row['target_id'],
            'targetTitle'  => $row['target_title'],
            'reason'       => $row['reason'],
            'message'      => $row['message'],
            'status'       => $row['status'],
            'createdAt'    => $row['created_at'],
            'reporter'     => $row['reporter_username'] ?? null,
        ];
    }

    echo json_encode(['status' => 'ok', 'data' => $reports], JSON_UNESCAPED_UNICODE);
    exit;
}

// ════════════════════════════════════════════════════════════
//  POSTS LIST
// ════════════════════════════════════════════════════════════
if ($action === 'posts') {
    $q = trim($_GET['q'] ?? '');

    $showRemoved = trim($_GET['show_removed'] ?? '0');
    $where  = $showRemoved === '1' ? "WHERE 1=1" : "WHERE p.status != 'removed'";
    $params = [];
    if ($q !== '') {
        $like    = '%' . $q . '%';
        $where  .= " AND (p.title LIKE ? OR u.username LIKE ?)";
        $params  = [$like, $like];
    }

    $sql = "SELECT p.id, p.title, p.like_count, p.status, p.image_url,
                   u.username AS artist,
                   COALESCE(GROUP_CONCAT(DISTINCT pt.tag ORDER BY pt.tag SEPARATOR ', '), '') AS tags
            FROM posts p
            JOIN users u ON u.id = p.artist_id
            LEFT JOIN post_tags pt ON pt.post_id = p.id
            $where
            GROUP BY p.id
            ORDER BY p.created_at DESC";

    $rows = db_query($sql, $params);

    $posts = [];
    foreach ($rows as $row) {
        $tagsRaw = array_filter(explode(', ', $row['tags']));
        $tags    = implode(' ', array_map(fn($t) => '#' . ltrim($t, '#'), $tagsRaw));

        $posts[] = [
            'id'     => $row['id'],
            'title'  => $row['title'],
            'artist' => '@' . $row['artist'],
            'tags'   => $tags,
            'likes'  => (int) $row['like_count'],
            'status' => $row['status'],
        ];
    }

    echo json_encode(['status' => 'ok', 'data' => $posts], JSON_UNESCAPED_UNICODE);
    exit;
}

// ════════════════════════════════════════════════════════════
//  ACCOUNTS LIST
// ════════════════════════════════════════════════════════════
if ($action === 'accounts') {
    $q = trim($_GET['q'] ?? '');

    $where  = '';
    $params = [];
    if ($q !== '') {
        $like   = '%' . $q . '%';
        $where  = "WHERE u.username LIKE ? OR u.email LIKE ?";
        $params = [$like, $like];
    }

    $sql = "SELECT u.id, u.username, u.role, u.is_banned,
                   (SELECT COUNT(*) FROM posts pp WHERE pp.artist_id = u.id AND pp.status != 'removed') AS post_count,
                   (SELECT COUNT(*) FROM follows f WHERE f.following_id = u.id) AS follower_count
            FROM users u
            $where
            ORDER BY u.created_at DESC";

    $rows = db_query($sql, $params);

    $accounts = [];
    foreach ($rows as $row) {
        $accounts[] = [
            'id'        => $row['id'],
            'name'      => $row['username'],
            'handle'    => '@' . $row['username'],
            'type'      => $row['role'] === 'artist' ? 'artist' : ($row['role'] === 'admin' ? 'admin' : 'user'),
            'posts'     => (int) $row['post_count'],
            'followers' => (int) $row['follower_count'],
            'status'    => $row['is_banned'] ? 'banned' : 'active',
        ];
    }

    echo json_encode(['status' => 'ok', 'data' => $accounts], JSON_UNESCAPED_UNICODE);
    exit;
}

// ════════════════════════════════════════════════════════════
//  UPDATE REPORT  (POST)
// ════════════════════════════════════════════════════════════
if ($action === 'update_report') {
    $id     = trim($body['id'] ?? '');
    $status = trim($body['status'] ?? '');

    if (!in_array($status, ['approved', 'rejected'], true) || $id === '') {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid.']);
        exit;
    }

    $result = db_execute($conn, "UPDATE reports SET status = ? WHERE id = ?", "ss", [$status, $id]);
    if ($result < 0) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui laporan.']);
        exit;
    }

    echo json_encode(['status' => 'ok', 'message' => $status === 'approved' ? 'Laporan disetujui.' : 'Laporan ditolak.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ════════════════════════════════════════════════════════════
//  DELETE POST  (POST)
// ════════════════════════════════════════════════════════════
if ($action === 'delete_post') {
    $id = trim($body['id'] ?? '');
    if ($id === '') {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'ID postingan wajib diisi.']);
        exit;
    }

    $result = db_execute($conn, "UPDATE posts SET status = 'removed' WHERE id = ?", "s", [$id]);
    if ($result < 0) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus postingan.']);
        exit;
    }

    echo json_encode(['status' => 'ok', 'message' => 'Postingan dihapus.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ════════════════════════════════════════════════════════════
//  TOGGLE BAN  (POST)
// ════════════════════════════════════════════════════════════
if ($action === 'toggle_ban') {
    $id = trim($body['id'] ?? '');
    if ($id === '') {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'ID akun wajib diisi.']);
        exit;
    }

    // Get current status
    $row = db_row($conn, "SELECT is_banned FROM users WHERE id = ?", "s", [$id]);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Akun tidak ditemukan.']);
        exit;
    }

    $newBanned = $row['is_banned'] ? 0 : 1;
    $result = db_execute($conn, "UPDATE users SET is_banned = ? WHERE id = ?", "is", [$newBanned, $id]);
    if ($result < 0) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status akun.']);
        exit;
    }

    // Check if user is an artist — if so, remove/restore all their posts
    $userRow = db_row($conn, "SELECT role FROM users WHERE id = ?", "s", [$id]);
    if ($userRow && $userRow['role'] === 'artist') {
        if ($newBanned) {
            // Banning: set all active/flagged posts to removed
            db_execute($conn, "UPDATE posts SET status = 'removed' WHERE artist_id = ? AND status IN ('active','flagged')", "s", [$id]);
        } else {
            // Unbanning: restore removed posts back to active
            db_execute($conn, "UPDATE posts SET status = 'active' WHERE artist_id = ? AND status = 'removed'", "s", [$id]);
        }
    }

    $msg = $newBanned ? 'Akun di-ban. Semua postingan di-remove.' : 'Akun di-unban. Postingan dipulihkan.';
    echo json_encode(['status' => 'ok', 'message' => $msg, 'is_banned' => (bool)$newBanned], JSON_UNESCAPED_UNICODE);
    exit;
}

// ════════════════════════════════════════════════════════════
//  RESTORE POST  (POST)
// ════════════════════════════════════════════════════════════
if ($action === 'restore_post') {
    $id = trim($body['id'] ?? '');
    if ($id === '') {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'ID postingan wajib diisi.']);
        exit;
    }

    $result = db_execute($conn, "UPDATE posts SET status = 'active' WHERE id = ? AND status = 'removed'", "s", [$id]);
    if ($result < 0) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal memulihkan postingan.']);
        exit;
    }

    echo json_encode(['status' => 'ok', 'message' => 'Postingan dipulihkan.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Unknown action ──────────────────────────────────────────
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali: ' . $action]);
