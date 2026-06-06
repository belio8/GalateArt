<?php

// -- Koneksi database --
require_once __DIR__ . '/../config/Db.php';

// -- Session --
session_start();

$is_json = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
$body = $is_json ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
$action = trim($body['action'] ?? $_GET['action'] ?? '');

function wants_json(): bool
{
    return strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
        || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
}

function auth_redirect_for_role(string $role): string
{
    if ($role === 'admin') {
        return '../admin.php';
    }
    if ($role === 'artist') {
        return '../landing-artist.php';
    }
    if ($role === 'regular') {
        return '../landing-reguler.php';
    }

    return '../landing.php';
}

function auth_success(array $payload, int $code = 200): void
{
    if (wants_json()) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($payload);
        exit;
    }

    $role = $payload['user']['role'] ?? ($_SESSION['role'] ?? '');
    header('Location: ' . auth_redirect_for_role($role));
    exit;
}

function auth_error(string $message, int $code = 400, array $extra = []): void
{
    if (wants_json()) {
        header('Content-Type: application/json');
        http_response_code($code);
        $res = array_merge(['status' => 'error', 'message' => $message], $extra);
        echo json_encode($res);
        exit;
    }

    header('Location: ../landing.php?auth=login&error=' . urlencode($message));
    exit;
}

