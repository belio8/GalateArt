<?php
require_once __DIR__ . '/../config/Db.php';

session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Anda harus login terlebih dahulu.';
    exit;
}

$postId = trim($_GET['post_id'] ?? '');
$userId = $_SESSION['user_id'];

if (!$postId) {
    http_response_code(400);
    echo 'post_id wajib diisi.';
    exit;
}

// Ambil data post
$post = db_row($conn, "SELECT id, artist_id, image_url, source_file_url, title, price, is_free FROM posts WHERE id = ? AND status = 'active'", "s", [$postId]);
if (!$post) {
    http_response_code(404);
    echo 'Post tidak ditemukan.';
    exit;
}

// Cek ownership: artis pemilik bisa download langsung
$isOwner = ($post['artist_id'] === $userId);

// Cek apakah sudah membeli
$purchased = db_row($conn, "SELECT id FROM post_purchases WHERE user_id = ? AND post_id = ?", "ss", [$userId, $postId]);

if (!$isOwner && !$purchased) {
    http_response_code(403);
    echo 'Anda belum membeli karya ini.';
    exit;
}

// Tentukan file yang akan di-download
$filePath = $post['source_file_url'];

if (!$filePath) {
    // Jika artis tidak mengunggah file source spesifik (ZIP/PSD),
    // berikan file gambar aslinya (tanpa watermark) dari folder originals
    if ($post['image_url']) {
        $filename = basename($post['image_url']);
        $filePath = 'Assets/uploads/originals/' . $filename;
    }
}

if (!$filePath) {
    http_response_code(404);
    echo 'File asli tidak tersedia.';
    exit;
}

$absolutePath = __DIR__ . '/../' . $filePath;

// Fallback untuk karya-karya lama yang diupload sebelum fitur pembelian dibuat.
// Karya lama hanya punya file di folder 'Assets/uploads/posts/' (karena dulu tidak ada folder originals)
if (!file_exists($absolutePath) && $post['image_url']) {
    $oldPath = __DIR__ . '/../' . $post['image_url'];
    if (file_exists($oldPath)) {
        $absolutePath = $oldPath;
        $filePath = $post['image_url'];
    }
}

if (!file_exists($absolutePath)) {
    http_response_code(404);
    echo 'File tidak ditemukan di server.';
    exit;
}

// Force download
$filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $post['title']) . '.' . pathinfo($absolutePath, PATHINFO_EXTENSION);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($absolutePath));

readfile($absolutePath);
exit;
