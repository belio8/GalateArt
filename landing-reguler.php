<?php
require_once __DIR__ . '/components/bootstrap.php';
require_once __DIR__ . '/config/Db.php';
require_login('regular');

$posts = db_query(
    "SELECT p.id, p.title, p.image_url, p.like_count, p.price, p.is_free, p.is_nsfw, u.username,
     COALESCE(NULLIF(u.avatar_url, ''), 'Assets/galateart_icon.png') AS avatar_url,
     COALESCE(GROUP_CONCAT(DISTINCT pt.tag ORDER BY pt.tag SEPARATOR ' '), '') AS tags
     FROM posts p
     JOIN users u ON u.id = p.artist_id
     LEFT JOIN post_tags pt ON pt.post_id = p.id
     WHERE p.status = ?
     GROUP BY p.id, p.title, p.image_url, p.like_count, u.username, u.avatar_url
     ORDER BY p.created_at DESC
     LIMIT 12",
    ['active']
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GalateArt - Koleksi Seni Lokal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container">
                <section class="hero-grid">
            <div class="hero-card main-card" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('https://via.placeholder.com/600x400');">
                <div class="hero-text">
                    <img src="Assets/galateart_icon.png">
                    <h2>Dibuat oleh artis lokal</h2>
                    <p>Semua yang Anda butuhkan untuk vtuber, game, dan aset digital ada di sini!</p>
                </div>
            </div>
            <div class="hero-card ai-card" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('https://via.placeholder.com/600x400');">
                <div class="hero-text">
                    <img src="Assets/galateart_icon.png">
                    <h2>Tanpa Generative AI</h2>
                    <p>Kami menghargai orisinalitas. Karya tanpa persetujuan dan kredit tidak diterima.</p>
                </div>
            </div>
            <div class="hero-card talent-card" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('https://via.placeholder.com/600x400');">
                <div class="hero-text">
                    <img src="Assets/draw2.png">
                    <h2>Temukan Bakat</h2>
                    <p>Temukan artis dengan gaya seni dan budget yang cocok untuk Anda.</p>
                </div>
            </div>
        </section>

        <section class="art-grid">
            <?php foreach ($posts as $post): ?>
                <?php
                    $image = $post['image_url'] ?: 'Assets/draw2.png';
                    $tags = trim((string) ($post['tags'] ?? ''));
                    $hashtags = $tags ? '#' . str_replace(' ', ' #', $tags) : '#original #illustration';
                ?>
                <div class="art-card <?= !empty($post['is_nsfw']) ? 'is-nsfw' : '' ?>" style="cursor: pointer;"
                     data-post-id="<?= e($post['id']) ?>"
                     data-img="<?= e($image) ?>"
                     data-artist="@<?= e($post['username']) ?>"
                     data-avatar-url="<?= e($post['avatar_url']) ?>"
                     data-tags="<?= e($hashtags) ?>"
                     data-likes="<?= (int)$post['like_count'] ?>"
                     data-title="<?= e($post['title']) ?>">
                    
                    <?php if (!empty($post['is_nsfw'])): ?>
                        <span class="nsfw-badge">18+</span>
                    <?php endif; ?>
                    
                    <?php if (!empty($post['price']) && $post['price'] > 0): ?>
                        <span class="price-badge">Rp <?= number_format((float)$post['price'], 0, ',', '.') ?></span>
                    <?php endif; ?>

                    <img src="<?= e($image) ?>" alt="<?= e($post['title']) ?>">
                    <div class="card-avatar-wrap" onclick="event.stopPropagation(); window.location.href='visit-profile.php?user=<?= e($post['username']) ?>';">
                        <img class="card-avatar" src="<?= e($post['avatar_url']) ?>" alt="" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='Assets/galateart_icon.png';">
                        <span class="card-avatar-tooltip">@<?= e($post['username']) ?></span>
                    </div>
                    <div class="art-info">
                        <p class="art-title"><?= e($post['title']) ?></p>
                        <p class="hashtags"><?= e($hashtags) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
        <?php include __DIR__ . '/components/art-modal.php'; ?>
    </main>
    <!-- Core utilities (wajib dimuat pertama) -->
    <script src="js/utils.js"></script>
    <!-- Komponen navbar: hamburger, notifikasi, cart, pesan, search -->
    <script src="js/navbar.js?v=<?= time() ?>"></script>
    <!-- Autentikasi: login state, modal daftar/masuk/artis -->
    <script src="js/auth.js?v=<?= time() ?>"></script>
    <!-- Modal popup karya seni + follow button -->
    <script src="js/art-modal.js?v=<?= time() ?>"></script>
    <script src="js/report-modal.js?v=<?= time() ?>"></script>
</body>
</html>