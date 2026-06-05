<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../components/bootstrap.php';
require_once __DIR__ . '/../config/Db.php';

require_login('artist');

$user = current_user();
$artist_id = $user['id'];

$data = json_decode(file_get_contents('php://input'), true);

$post_id = trim($data['post_id'] ?? '');
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$tags_raw = trim($data['tags'] ?? '');
$price = isset($data['price']) ? (float)$data['price'] : 0;
$is_free = !empty($data['is_free']) ? 1 : 0;
$is_nsfw = !empty($data['is_nsfw']) ? 1 : 0;

if (empty($post_id) || empty($title) || empty($tags_raw)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Judul karya, Hashtag, dan ID Postingan wajib diisi.'
    ]);
    exit;
}

global $conn;

// Verifikasi kepemilikan
$post = db_row($conn, "SELECT id FROM posts WHERE id = ? AND artist_id = ?", "ss", [$post_id, $artist_id]);
if (!$post) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit;
}

// Helper UUID
function gen_uuid_edit(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Mulai transaksi
$conn->begin_transaction();

try {
    // Update posts
    $sql_update = "UPDATE posts SET title = ?, description = ?, price = ?, is_free = ?, is_nsfw = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssdiis", $title, $description, $price, $is_free, $is_nsfw, $post_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Hapus tags lama
    $sql_del_tags = "DELETE FROM post_tags WHERE post_id = ?";
    $stmt_del = $conn->prepare($sql_del_tags);
    $stmt_del->bind_param("s", $post_id);
    $stmt_del->execute();
    $stmt_del->close();

    // Parse & simpan tags baru
    $tags_array = array_filter(
        array_map(function($tag) {
            $tag = trim($tag);
            return (strpos($tag, '#') === 0 ? substr($tag, 1) : $tag);
        }, explode(' ', $tags_raw)),
        function($tag) { return !empty($tag); }
    );

    $sql_add_tag = "INSERT INTO post_tags (id, post_id, tag) VALUES (?, ?, ?)";
    $stmt_add = $conn->prepare($sql_add_tag);
    foreach ($tags_array as $tag) {
        $tag_id = gen_uuid_edit();
        $stmt_add->bind_param("sss", $tag_id, $post_id, $tag);
        $stmt_add->execute();
    }
    $stmt_add->close();

    $conn->commit();

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Postingan berhasil diperbarui.',
        'data' => [
            'id' => $post_id,
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'is_free' => $is_free,
            'is_nsfw' => $is_nsfw,
            'tags' => $tags_array
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui postingan.']);
}
