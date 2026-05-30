<?php

// Izinkan request dari halaman HTML di folder yang sama (CORS lokal)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// -- Koneksi database --
require_once __DIR__ . '/../config/Db.php';

// -- Session --
session_start();

// -- Ambil body JSON --
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = trim($body['action'] ?? $_GET['action'] ?? '');

// ── Router ──────────────────────────────────────────────────
switch ($action) {
    case 'register':        handle_register($conn, $body);         break;
    case 'register_artist': handle_register_artist($conn, $body);  break;
    case 'login':           handle_login($conn, $body);            break;
    case 'logout':          handle_logout();                       break;
    case 'me':              handle_me($conn);                      break;
    default:
        json_response(['status' => 'error', 'message' => 'Action tidak dikenal.'], 400);
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
        json_response(['status' => 'error', 'message' => 'Username, email, dan password wajib diisi.'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['status' => 'error', 'message' => 'Format email tidak valid.'], 422);
    }
    if (strlen($password) < 6) {
        json_response(['status' => 'error', 'message' => 'Password minimal 6 karakter.'], 422);
    }

    // Cek apakah username atau email sudah ada
    $existing = db_row($conn,
        "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1",
        "ss", [$username, $email]
    );
    if ($existing) {
        json_response(['status' => 'error', 'message' => 'Username atau email sudah terdaftar.'], 409);
    }

    // Simpan ke database
    $id   = uuid();
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $affected = db_execute($conn,
        "INSERT INTO users (id, username, email, password_hash, role, created_at)
         VALUES (?, ?, ?, ?, 'regular', NOW())",
        "ssss", [$id, $username, $email, $hash]
    );

    if ($affected < 1) {
        json_response(['status' => 'error', 'message' => 'Gagal menyimpan akun. Coba lagi.'], 500);
    }

    // Langsung login setelah register
    $_SESSION['user_id']   = $id;
    $_SESSION['username']  = $username;
    $_SESSION['role']      = 'regular';

    json_response([
        'status'   => 'ok',
        'message'  => 'Akun berhasil dibuat!',
        'user'     => ['id' => $id, 'username' => $username, 'role' => 'regular'],
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
        json_response(['status' => 'error', 'message' => 'Username, email, dan password wajib diisi.'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['status' => 'error', 'message' => 'Format email tidak valid.'], 422);
    }
    if (strlen($password) < 6) {
        json_response(['status' => 'error', 'message' => 'Password minimal 6 karakter.'], 422);
    }

    // Cek duplikat
    $existing = db_row($conn,
        "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1",
        "ss", [$username, $email]
    );
    if ($existing) {
        json_response(['status' => 'error', 'message' => 'Username atau email sudah terdaftar.'], 409);
    }

    // Buat user dulu
    $user_id = uuid();
    $hash    = password_hash($password, PASSWORD_BCRYPT);

    db_execute($conn,
        "INSERT INTO users (id, username, email, password_hash, role, created_at)
         VALUES (?, ?, ?, ?, 'artist', NOW())",
        "ssss", [$user_id, $username, $email, $hash]
    );

    // Buat profil artis di tabel artist_profiles
    $profile_id = uuid();
    db_execute($conn,
        "INSERT INTO artist_profiles (id, user_id, portfolio_url, commission_status, created_at)
         VALUES (?, ?, ?, 'open', NOW())",
        "sss", [$profile_id, $user_id, $portfolio_url]
    );

    // Langsung login
    $_SESSION['user_id']  = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role']     = 'artist';

    json_response([
        'status'  => 'ok',
        'message' => 'Akun artis berhasil dibuat!',
        'user'    => ['id' => $user_id, 'username' => $username, 'role' => 'artist'],
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
        json_response(['status' => 'error', 'message' => 'Username dan password wajib diisi.'], 422);
    }

    // Cari user berdasarkan username ATAU email
    $user = db_row($conn,
        "SELECT id, username, email, password_hash, role, is_banned
         FROM users
         WHERE username = ? OR email = ?
         LIMIT 1",
        "ss", [$username, $username]
    );

    if (!$user) {
        json_response(['status' => 'error', 'message' => 'Username atau password salah.'], 401);
    }

    if (!password_verify($password, $user['password_hash'])) {
        json_response(['status' => 'error', 'message' => 'Username atau password salah.'], 401);
    }

    if ($user['is_banned']) {
        json_response(['status' => 'error', 'message' => 'Akun ini telah dinonaktifkan. Hubungi admin.'], 403);
    }

    // Simpan session
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    json_response([
        'status'  => 'ok',
        'message' => 'Login berhasil!',
        'user'    => [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'],
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
    json_response(['status' => 'ok', 'message' => 'Berhasil keluar.']);
}

// ============================================================
//  ME — cek siapa yang sedang login
// ============================================================
function handle_me(mysqli $conn): void
{
    if (empty($_SESSION['user_id'])) {
        json_response(['status' => 'error', 'message' => 'Belum login.'], 401);
    }

    $user = db_row($conn,
        "SELECT id, username, email, role FROM users WHERE id = ? LIMIT 1",
        "s", [$_SESSION['user_id']]
    );

    if (!$user) {
        session_destroy();
        json_response(['status' => 'error', 'message' => 'Session tidak valid.'], 401);
    }

    json_response(['status' => 'ok', 'user' => $user]);
}