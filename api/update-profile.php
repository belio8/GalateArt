<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../components/bootstrap.php';
require_once __DIR__ . '/../config/Db.php';

require_login();
$userSession = current_user();
$userId = $userSession['id'];

$username = trim($_POST['username'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$image_url = null;
$banner_url = null;

// Validasi username
if (empty($username)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username tidak boleh kosong.']);
    exit;
}

if (strlen($username) < 3) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username minimal 3 karakter.']);
    exit;
}

if (strlen($username) > 30) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username maksimal 30 karakter.']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username hanya boleh mengandung huruf, angka, titik, garis bawah, dan tanda hubung.']);
    exit;
}

// Cek username sudah ada atau tidak (dan bukan milik user sendiri)
global $conn;
$existing_user = db_row($conn, "SELECT id FROM users WHERE username = ? AND id != ?", "ss", [$username, $userId]);
if ($existing_user) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan oleh pengguna lain.']);
    exit;
}

// Handle avatar upload
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['avatar'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed_types, true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Tipe file avatar harus PNG, JPG, atau WEBP.']);
        exit;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ukuran avatar maksimal 5 MB.']);
        exit;
    }
    
    $uploads_dir = __DIR__ . '/../Assets/uploads/avatars';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(12)) . '.' . $ext;
    $filepath = $uploads_dir . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file avatar.']);
        exit;
    }
    
    $image_url = 'Assets/uploads/avatars/' . $filename;
}

// Handle banner upload
if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['banner'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed_types, true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Tipe file banner harus PNG, JPG, atau WEBP.']);
        exit;
    }
    
    if ($file['size'] > 10 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ukuran banner maksimal 10 MB.']);
        exit;
    }
    
    $uploads_dir = __DIR__ . '/../Assets/uploads/banners';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(12)) . '.' . $ext;
    $filepath = $uploads_dir . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file banner.']);
        exit;
    }
    
    $banner_url = 'Assets/uploads/banners/' . $filename;
}

// Build dynamic UPDATE query
global $conn;

$fields = ['bio = ?', 'username = ?'];
$types  = 'ss';
$params = [$bio, $username];

if ($image_url) {
    $fields[] = 'avatar_url = ?';
    $types   .= 's';
    $params[] = $image_url;
}

if ($banner_url) {
    $fields[] = 'banner_url = ?';
    $types   .= 's';
    $params[] = $banner_url;
}

$types  .= 's';
$params[] = $userId;

$sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
$result = db_execute($conn, $sql, $types, $params);

if ($result === -1) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate profil di database.']);
    exit;
}

// Update the session variables so navbar changes immediately
$_SESSION['username'] = $username;
if ($image_url) {
    $_SESSION['avatar_url'] = $image_url;
}

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Profil berhasil diupdate!',
    'username' => $username,
    'avatar_url' => $image_url,
    'banner_url' => $banner_url,
]);

