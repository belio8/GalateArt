<?php
require_once __DIR__ . '/components/bootstrap.php';
require_once __DIR__ . '/config/Db.php';

// Ambil postingan populer berdasarkan jumlah likes terbanyak
$trending_posts = db_query(
    $conn,
    "SELECT p.id, p.title, p.image_url, p.like_count, u.username,
            COALESCE(NULLIF(u.avatar_url, ''), 'Assets/galateart_icon.png') AS avatar_url,
            COALESCE(GROUP_CONCAT(DISTINCT pt.tag ORDER BY pt.tag SEPARATOR ' '), '') AS tags
     FROM posts p
     JOIN users u ON u.id = p.artist_id
     LEFT JOIN post_tags pt ON pt.post_id = p.id
     WHERE p.status = 'active'
     GROUP BY p.id
     ORDER BY p.like_count DESC, p.created_at DESC
     LIMIT 12"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trending - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container">
        <section class="trending-posts">
            <h1>Trending</h1>
            <p>Karya seni terpopuler saat ini di GalateArt.</p>
            
            <div class="posts-grid">
                <?php if (empty($trending_posts)): ?>
                    <p style="text-align: center; color: #888; grid-column: 1 / -1;">Belum ada postingan trending saat ini.</p>
                <?php else: ?>
                    <?php foreach ($trending_posts as $post): ?>
                        <?php
                            $image = $post['image_url'] ?: 'Assets/draw2.png';
                            $tags = trim((string) $post['tags']);
                            $hashtags = $tags ? '#' . str_replace(' ', ' #', $tags) : '#trending #art';
                        ?>
                        <div class="post-card" style="cursor: pointer;"
                             data-post-id="<?= e($post['id']) ?>"
                             data-img="<?= e($image) ?>"
                             data-artist="@<?= e($post['username']) ?>"
                             data-avatar-url="<?= e($post['avatar_url']) ?>"
                             data-tags="<?= e($hashtags) ?>"
                             data-likes="<?= (int)$post['like_count'] ?>">
                            <img src="<?= e($image) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
                            <div class="post-info">
                                <p class="hashtags"><?= e($hashtags) ?></p>
                                <p class="artist-name">@<?= e($post['username']) ?></p>
                                <p class="likes"><i class="fas fa-heart"></i> <?= number_format((int)$post['like_count'], 0, ',', '.') ?> likes</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
        <?php include __DIR__ . '/components/art-modal.php'; ?>
    </main>
    <script src="js/utils.js"></script>
    <script src="js/navbar.js?v=<?= time() ?>"></script>
    <script src="js/auth.js?v=<?= time() ?>"></script>
    <script src="js/art-modal.js?v=<?= time() ?>"></script>
    <script src="js/report-modal.js?v=<?= time() ?>"></script>
</body>
</html>