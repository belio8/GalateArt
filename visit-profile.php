<?php
require_once __DIR__ . '/components/bootstrap.php';
require_once __DIR__ . '/config/Db.php';

// Must be logged in to visit profiles
if (!is_logged_in()) {
    header('Location: landing.php?auth=login');
    exit;
}

$currentUser = current_user();
$currentUserId = $currentUser['id'];

// Get target username from query string
$targetUsername = trim($_GET['user'] ?? '');

if (!$targetUsername) {
    header('Location: ' . active_user_home());
    exit;
}

// If visiting own profile, redirect to own profile page
if ($targetUsername === $currentUser['username']) {
    header('Location: ' . active_user_profile());
    exit;
}

// Fetch target user data
$user = db_row($conn, "SELECT * FROM users WHERE username = ? AND is_banned = 0", "s", [$targetUsername]);

if (!$user) {
    // User not found — redirect to home
    header('Location: ' . active_user_home());
    exit;
}

$targetUserId = $user['id'];
$isArtist = ($user['role'] === 'artist');

// Fetch follow counts
$followingRow = db_row($conn, "SELECT COUNT(*) AS cnt FROM follows WHERE follower_id = ?", "s", [$targetUserId]);
$followingCount = (int) ($followingRow['cnt'] ?? 0);

$followersRow = db_row($conn, "SELECT COUNT(*) AS cnt FROM follows WHERE following_id = ?", "s", [$targetUserId]);
$followersCount = (int) ($followersRow['cnt'] ?? 0);

// Check if current user is following this user
$isFollowing = false;
$followRow = db_row($conn, "SELECT id FROM follows WHERE follower_id = ? AND following_id = ?", "ss", [$currentUserId, $targetUserId]);
if ($followRow) $isFollowing = true;

// Artist-specific data
$postCount = 0;
$commissionStatus = 'closed';
$postRows = [];

