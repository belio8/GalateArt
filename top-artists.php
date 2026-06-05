<?php
require_once __DIR__ . '/components/bootstrap.php';
require_once __DIR__ . '/config/Db.php';

// Ambil top artist berdasarkan follower terbanyak
$top_artists = db_query(
    $conn,
    "SELECT u.id, u.username, u.bio, 
            COALESCE(NULLIF(u.avatar_url, ''), 'Assets/galateart_icon.png') AS avatar_url,
            (SELECT COUNT(*) FROM follows f WHERE f.following_id = u.id) AS follower_count,
            (SELECT COUNT(*) FROM posts p WHERE p.artist_id = u.id AND p.status = 'active') AS post_count
     FROM users u
     WHERE u.role = 'artist' AND u.is_banned = 0
     ORDER BY follower_count DESC, post_count DESC
     LIMIT 12"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Artist - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container">
        <section class="top-artists">
            <h1>Top Artist</h1>
            <p>Temukan artis terbaik di GalateArt dengan karya seni berkualitas tinggi.</p>
            
            <div class="artists-grid">
                <?php if (empty($top_artists)): ?>
                    <p style="text-align: center; color: #888; grid-column: 1 / -1;">Belum ada artis yang tersedia.</p>
                <?php else: ?>
                    <?php foreach ($top_artists as $artist): ?>
                        <div class="artist-card">
                            <a href="visit-profile.php?user=<?= e($artist['username']) ?>">
                                <img src="<?= e($artist['avatar_url']) ?>" alt="<?= e($artist['username']) ?>" class="artist-avatar" loading="lazy">
                            </a>
                            <div class="artist-details">
                                <h3><a href="visit-profile.php?user=<?= e($artist['username']) ?>" style="color: inherit; text-decoration: none;">@<?= e($artist['username']) ?></a></h3>
                                <p><?= e($artist['bio'] ?: 'Artis lokal aktif yang membuat karya luar biasa.') ?></p>
                                <div class="artist-stats">
                                    <span><strong><?= number_format((int)$artist['follower_count'], 0, ',', '.') ?></strong> Followers</span>
                                    <span><strong><?= number_format((int)$artist['post_count'], 0, ',', '.') ?></strong> Posts</span>
                                </div>
                                <button class="btn-follow" data-artist-id="<?= e($artist['id']) ?>">Follow</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/art-modal.js"></script>
    <script src="report-modal.js"></script>
</body>
</html>