function gen_uuid(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// ── Router ──────────────────────────────────────────────────
switch ($action) {
    case 'register':        handle_register($conn, $body);         break;
    case 'register_artist': handle_register_artist($conn, $body);  break;
    case 'login':           handle_login($conn, $body);            break;
    case 'logout':          handle_logout();                       break;
    case 'me':              handle_me($conn);                      break;
    case 'appeal':          handle_appeal($conn, $body);           break;
    default:
        auth_error('Action tidak dikenal.', 400);
}

// ============================================================
//  REGISTER — Akun Regular
// ============================================================
function handle_register(mysqli $conn, array $body): void
{
    $username = trim($body['username'] ?? '');
    $email    = strtolower(trim($body['email'] ?? ''));
    $password = $body['password'] ?? '';

    // Validasi input
    if (!$username || !$email || !$password) {
        auth_error('Username, email, dan password wajib diisi.', 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        auth_error('Format email tidak valid.', 422);
    }
    if (strlen($password) < 6) {
        auth_error('Password minimal 6 karakter.', 422);
    }

    // Cek apakah username atau email sudah ada
    $existing = db_row($conn,
        "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1",
        "ss", [$username, $email]
    );
    if ($existing) {
        auth_error('Username atau email sudah terdaftar.', 409);
    }

    // Simpan ke database
    $id   = gen_uuid();
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $affected = db_execute($conn,
        "INSERT INTO users (id, username, email, password_hash, role, created_at)
         VALUES (?, ?, ?, ?, 'regular', NOW())",
        "ssss", [$id, $username, $email, $hash]
    );

    if ($affected < 1) {
        auth_error('Gagal menyimpan akun. Coba lagi.', 500);
    }

    // Langsung login setelah register
    $_SESSION['user_id']   = $id;
    $_SESSION['username']  = $username;
    $_SESSION['role']      = 'regular';
    $_SESSION['avatar_url'] = null;

    auth_success([
        'status'   => 'ok',
        'message'  => 'Akun berhasil dibuat!',
        'user'     => ['id' => $id, 'username' => $username, 'role' => 'regular', 'avatar_url' => null],
    ], 201);
}

// ============================================================
//  REGISTER ARTIST — Akun Artis
// ============================================================
function handle_register_artist(mysqli $conn, array $body): void
{
    $username       = trim($body['username'] ?? '');
    $email          = strtolower(trim($body['email'] ?? ''));
    $password       = $body['password'] ?? '';
    $portfolio_url  = trim($body['portfolio_url'] ?? '');

    if (!$username || !$email || !$password) {
        auth_error('Username, email, dan password wajib diisi.', 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        auth_error('Format email tidak valid.', 422);
    }
    if (strlen($password) < 6) {
        auth_error('Password minimal 6 karakter.', 422);
    }

    // Cek duplikat
    $existing = db_row($conn,
        "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1",
        "ss", [$username, $email]
    );
    if ($existing) {
        auth_error('Username atau email sudah terdaftar.', 409);
    }

    // Buat user dulu
    $user_id = gen_uuid();
    $hash    = password_hash($password, PASSWORD_BCRYPT);

    db_execute($conn,
        "INSERT INTO users (id, username, email, password_hash, role, created_at)
         VALUES (?, ?, ?, ?, 'artist', NOW())",
        "ssss", [$user_id, $username, $email, $hash]
    );

    // Buat profil artis di tabel artist_profiles
    $profile_id = gen_uuid();
    db_execute($conn,
        "INSERT INTO artist_profiles (id, user_id, portfolio_url, commission_status)
         VALUES (?, ?, ?, 'open')",
        "sss", [$profile_id, $user_id, $portfolio_url]
    );

    // Langsung login
    $_SESSION['user_id']  = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role']     = 'artist';
    $_SESSION['avatar_url'] = null;

    auth_success([
        'status'  => 'ok',
        'message' => 'Akun artis berhasil dibuat!',
        'user'    => ['id' => $user_id, 'username' => $username, 'role' => 'artist', 'avatar_url' => null],
    ], 201);
}

// ============================================================
//  LOGIN
// ============================================================
function handle_login(mysqli $conn, array $body): void
{
    $username = trim($body['username'] ?? '');
    $password = $body['password'] ?? '';

    if (!$username || !$password) {
        auth_error('Username dan password wajib diisi.', 422);
    }

    // Cari user berdasarkan username ATAU email
    $user = db_row($conn,
        "SELECT id, username, email, password_hash, role, is_banned, avatar_url
         FROM users
         WHERE username = ? OR email = ?
         LIMIT 1",
        "ss", [$username, $username]
    );

    if (!$user) {
        auth_error('Username atau password salah.', 401);
    }

    if (!password_verify($password, $user['password_hash'])) {
        auth_error('Username atau password salah.', 401);
    }

    if ($user['is_banned']) {
        auth_error('Akun ini telah dinonaktifkan. Hubungi admin.', 403, [
            'is_banned' => true,
            'banned_username' => $user['username']
        ]);
    }

    // Simpan session
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['avatar_url'] = $user['avatar_url'];

    auth_success([
        'status'  => 'ok',
        'message' => 'Login berhasil!',
        'user'    => [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'],
            'avatar_url' => $user['avatar_url']
        ],
    ]);
}

// ============================================================
//  LOGOUT
// ============================================================
function handle_logout(): void
{
    session_unset();
    session_destroy();
    if (!wants_json()) {
        header('Location: ../landing.php');
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'message' => 'Berhasil keluar.']);
    exit;
}

// ============================================================
//  ME — cek siapa yang sedang login
// ============================================================
function handle_me(mysqli $conn): void
{
    if (empty($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Belum login.']);
        exit;
    }

    $user = db_row($conn,
        "SELECT id, username, email, role, avatar_url FROM users WHERE id = ? LIMIT 1",
        "s", [$_SESSION['user_id']]
    );

    if (!$user) {
        session_destroy();
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Session tidak valid.']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'user' => $user]);
    exit;
}

// ============================================================
//  APPEAL (Banding Akun Banned)
// ============================================================
function handle_appeal(mysqli $conn, array $body): void
{
    $username = trim($body['username'] ?? '');
    $message  = trim($body['message'] ?? '');

    if (!$username || !$message) {
        auth_error('Username dan pesan wajib diisi.', 422);
    }

    $user = db_row($conn, "SELECT id FROM users WHERE username = ? LIMIT 1", "s", [$username]);
    if (!$user) {
        auth_error('Username tidak ditemukan.', 404);
    }

    $id = gen_uuid();
    $user_id = $user['id'];
    
    // Insert into reports table as an account report with 'other' reason.
    // Also store the message. We assume the table has a 'message' column now.
    $affected = db_execute($conn,
        "INSERT INTO reports (id, reporter_id, target_user_id, target_type, reason, message, created_at)
         VALUES (?, ?, ?, 'account', 'other', ?, NOW())",
        "ssss", [$id, $user_id, $user_id, $message]
    );

    if ($affected < 1) {
        auth_error('Gagal mengirim banding. Coba lagi.', 500);
    }

    auth_success([
        'status'  => 'ok',
        'message' => 'Pesan banding berhasil dikirim ke Admin.',
    ]);
}
