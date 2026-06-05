<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login('artist');
require_once __DIR__ . '/config/Db.php';

$userSession = current_user();
$userId = $userSession['id'];

// Fetch full user data
$userRow = db_row($conn, "SELECT * FROM users WHERE id = ?", "s", [$userId]);
$user = $userRow ?: $userSession;

// Fetch artist profile specifically for commission status
$artistProfileRow = db_row($conn, "SELECT commission_status FROM artist_profiles WHERE user_id = ?", "s", [$userId]);
$commissionStatus = $artistProfileRow['commission_status'] ?? 'closed';

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

$savedPostsSql = "
    SELECT p.id as post_id, p.image_url, p.title, p.like_count, u.username as artist_name, u.avatar_url,
           (SELECT GROUP_CONCAT(tag SEPARATOR ',') FROM post_tags WHERE post_id = p.id) as tags
    FROM saves s
    JOIN posts p ON s.post_id = p.id
    JOIN users u ON p.artist_id = u.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
";
$savedPosts = db_query($conn, $savedPostsSql, "s", [$userId]);

$likedPostsSql = "
    SELECT p.id as post_id, p.image_url, p.title, p.like_count, u.username as artist_name, u.avatar_url,
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
                        <select id="commissionStatusSelect">
                            <option value="open" <?= $commissionStatus === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="closed" <?= $commissionStatus === 'closed' ? 'selected' : '' ?>>Closed</option>
                            <option value="waitlist" <?= $commissionStatus === 'waitlist' ? 'selected' : '' ?>>Waitlist</option>
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
                                <?php
                                    $tags = trim((string) ($post['tags'] ?? ''));
                                    $hashtags = $tags ? '#' . str_replace(' ', ' #', $tags) : '#digitalart';
                                ?>
                                <div class="art-card" style="cursor: pointer;"
                                     data-post-id="<?= e($post['id']) ?>"
                                     data-img="<?= e($post['image_url'] ?: 'Assets/draw2.png') ?>"
                                     data-artist="@<?= e($user['username']) ?>"
                                     data-avatar-url="<?= e($user['avatar_url'] ?: 'Assets/galateart_icon.png') ?>"
                                     data-tags="<?= e($hashtags) ?>"
                                     data-likes="<?= (int)$post['like_count'] ?>">
                                    <img src="<?php echo htmlspecialchars($post['image_url'] ?: 'Assets/draw2.png'); ?>" alt="<?php echo htmlspecialchars($post['title'] ?: 'Postingan'); ?>">
                                    <div class="art-info">
                                        <p class="hashtags"><?php echo htmlspecialchars($hashtags); ?></p>
                                        <p class="likes"><i class="fas fa-heart"></i> <?php echo number_format((int) $post['like_count'], 0, ',', '.'); ?> likes</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php include __DIR__ . '/components/art-modal.php'; ?>
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
                                <div class="art-card" data-post-id="<?= htmlspecialchars($post['post_id']) ?>" data-img="<?= $img ?>" data-artist="<?= $artist ?>" data-tags="<?= $tagsFormatted ?>" data-likes="<?= $likes ?>" data-avatar-url="<?= htmlspecialchars($post['avatar_url'] ?: 'Assets/galateart_icon.png') ?>">
                                    <img src="<?= $img ?>" alt="Art">
                                    <div class="art-info">
                                        <p class="hashtags"><?= $tagsFormatted ?></p>
                                        <p class="artist-name">
                                            <a href="visit-profile.php?user=<?= htmlspecialchars($post['artist_name']) ?>" style="color: inherit; text-decoration: none;" onclick="event.stopPropagation();">
                                                <?= $artist ?>
                                            </a>
                                        </p>
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
                                <div class="art-card" data-post-id="<?= htmlspecialchars($post['post_id']) ?>" data-img="<?= $img ?>" data-artist="<?= $artist ?>" data-tags="<?= $tagsFormatted ?>" data-likes="<?= $likes ?>" data-avatar-url="<?= htmlspecialchars($post['avatar_url'] ?: 'Assets/galateart_icon.png') ?>">
                                    <img src="<?= $img ?>" alt="Art">
                                    <div class="art-info">
                                        <p class="hashtags"><?= $tagsFormatted ?></p>
                                        <p class="artist-name">
                                            <a href="visit-profile.php?user=<?= htmlspecialchars($post['artist_name']) ?>" style="color: inherit; text-decoration: none;" onclick="event.stopPropagation();">
                                                <?= $artist ?>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="js/utils.js?v=<?= time() ?>"></script>
    <script src="js/navbar.js?v=<?= time() ?>"></script>
    <script src="js/auth.js?v=<?= time() ?>"></script>
    <script src="js/profile.js?v=<?= time() ?>"></script>
    <script src="js/report-modal.js?v=<?= time() ?>"></script>
    <script src="js/art-modal.js?v=<?= time() ?>"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('commissionStatusSelect');
        if (select) {
            select.addEventListener('change', async function() {
                this.disabled = true;
                const status = this.value;
                try {
                    const res = await fetch('api/update-commission-status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: status })
                    });
                    const data = await res.json();
                    if (data.status !== 'ok') {
                        alert(data.message || 'Gagal mengupdate status komisi.');
                    }
                } catch (e) {
                    console.error('Update commission status error:', e);
                    alert('Terjadi kesalahan jaringan.');
                }
                this.disabled = false;
            });
        }
    });
    </script>
</body>
</html>