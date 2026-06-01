<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login('artist');
require_once __DIR__ . '/config/Db.php';

$user = current_user();
$userId = $user['id'];

$postRow = db_row(
    $conn,
    "SELECT COUNT(*) AS cnt FROM posts WHERE artist_id = ? AND status = 'active'",
    "s",
    [$userId]
);
$postCount = (int) ($postRow['cnt'] ?? 0);

$followingRow = db_row(
    $conn,
    "SELECT COUNT(*) AS cnt FROM follows WHERE follower_id = ?",
    "s",
    [$userId]
);
$followingCount = (int) ($followingRow['cnt'] ?? 0);

$followersRow = db_row(
    $conn,
    "SELECT COUNT(*) AS cnt FROM follows WHERE following_id = ?",
    "s",
    [$userId]
);
$followersCount = (int) ($followersRow['cnt'] ?? 0);

$postRows = db_query(
    $conn,
    "SELECT p.id, p.image_url, p.like_count, p.title, p.description,
            COALESCE(GROUP_CONCAT(pt.tag ORDER BY pt.id SEPARATOR ' '), '') AS tags
     FROM posts p
     LEFT JOIN post_tags pt ON pt.post_id = p.id
     WHERE p.artist_id = ? AND p.status = 'active'
     GROUP BY p.id
     ORDER BY p.created_at DESC",
    "s",
    [$userId]
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Artis - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container is-artist">
        <section class="artist-profile">
            <div class="profile-header">
                <img src="Assets/draw2.png" alt="Artist Avatar" class="artist-avatar">
                <div class="artist-info">
                    <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                    <p class="artist-bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></p>
                    <div class="artist-stats">
                        <span><strong><?php echo number_format($postCount, 0, ',', '.'); ?></strong> Postingan</span>
                        <span><strong><?php echo number_format($followersCount, 0, ',', '.'); ?></strong> Followers</span>
                        <span><strong><?php echo number_format($followingCount, 0, ',', '.'); ?></strong> Following</span>
                    </div>
                    <div class="commission-status">
                        <label>Set commission status</label>
                        <select>
                            <option>Open</option>
                            <option>Closed</option>
                            <option>Waitlist</option>
                        </select>
                    </div>
                    
                    
                </div>
            </div>

            <div class="artist-posts">
                <div class="post-grid">
                    <?php if (empty($postRows)): ?>
                        <div class="post-card empty-state">
                            <div class="post-info">
                                <p>Tidak ada postingan.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($postRows as $post): ?>
                            <div class="post-card">
                                <img src="<?php echo htmlspecialchars($post['image_url'] ?: 'Assets/draw2.png'); ?>" alt="<?php echo htmlspecialchars($post['title'] ?: 'Postingan'); ?>">
                                <div class="post-info">
                                    <p class="hashtags"><?php echo htmlspecialchars($post['tags'] ?: '#digitalart'); ?></p>
                                    <p class="likes"><i class="fas fa-heart"></i> <?php echo number_format((int) $post['like_count'], 0, ',', '.'); ?> likes</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <script src="js/utils.js"></script>
    <script src="report-modal.js"></script>
    <script src="js/art-modal.js"></script>
</body>
</html>