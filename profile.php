<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login();
require_once __DIR__ . '/config/Db.php';

$userSession = current_user();
$userId = $userSession['id'];

// Fetch full user data
$userRow = db_row($conn, "SELECT * FROM users WHERE id = ?", "s", [$userId]);
$user = $userRow ?: $userSession;

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

// Fetch saved posts
$savedPostsSql = "
    SELECT p.id as post_id, p.image_url, p.title, p.like_count, p.price, p.is_free, p.is_nsfw, u.username as artist_name, u.avatar_url,
           (SELECT GROUP_CONCAT(tag SEPARATOR ',') FROM post_tags WHERE post_id = p.id) as tags
    FROM saves s
    JOIN posts p ON s.post_id = p.id
    JOIN users u ON p.artist_id = u.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
";
$savedPosts = db_query($conn, $savedPostsSql, "s", [$userId]);

// Fetch liked posts
$likedPostsSql = "
    SELECT p.id as post_id, p.image_url, p.title, p.like_count, p.price, p.is_free, p.is_nsfw, u.username as artist_name, u.avatar_url,
           (SELECT GROUP_CONCAT(tag SEPARATOR ',') FROM post_tags WHERE post_id = p.id) as tags
    FROM likes l
    JOIN posts p ON l.post_id = p.id
    JOIN users u ON p.artist_id = u.id
    WHERE l.user_id = ?
    ORDER BY l.created_at DESC