if ($isArtist) {
    $postRow = db_row($conn, "SELECT COUNT(*) AS cnt FROM posts WHERE artist_id = ? AND status = 'active'", "s", [$targetUserId]);
    $postCount = (int) ($postRow['cnt'] ?? 0);

    // Commission status from artist_profiles
    $artistProfile = db_row($conn, "SELECT commission_status FROM artist_profiles WHERE user_id = ?", "s", [$targetUserId]);
    $commissionStatus = $artistProfile['commission_status'] ?? 'closed';

    // Fetch posts
    $postRows = db_query(
        $conn,
        "SELECT p.id, p.image_url, p.like_count, p.title, p.description, p.price, p.is_free, p.is_nsfw,
                COALESCE(GROUP_CONCAT(pt.tag ORDER BY pt.id SEPARATOR ' '), '') AS tags
         FROM posts p
         LEFT JOIN post_tags pt ON pt.post_id = p.id
         WHERE p.artist_id = ? AND p.status = 'active'
         GROUP BY p.id
         ORDER BY p.created_at DESC",
        "s",
        [$targetUserId]
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($user['username']) ?> - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="profile-page-container <?= $isArtist ? 'is-artist' : 'is-regular' ?>" id="profileWrapper">
        <div class="profile-main">
            <div class="account-badge" id="accountBadgeText"><?= $isArtist ? 'Artist Account' : 'Regular Account' ?></div>
            <?php $bannerSrc = !empty($user['banner_url']) ? htmlspecialchars($user['banner_url']) : 'Assets/galateart_banner.png'; ?>
            <div class="cover-photo" style="background-image: url('<?= $bannerSrc ?>');">
            </div>
            
            <div class="profile-info-section">
                <div class="profile-header-row">
                    <div class="avatar-container">
                        <img src="<?= e(!empty($user['avatar_url']) ? $user['avatar_url'] : 'Assets/galateart_icon.png') ?>" referrerpolicy="no-referrer">
                    </div>
                    <div class="visit-profile-actions">
                        <?php if ($isArtist && in_array($commissionStatus, ['open', 'waitlist'])): ?>
                            <a href="commission.php?artist=<?= urlencode($user['username']) ?>" class="btn-request-commission" style="background: var(--accent); color: white; padding: 8px 16px; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.2s;">
                                <i class="fas fa-paint-brush"></i> Request Commission
                            </a>
                        <?php endif; ?>
                        <button class="btn-follow-profile <?= $isFollowing ? 'following' : '' ?>"
                                id="btnFollowProfile"
                                data-artist-id="<?= e($targetUserId) ?>"
                                data-following="<?= $isFollowing ? 'true' : 'false' ?>">
                            <i class="fas <?= $isFollowing ? 'fa-user-check' : 'fa-user-plus' ?>"></i>
                            <span><?= $isFollowing ? 'Following' : 'Follow' ?></span>
                        </button>
                        <button class="btn-report-profile" id="btnReportProfile"
                                data-user-id="<?= e($targetUserId) ?>"
                                data-username="<?= e($user['username']) ?>">
                            <i class="fas fa-flag"></i>
                        </button>
                    </div>
                </div>
                
                <div class="profile-text">
                    <h1 id="userName"><?= e($user['username']) ?></h1>
                    <p id="userHandle">@<?= e($user['username']) ?></p>
                    
                    <?php if ($isArtist): ?>
                    <div class="commission-badge-wrapper">
                        <?php
                        $statusLabel = ucfirst($commissionStatus);
                        $statusClass = 'status-' . $commissionStatus;
                        $statusIcon = match ($commissionStatus) {
                            'open' => 'fa-door-open',
                            'closed' => 'fa-door-closed',
                            'waitlist' => 'fa-clock',
                            default => 'fa-circle'
                        };
                        ?>
                        <div class="commission-badge <?= $statusClass ?>">
                            <i class="fas <?= $statusIcon ?>"></i>
                            <span>Commission <?= $statusLabel ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-stats">
                <?php if ($isArtist): ?>
                <span><strong><?= number_format($postCount, 0, ',', '.') ?></strong> Postingan</span>
                <?php endif; ?>
                <span><strong><?= number_format($followingCount, 0, ',', '.') ?></strong> Following</span>
                <span id="followersCountDisplay"><strong><?= number_format($followersCount, 0, ',', '.') ?></strong> Followers</span>
            </div>

            <div class="profile-tabs">
                <button class="tab-btn active" onclick="switchTab(this, 'content-bio')">Bio</button>
                <?php if ($isArtist): ?>
                <button class="tab-btn" onclick="switchTab(this, 'content-posts')">Posts</button>
                <?php endif; ?>
            </div>

            <div class="profile-content">
                <div class="tab-content active" id="content-bio">
                    <p><?= nl2br(e(!empty($user['bio']) ? $user['bio'] : 'Belum ada bio.')) ?></p>
                </div>

                <?php if ($isArtist): ?>
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
                                <div class="art-card <?= !empty($post['is_nsfw']) ? 'is-nsfw' : '' ?>" style="cursor: pointer;"
                                     data-post-id="<?= e($post['id']) ?>"
                                     data-img="<?= e($post['image_url'] ?: 'Assets/draw2.png') ?>"
                                     data-artist="@<?= e($user['username']) ?>"
                                     data-avatar-url="<?= e($user['avatar_url'] ?: 'Assets/galateart_icon.png') ?>"
                                     data-tags="<?= e($hashtags) ?>"
                                     data-likes="<?= (int)$post['like_count'] ?>"
                                     data-title="<?= e($post['title'] ?: '') ?>">
                                    
                                    <?php if (!empty($post['is_nsfw'])): ?>
                                        <span class="nsfw-badge">18+</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($post['price']) && $post['price'] > 0): ?>
                                        <span class="price-badge">Rp <?= number_format((float)$post['price'], 0, ',', '.') ?></span>
                                    <?php endif; ?>

                                    <img src="<?= e($post['image_url'] ?: 'Assets/draw2.png') ?>" alt="<?= e($post['title'] ?: 'Postingan') ?>">
                                    <div class="card-avatar-wrap" onclick="event.stopPropagation(); window.location.href='visit-profile.php?user=<?= e($user['username']) ?>';">
                                        <img class="card-avatar" src="<?= e($user['avatar_url'] ?: 'Assets/galateart_icon.png') ?>" alt="" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='Assets/galateart_icon.png';">
                                        <span class="card-avatar-tooltip">@<?= e($user['username']) ?></span>
                                    </div>
                                    <div class="art-info">
                                        <p class="art-title"><?= e($post['title'] ?: 'Postingan') ?></p>
                                        <p class="hashtags"><?= e($hashtags) ?></p>
                                        <p class="likes"><i class="fas fa-heart"></i> <?= number_format((int) $post['like_count'], 0, ',', '.') ?> likes</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php include __DIR__ . '/components/art-modal.php'; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="js/utils.js?v=<?= time() ?>"></script>
    <script src="js/navbar.js?v=<?= time() ?>"></script>
    <script src="js/auth.js?v=<?= time() ?>"></script>
    <script src="js/profile.js?v=<?= time() ?>"></script>
    <script src="js/art-modal.js?v=<?= time() ?>"></script>
    <script src="js/report-modal.js?v=<?= time() ?>"></script>

    <script>
    (function() {
        // ── Follow button on profile ──────────────────────────────
        const followBtn = document.getElementById('btnFollowProfile');
        if (followBtn) {
            followBtn.addEventListener('click', async function() {
                const artistId = this.dataset.artistId;
                if (!artistId) return;

                this.disabled = true;
                try {
                    const res = await fetch('api/follow.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ artist_id: artistId })
                    });
                    const data = await res.json();
                    if (data.status === 'ok') {
                        this.dataset.following = String(data.following);
                        const icon = this.querySelector('i');
                        const span = this.querySelector('span');

                        if (data.following) {
                            this.classList.add('following');
                            icon.className = 'fas fa-user-check';
                            span.textContent = 'Following';
                        } else {
                            this.classList.remove('following');
                            icon.className = 'fas fa-user-plus';
                            span.textContent = 'Follow';
                        }

                        // Update followers count display
                        const followersEl = document.getElementById('followersCountDisplay');
                        if (followersEl) {
                            const strong = followersEl.querySelector('strong');
                            if (strong) {
                                let currentCount = parseInt(strong.textContent.replace(/\./g, '')) || 0;
                                currentCount += data.following ? 1 : -1;
                                if (currentCount < 0) currentCount = 0;
                                strong.textContent = currentCount.toLocaleString('id');
                            }
                        }
                    } else if (data.message) {
                        alert(data.message);
                    }
                } catch (err) {
                    console.error('Follow error:', err);
                    alert('Terjadi kesalahan jaringan.');
                }
                this.disabled = false;
            });
        }

        // ── Report button on profile ──────────────────────────────
        const reportBtn = document.getElementById('btnReportProfile');
        if (reportBtn) {
            reportBtn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const username = this.dataset.username;
                if (window.openReportModal) {
                    window.openReportModal({
                        type: 'account',
                        id: userId,
                        title: '@' + username
                    });
                }
            });
        }
    })();
    </script>
</body>
</html>
