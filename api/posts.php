<?php
require_once __DIR__ . '/../config/Db.php';

header('Content-Type: application/json; charset=utf-8');

// ── Parameter ───────────────────────────────────────────────
$tag   = trim($_GET['tag'] ?? '');        // e.g. "#vtuber" or "vtuber"
$type  = trim($_GET['type'] ?? '');       // e.g. "Illustration"
$q     = trim($_GET['q'] ?? '');          // free text search
$sort  = trim($_GET['sort'] ?? 'newest'); // newest | popular | oldest
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = min(50, max(1, (int)($_GET['limit'] ?? 16)));

// Sanitize tag: remove leading #
$tagClean = ltrim($tag, '#');

// ── Build query ─────────────────────────────────────────────
$where   = ["p.status = 'active'"];
$params  = [];
$types   = '';

if ($tagClean !== '') {
    $where[] = "EXISTS (SELECT 1 FROM post_tags pt2 WHERE pt2.post_id = p.id AND pt2.tag LIKE ?)";
    $params[] = '%' . $tagClean . '%';
    $types   .= 's';
}

if ($type !== '') {
    // Type is stored as a tag in our system, so we search for it in tags
    // But if there's a specific column for type, use it. For now, search in tags.
    $where[] = "EXISTS (SELECT 1 FROM post_tags pt3 WHERE pt3.post_id = p.id AND pt3.tag LIKE ?)";
    $params[] = '%' . strtolower($type) . '%';
    $types   .= 's';
}

if ($q !== '') {
    $searchTerm = '%' . $q . '%';
    $where[] = "(p.title LIKE ? OR p.description LIKE ? OR u.username LIKE ? OR EXISTS (SELECT 1 FROM post_tags pt4 WHERE pt4.post_id = p.id AND pt4.tag LIKE ?))";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types   .= 'ssss';
}

$whereSQL = implode(' AND ', $where);

// ── Sorting ─────────────────────────────────────────────────
switch ($sort) {
    case 'popular':
        $orderBy = 'p.like_count DESC, p.created_at DESC';
        break;
    case 'oldest':
        $orderBy = 'p.created_at ASC';
        break;
    default: // newest
        $orderBy = 'p.created_at DESC';
        break;
}

$offset = ($page - 1) * $limit;

// ── Count total ─────────────────────────────────────────────
$countSQL = "SELECT COUNT(DISTINCT p.id) AS total
             FROM posts p
             JOIN users u ON u.id = p.artist_id
             WHERE $whereSQL";

$countRows = db_query($conn, $countSQL, $types, $params);
$total = $countRows[0]['total'] ?? 0;

// ── Fetch posts ─────────────────────────────────────────────
$sql = "SELECT p.id, p.title, p.description, p.image_url, p.price, p.is_free,
               p.is_nsfw, p.like_count, p.created_at,
               u.username AS artist,
               u.avatar_url AS artist_avatar,
               COALESCE(GROUP_CONCAT(DISTINCT pt.tag ORDER BY pt.tag SEPARATOR ','), '') AS tags_csv
        FROM posts p
        JOIN users u ON u.id = p.artist_id
        LEFT JOIN post_tags pt ON pt.post_id = p.id
        WHERE $whereSQL
        GROUP BY p.id
        ORDER BY $orderBy
        LIMIT $limit OFFSET $offset";

$posts = db_query($conn, $sql, $types, $params);

// ── Format output ───────────────────────────────────────────
$result = [];
foreach ($posts as $post) {
    $tagList = array_filter(explode(',', $post['tags_csv']));
    $tagsFormatted = array_map(fn($t) => '#' . ltrim($t, '#'), $tagList);
    $avatar = $post['artist_avatar'] ?: 'Assets/galateart_icon.png';

    $result[] = [
        'id'       => $post['id'],
        'img'      => $post['image_url'] ?: 'Assets/draw2.png',
        'title'    => $post['title'],
        'tags'     => $tagsFormatted,
        'artist'   => '@' . $post['artist'],
        'artist_avatar' => $avatar,
        'likes'    => (int) $post['like_count'],
        'type'     => $tagList[0] ?? 'Illustration', // first tag as type fallback
        'is_nsfw'  => (bool) $post['is_nsfw'],
        'is_free'  => (bool) $post['is_free'],
        'price'    => (float) $post['price'],
    ];
}

echo json_encode([
    'status' => 'ok',
    'total'  => (int) $total,
    'page'   => $page,
    'limit'  => $limit,
    'posts'  => $result,
], JSON_UNESCAPED_UNICODE);
