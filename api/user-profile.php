<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$username = trim($_GET['username'] ?? '');
$viewer_id = $_SESSION['user_id'] ?? null;

if (!$username) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'username wajib diisi.']);
    exit;
}

// Fetch user
$user = db_row($conn, "SELECT id, username, role, avatar_url, bio, banner_url FROM users WHERE username = ? AND is_banned = 0", "s", [$username]);

if (!$user) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
    exit;
}

$target_id = $user['id'];

// Follow counts
$followingRow = db_row($conn, "SELECT COUNT(*) AS cnt FROM follows WHERE follower_id = ?", "s", [$target_id]);
$followingCount = (int) ($followingRow['cnt'] ?? 0);

$followersRow = db_row($conn, "SELECT COUNT(*) AS cnt FROM follows WHERE following_id = ?", "s", [$target_id]);
$followersCount = (int) ($followersRow['cnt'] ?? 0);

// Post count (only for artists)
$postCount = 0;
$commissionStatus = null;

if ($user['role'] === 'artist') {
    $postRow = db_row($conn, "SELECT COUNT(*) AS cnt FROM posts WHERE artist_id = ? AND status = 'active'", "s", [$target_id]);
    $postCount = (int) ($postRow['cnt'] ?? 0);

    // Commission status
    $artistProfile = db_row($conn, "SELECT commission_status FROM artist_profiles WHERE user_id = ?", "s", [$target_id]);
    $commissionStatus = $artistProfile['commission_status'] ?? 'closed';
}

// Check if viewer is following this user
$isFollowing = false;
if ($viewer_id && $viewer_id !== $target_id) {
    $follow = db_row($conn, "SELECT id FROM follows WHERE follower_id = ? AND following_id = ?", "ss", [$viewer_id, $target_id]);
    $isFollowing = (bool) $follow;
}

echo json_encode([
    'status' => 'ok',
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'avatar_url' => $user['avatar_url'],
        'bio' => $user['bio'],
        'banner_url' => $user['banner_url'],
    ],
    'post_count' => $postCount,
    'following_count' => $followingCount,
    'followers_count' => $followersCount,
    'commission_status' => $commissionStatus,
    'is_following' => $isFollowing,
], JSON_UNESCAPED_UNICODE);
