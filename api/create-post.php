<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../components/bootstrap.php';
require_once __DIR__ . '/../config/Db.php';
require_once __DIR__ . '/../config/watermark.php';

// Pastikan user adalah artist
require_login('artist');

$user = current_user();
$artist_id = $user['id'];

// ── Validasi file upload ──────────────────────────────────────
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gambar harus diunggah.'
    ]);
    exit;
}

$file = $_FILES['image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($file['type'], $allowed_types, true)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Tipe file harus PNG, JPG, atau WEBP.'
    ]);
    exit;
}

if ($file['size'] > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ukuran gambar maksimal 10 MB.'
    ]);
    exit;
}

// ── Validasi form data ────────────────────────────────────────
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$tags_raw = trim($_POST['tags'] ?? '');
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$is_free = isset($_POST['is_free']) ? (int)$_POST['is_free'] : 0;
$is_nsfw = isset($_POST['is_nsfw']) ? (int)$_POST['is_nsfw'] : 0;

if (empty($title)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Judul karya tidak boleh kosong.'
    ]);
    exit;
}

if (empty($tags_raw)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Minimal satu hashtag harus ditambahkan.'
    ]);
    exit;
}

// ── Simpan file gambar asli ke directory ──────────────────────
$uploads_dir = __DIR__ . '/../Assets/uploads/posts';
$originals_dir = __DIR__ . '/../Assets/uploads/originals';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}
if (!is_dir($originals_dir)) {
    mkdir($originals_dir, 0755, true);
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = bin2hex(random_bytes(12)) . '.' . $ext;
$filepath_original = $originals_dir . '/' . $filename;
$filepath_preview  = $uploads_dir . '/' . $filename;

// Simpan file asli ke folder originals (untuk download nanti)
if (!move_uploaded_file($file['tmp_name'], $filepath_original)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan file gambar.'
    ]);
    exit;
}

// ── Generate preview dengan watermark ─────────────────────────
$hasPrice = ($price > 0 || $is_free == 1);

if ($hasPrice) {
    // Karya berbayar atau gratis (perlu order) → buat watermark preview
    $wmResult = apply_watermark($filepath_original, $filepath_preview);
    if (!$wmResult) {
        // Fallback: jika watermark gagal, copy file asli sebagai preview
        copy($filepath_original, $filepath_preview);
    }
} else {
    // Karya tanpa harga & bukan free download → tidak perlu watermark
    copy($filepath_original, $filepath_preview);
}

$image_url = 'Assets/uploads/posts/' . $filename;

// ── Handle Source File (opsional, misal ZIP/PSD) ──────────────
$source_file_url = null;
if (!empty($_FILES['source_file']) && $_FILES['source_file']['error'] === UPLOAD_ERR_OK) {
    $srcFile = $_FILES['source_file'];
    $allowed_src_types = [
        'application/zip', 'application/x-zip-compressed',
        'application/x-rar-compressed', 'application/vnd.rar',
        'application/octet-stream',
        'image/vnd.adobe.photoshop', 'application/photoshop',
        'image/jpeg', 'image/png', 'image/webp'
    ];

    if ($srcFile['size'] > 50 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Ukuran source file maksimal 50 MB.'
        ]);
        exit;
    }

    $srcDir = __DIR__ . '/../Assets/uploads/sources';
    if (!is_dir($srcDir)) {
        mkdir($srcDir, 0755, true);
    }

    $srcExt = pathinfo($srcFile['name'], PATHINFO_EXTENSION);
    $srcFilename = bin2hex(random_bytes(12)) . '.' . $srcExt;
    $srcFilepath = $srcDir . '/' . $srcFilename;

    if (move_uploaded_file($srcFile['tmp_name'], $srcFilepath)) {
        $source_file_url = 'Assets/uploads/sources/' . $srcFilename;
    }
}

// Jika tidak ada source file terpisah, gunakan file gambar asli sebagai downloadable
if (!$source_file_url) {
    $source_file_url = 'Assets/uploads/originals/' . $filename;
}

// ── Helper: Generate UUID ─────────────────────────────────────
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

// ── Buat record post di database ──────────────────────────────
global $conn;

$post_id = gen_uuid();
$sql_post = "INSERT INTO posts (id, artist_id, title, description, image_url, source_file_url, price, is_free, is_nsfw, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";

$types = 'ssssssdii';
$params = [$post_id, $artist_id, $title, $description, $image_url, $source_file_url, $price, $is_free, $is_nsfw];

$result = db_execute($conn, $sql_post, $types, $params);

if ($result === -1) {
    // Hapus file yang sudah diupload
    @unlink($filepath_original);
    @unlink($filepath_preview);
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan post ke database.'
    ]);
    exit;
}

// ── Parse & simpan tags ───────────────────────────────────────
$tags_array = array_filter(
    array_map(function($tag) {
        $tag = trim($tag);
        return (strpos($tag, '#') === 0 ? substr($tag, 1) : $tag);
    }, explode(' ', $tags_raw)),
    function($tag) { return !empty($tag); }
);

foreach ($tags_array as $tag) {
    $tag_id = gen_uuid();
    $sql_tag = "INSERT INTO post_tags (id, post_id, tag) VALUES (?, ?, ?)";
    db_execute($conn, $sql_tag, 'sss', [$tag_id, $post_id, $tag]);
}

// ── Return response ───────────────────────────────────────────
http_response_code(201);
echo json_encode([
    'status' => 'success',
    'message' => 'Postingan berhasil diunggah!',
    'data' => [
        'id' => $post_id,
        'artist_id' => $artist_id,
        'title' => $title,
        'description' => $description,
        'image_url' => $image_url,
        'source_file_url' => $source_file_url,
        'price' => $price,
        'is_free' => $is_free,
        'is_nsfw' => $is_nsfw,
        'tags' => $tags_array,
        'created_at' => date('Y-m-d H:i:s')
    ]
]);
