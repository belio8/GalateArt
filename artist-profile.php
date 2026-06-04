<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login('artist');
require_once __DIR__ . '/config/Db.php';

$userSession = current_user();
$userId = $userSession['id'];

// Fetch full user data
$userRow = db_row($conn, "SELECT * FROM users WHERE id = ?", "s", [$userId]);
$user = $userRow ?: $userSession;

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

    <main class="profile-page-container is-artist" id="profileWrapper">
        <div class="profile-main">
            <div class="account-badge" id="accountBadgeText">Artist Account</div>
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

            <div class="profile-stats">
                <span><strong><?php echo number_format($postCount, 0, ',', '.'); ?></strong> Postingan</span>
                <span><strong><?php echo number_format($followingCount, 0, ',', '.'); ?></strong> Following</span>
                <span><strong><?php echo number_format($followersCount, 0, ',', '.'); ?></strong> Followers</span>
            </div>

            <div class="profile-tabs">
                <button class="tab-btn active" onclick="switchTab(this, 'content-bio')">Bio</button>
                <button class="tab-btn" onclick="switchTab(this, 'content-posts')">Posts</button>
                <button class="tab-btn" onclick="switchTab(this, 'content-saved')">Saved</button>
                <button class="tab-btn" onclick="switchTab(this, 'content-liked')">Liked</button>
            </div>

            <div class="profile-content">
                <div class="tab-content active" id="content-bio">
                    <p><?php echo nl2br(htmlspecialchars(!empty($user['bio']) ? $user['bio'] : 'Belum ada bio.')); ?></p>
                </div>

                <div class="tab-content" id="content-posts">
                    <div class="profile-art-grid">
                        <?php if (empty($postRows)): ?>
                            <div class="post-card empty-state" style="grid-column: 1 / -1; text-align: center;">
                                <div class="post-info">
                                    <p>Tidak ada postingan.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($postRows as $post): ?>
                                <div class="art-card">
                                    <img src="<?php echo htmlspecialchars($post['image_url'] ?: 'Assets/draw2.png'); ?>" alt="<?php echo htmlspecialchars($post['title'] ?: 'Postingan'); ?>">
                                    <div class="art-info">
                                        <p class="hashtags"><?php echo htmlspecialchars($post['tags'] ?: '#digitalart'); ?></p>
                                        <p class="likes"><i class="fas fa-heart"></i> <?php echo number_format((int) $post['like_count'], 0, ',', '.'); ?> likes</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tab-content" id="content-saved">
                    <div class="profile-art-grid">
                        <div class="art-card">
                            <img src="https://via.placeholder.com/300x400/b3b3b3/ffffff?text=Kobo" alt="Art">
                            <div class="art-info">
                                <p class="hashtags">#kobokanaeru #hololive #holoid</p>
                                <p class="artist-name">@jasper_xandros</p>
                            </div>
                        </div>
                        <div class="art-card">
                            <img src="https://via.placeholder.com/300x400/ff7e33/ffffff?text=Exusiai" alt="Art">
                            <div class="art-info">
                                <p class="hashtags">#exusiai #arknight #exiaalter</p>
                                <p class="artist-name">@artist_3</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="content-liked"> 
                    <div class="profile-art-grid">
                        <div class="art-card">
                            <img src="https://via.placeholder.com/300x400/8e54e9/ffffff?text=Liked+Art" alt="Art">
                            <div class="art-info">
                                <p class="hashtags">#illustration #digitalart</p>
                                <p class="artist-name">@ichigowarano</p>
                            </div>
                        </div>
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
    <script src="js/art-modal.js"></script>
</body>
</html>