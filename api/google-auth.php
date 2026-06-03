<?php
/**
 * Google Sign-In handler.
 *
 * Receives a Google access_token from the front-end,
 * verifies it by calling Google's userinfo API,
 * then either logs in an existing user or creates a new "regular" account.
 */

require_once __DIR__ . '/../config/Db.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

// ── Only accept POST ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// ── Read the access token ─────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);
$access_token = trim($input['access_token'] ?? '');

if (!$access_token) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Access token tidak ditemukan.']);
    exit;
}

// ── Get user info from Google using the access token ──────────
$userinfo_url = 'https://www.googleapis.com/oauth2/v3/userinfo';

$ch = curl_init($userinfo_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $access_token,
    ],
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    http_response_code(401);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Token Google tidak valid atau kadaluarsa.',
        'debug'   => $curl_error ?: "HTTP $http_code",
    ]);
    exit;
}

$payload = json_decode($response, true);

// ── Extract user info ─────────────────────────────────────────
$google_id = $payload['sub']     ?? '';
$email     = $payload['email']   ?? '';
$name      = $payload['name']    ?? '';
$avatar    = $payload['picture'] ?? null;

if (!$google_id || !$email) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data Google tidak lengkap.']);
    exit;
}

// ── 1. Check if this Google ID already exists ─────────────────
$user = db_row($conn,
    "SELECT id, username, email, role, is_banned FROM users WHERE google_id = ? LIMIT 1",
    "s", [$google_id]
);

// ── 2. If not, check if the email already exists (link accounts) ──
if (!$user) {
    $user = db_row($conn,
        "SELECT id, username, email, role, is_banned FROM users WHERE email = ? LIMIT 1",
        "s", [$email]
    );

    if ($user) {
        // Link the Google ID to the existing account
        db_execute($conn,
            "UPDATE users SET google_id = ?, avatar_url = COALESCE(avatar_url, ?) WHERE id = ?",
            "sss", [$google_id, $avatar, $user['id']]
        );
    }
}

// ── 3. If still no user, create a new account ─────────────────
if (!$user) {
    $id = uuid();

    // Generate a unique username from the Google name
    $base_username = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', strtolower($name)));
    if (!$base_username) {
        $base_username = 'user';
    }
    $username = $base_username;
    $counter = 1;

    // Make sure the username is unique
    while (db_row($conn, "SELECT id FROM users WHERE username = ? LIMIT 1", "s", [$username])) {
        $username = $base_username . '_' . $counter;
        $counter++;
    }

    // Create user with a random password hash (they'll use Google to log in)
    $random_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

    $affected = db_execute($conn,
        "INSERT INTO users (id, username, email, password_hash, role, google_id, avatar_url, created_at)
         VALUES (?, ?, ?, ?, 'regular', ?, ?, NOW())",
        "ssssss", [$id, $username, $email, $random_hash, $google_id, $avatar]
    );

    if ($affected < 1) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal membuat akun. Coba lagi.']);
        exit;
    }

    $user = [
        'id'       => $id,
        'username' => $username,
        'email'    => $email,
        'role'     => 'regular',
        'is_banned' => 0,
    ];
}

// ── 4. Check if banned ────────────────────────────────────────
if (!empty($user['is_banned'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akun ini telah dinonaktifkan. Hubungi admin.']);
    exit;
}

// ── 5. Set session & respond ──────────────────────────────────
$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];

// Determine redirect URL based on role
$redirect = 'landing.php';
if ($user['role'] === 'admin') {
    $redirect = 'admin.php';
} elseif ($user['role'] === 'artist') {
    $redirect = 'landing-artist.php';
} elseif ($user['role'] === 'regular') {
    $redirect = 'landing-reguler.php';
}

echo json_encode([
    'status'   => 'ok',
    'message'  => 'Login Google berhasil!',
    'redirect' => $redirect,
    'user'     => [
        'id'       => $user['id'],
        'username' => $user['username'],
        'email'    => $user['email'],
        'role'     => $user['role'],
    ],
]);
