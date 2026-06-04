<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // ── GET Comments ──────────────────────────────────────────
    $post_id = trim($_GET['post_id'] ?? '');
    
    if (!$post_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'post_id required.']);
        exit;
    }

    $sql = "SELECT c.id, c.content, c.created_at, u.username, 
                   COALESCE(NULLIF(u.avatar_url, ''), 'Assets/galateart_icon.png') AS avatar_url
            FROM comments c
            JOIN users u ON u.id = c.user_id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC";
            
    $comments = db_query($conn, $sql, "s", [$post_id]);
    
    $result = [];
    foreach ($comments as $c) {
        $result[] = [
            'id' => $c['id'],
            'author' => '@' . $c['username'],
            'avatar_url' => $c['avatar_url'],
            'content' => $c['content'],
            'time' => format_time_ago($c['created_at'])
        ];
    }
    
    echo json_encode(['status' => 'ok', 'comments' => $result]);
    exit;
} 
elseif ($method === 'POST') {
    // ── POST Comment ──────────────────────────────────────────
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Belum login.']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $post_id = trim($body['post_id'] ?? '');
    $content = trim($body['content'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (!$post_id || !$content) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'post_id dan content wajib diisi.']);
        exit;
    }
    
    $id = uuid();
    $res = db_execute($conn, "INSERT INTO comments (id, user_id, post_id, content) VALUES (?, ?, ?, ?)", "ssss", [$id, $user_id, $post_id, $content]);
    
    if ($res >= 0) {
        // Ambil data user
        $u = db_row($conn, "SELECT username, COALESCE(NULLIF(avatar_url, ''), 'Assets/galateart_icon.png') AS avatar_url FROM users WHERE id = ?", "s", [$user_id]);
        
        echo json_encode([
            'status' => 'ok', 
            'comment' => [
                'id' => $id,
                'author' => '@' . $u['username'],
                'avatar_url' => $u['avatar_url'],
                'content' => $content,
                'time' => 'baru saja'
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan komentar.']);
    }
    exit;
}

function format_time_ago($datetime) {
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'baru saja';
    if ($diff < 3600) return floor($diff / 60) . 'm';
    if ($diff < 86400) return floor($diff / 3600) . 'j';
    return floor($diff / 86400) . 'h';
}