";
$likedPosts = db_query($conn, $likedPostsSql, "s", [$userId]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="profile-page-container is-regular" id="profileWrapper">
        
        <div class="profile-main">
            <div class="account-badge" id="accountBadgeText">Regular Account</div>
            <?php $bannerSrc = !empty($user['banner_url']) ? htmlspecialchars($user['banner_url']) : 'Assets/galateart_banner.png'; ?>
            <div class="cover-photo" style="background-image: url('<?= $bannerSrc ?>');">
            </div>
            
            <div class="profile-info-section">
                <div class="profile-header-row">
                    <div class="avatar-container">
                        <img src="<?php echo htmlspecialchars(!empty($user['avatar_url']) ? $user['avatar_url'] : 'Assets/galateart_icon.png'); ?>" referrerpolicy="no-referrer">
                    </div>
                    <div class="profile-actions">
                        <a href="edit-profile.php" class="btn-edit-profile">Edit Profile</a>
                    </div>
                </div>
                
                <div class="profile-text">
                    <h1 id="userName"><?php echo htmlspecialchars($user['username']); ?></h1>
                    <p id="userHandle">@<?php echo htmlspecialchars($user['username']); ?></p>
                </div>
            </div>

            <div class="profile-stats">
                <span><strong><?php echo number_format($followingCount, 0, ',', '.'); ?></strong> Following</span>
                <span><strong><?php echo number_format($followersCount, 0, ',', '.'); ?></strong> Followers</span>
            </div>

            <div class="profile-tabs">
                <button class="tab-btn active" onclick="switchTab(this, 'content-bio')">Bio</button>
                <button class="tab-btn" onclick="switchTab(this, 'content-saved')">Saved</button>
                <button class="tab-btn" onclick="switchTab(this, 'content-liked')">Liked</button>
            </div>

            <div class="profile-content">
                
                <div class="tab-content active" id="content-bio">
                    <p><?php echo nl2br(htmlspecialchars(!empty($user['bio']) ? $user['bio'] : 'Belum ada bio.')); ?></p>
                </div>


                <div class="tab-content" id="content-saved">
                    <div class="profile-art-grid">
                        <?php if (empty($savedPosts)): ?>
                            <p style="color:var(--text-gray);text-align:center;grid-column:1/-1;">Belum ada karya yang disimpan.</p>
                        <?php else: ?>
                            <?php foreach ($savedPosts as $post): ?>
                                <?php
                                $img = htmlspecialchars($post['image_url']);
                                $artist = '@' . htmlspecialchars($post['artist_name']);
                                $likes = (int)($post['like_count'] ?? 0);
                                $tagsStr = '';
                                if (!empty($post['tags'])) {
                                    $tagsArr = explode(',', $post['tags']);
                                    foreach ($tagsArr as $t) {
                                        $tagsStr .= '#' . trim($t) . ' ';
                                    }
                                }
                                $tagsFormatted = htmlspecialchars(trim($tagsStr));
                                ?>
                                <div class="art-card <?= !empty($post['is_nsfw']) ? 'is-nsfw' : '' ?>" data-post-id="<?= htmlspecialchars($post['post_id']) ?>" data-img="<?= $img ?>" data-artist="<?= $artist ?>" data-tags="<?= $tagsFormatted ?>" data-likes="<?= $likes ?>" data-avatar-url="<?= htmlspecialchars($post['avatar_url'] ?: 'Assets/galateart_icon.png') ?>" data-title="<?= htmlspecialchars($post['title'] ?: '') ?>">
                                    <?php if (!empty($post['is_nsfw'])): ?>
                                        <span class="nsfw-badge">18+</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($post['price']) && $post['price'] > 0): ?>
                                        <span class="price-badge">Rp <?= number_format((float)$post['price'], 0, ',', '.') ?></span>
                                    <?php endif; ?>

                                    <img src="<?= $img ?>" alt="Art">
                                    <div class="card-avatar-wrap" onclick="event.stopPropagation(); window.location.href='visit-profile.php?user=<?= htmlspecialchars($post['artist_name']) ?>';">
                                        <img class="card-avatar" src="<?= htmlspecialchars($post['avatar_url'] ?: 'Assets/galateart_icon.png') ?>" alt="" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='Assets/galateart_icon.png';">
                                        <span class="card-avatar-tooltip"><?= $artist ?></span>
                                    </div>
                                    <div class="art-info">
                                        <p class="art-title"><?= htmlspecialchars($post['title'] ?: 'Karya Seni') ?></p>
                                        <p class="hashtags"><?= $tagsFormatted ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tab-content" id="content-liked"> 
                    <div class="profile-art-grid">
                        <?php if (empty($likedPosts)): ?>
                            <p style="color:var(--text-gray);text-align:center;grid-column:1/-1;">Belum ada karya yang disukai.</p>
                        <?php else: ?>
                            <?php foreach ($likedPosts as $post): ?>
                                <?php
                                $img = htmlspecialchars($post['image_url']);
                                $artist = '@' . htmlspecialchars($post['artist_name']);
                                $likes = (int)($post['like_count'] ?? 0);
                                $tagsStr = '';
                                if (!empty($post['tags'])) {
                                    $tagsArr = explode(',', $post['tags']);
                                    foreach ($tagsArr as $t) {
                                        $tagsStr .= '#' . trim($t) . ' ';
                                    }
                                }
                                $tagsFormatted = htmlspecialchars(trim($tagsStr));
                                ?>
                                <div class="art-card <?= !empty($post['is_nsfw']) ? 'is-nsfw' : '' ?>" data-post-id="<?= htmlspecialchars($post['post_id']) ?>" data-img="<?= $img ?>" data-artist="<?= $artist ?>" data-tags="<?= $tagsFormatted ?>" data-likes="<?= $likes ?>" data-avatar-url="<?= htmlspecialchars($post['avatar_url'] ?: 'Assets/galateart_icon.png') ?>" data-title="<?= htmlspecialchars($post['title'] ?: '') ?>">
                                    <?php if (!empty($post['is_nsfw'])): ?>
                                        <span class="nsfw-badge">18+</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($post['price']) && $post['price'] > 0): ?>
                                        <span class="price-badge">Rp <?= number_format((float)$post['price'], 0, ',', '.') ?></span>
                                    <?php endif; ?>

                                    <img src="<?= $img ?>" alt="Art">
                                    <div class="card-avatar-wrap" onclick="event.stopPropagation(); window.location.href='visit-profile.php?user=<?= htmlspecialchars($post['artist_name']) ?>';">
                                        <img class="card-avatar" src="<?= htmlspecialchars($post['avatar_url'] ?: 'Assets/galateart_icon.png') ?>" alt="" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='Assets/galateart_icon.png';">
                                        <span class="card-avatar-tooltip"><?= $artist ?></span>
                                    </div>
                                    <div class="art-info">
                                        <p class="art-title"><?= htmlspecialchars($post['title'] ?: 'Karya Seni') ?></p>
                                        <p class="hashtags"><?= $tagsFormatted ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                

            </div>
        </div>
        
    </main>
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/profile.js"></script>
    <script src="script.js"></script>
    <script src="report-modal.js"></script>
</body>
</html>