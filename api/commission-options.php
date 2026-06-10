<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Only allow GET ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan.']);
    exit;
}

// ── Get artist username from query string ───────────────────
$artistUsername = trim($_GET['artist'] ?? '');

if (!$artistUsername) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parameter artist diperlukan.']);
    exit;
}

// ── Fetch artist user ───────────────────────────────────────
$artistUser = db_row($conn, "SELECT id, username, avatar_url, bio FROM users WHERE username = ? AND role = 'artist' AND is_banned = 0", "s", [$artistUsername]);

if (!$artistUser) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Artist tidak ditemukan.']);
    exit;
}

// ── Fetch artist profile ────────────────────────────────────
$artistProfile = db_row($conn, "SELECT * FROM artist_profiles WHERE user_id = ?", "s", [$artistUser['id']]);

if (!$artistProfile) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Profil artist tidak ditemukan.']);
    exit;
}

// ── Fetch commission tiers ──────────────────────────────────
$tiers = db_query(
    $conn,
    "SELECT id, name, label, description, price, revision_count, commercial_use, status
     FROM commission_tiers
     WHERE artist_id = ? AND status = 'active'
     ORDER BY price ASC",
    "s",
    [$artistProfile['id']]
);

// ── Fetch commission options (dynamic) ──────────────────────
$options = db_query(
    $conn,
    "SELECT id, category, description, selection_type, is_required, sort_order
     FROM commission_options
     WHERE artist_id = ?
     ORDER BY sort_order ASC",
    "s",
    [$artistProfile['id']]
);

// For each option, fetch items
foreach ($options as &$opt) {
    $opt['items'] = db_query(
        $conn,
        "SELECT id, label, price_type, price_value, is_default, sort_order
         FROM commission_option_items
         WHERE option_id = ?
         ORDER BY sort_order ASC",
        "s",
        [$opt['id']]
    );
}
unset($opt);

// ── Fetch commission addons (legacy) ────────────────────────
$addons = db_query(
    $conn,
    "SELECT id, label, price, type
     FROM commission_addons
     WHERE artist_id = ?
     ORDER BY label ASC",
    "s",
    [$artistProfile['id']]
);

// ── Build response ──────────────────────────────────────────
echo json_encode([
    'status' => 'ok',
    'artist' => [
        'id'           => $artistUser['id'],
        'username'     => $artistUser['username'],
        'avatar_url'   => $artistUser['avatar_url'],
        'bio'          => $artistUser['bio'],
    ],
    'profile' => [
        'id'                => $artistProfile['id'],
        'commission_status' => $artistProfile['commission_status'],
        'tos'               => $artistProfile['tos'],
        'turnaround_days'   => $artistProfile['turnaround_days'],
    ],
    'tiers'   => $tiers,
    'options' => $options,
    'addons'  => $addons,
], JSON_UNESCAPED_UNICODE);
