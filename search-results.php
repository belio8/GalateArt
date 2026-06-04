<?php
require_once __DIR__ . '/components/bootstrap.php';
require_once __DIR__ . '/config/Db.php';

$q = trim($_GET['q'] ?? '');
$tab = $_GET['tab'] ?? 'artwork';
if (!in_array($tab, ['artwork', 'artist', 'tag'], true)) {
    $tab = 'artwork';
}

$searchTerm = '%' . str_replace(' ', '%', $q) . '%';
$tagSearch = ltrim($q, '#');

if ($q === '') {
    $artworks = db_query(
        $conn,
        "SELECT p.id, p.title, p.description, p.image_url, p.price, p.is_free, p.is_nsfw,
                u.username AS artist,
                COALESCE(GROUP_CONCAT(DISTINCT pt.tag ORDER BY pt.id SEPARATOR ' '), '') AS tags
         FROM posts p
         JOIN users u ON p.artist_id = u.id
         LEFT JOIN post_tags pt ON pt.post_id = p.id
         WHERE p.status = 'active'
         GROUP BY p.id
         ORDER BY p.created_at DESC
         LIMIT 24"
    );

    $artists = db_query(
        $conn,
        "SELECT u.id, u.username, COALESCE(NULLIF(u.avatar_url, ''), CONCAT('https://api.dicebear.com/7.x/avataaars/svg?seed=', u.username)) AS avatar_url,
                COALESCE(u.bio, '') AS bio,
                COUNT(DISTINCT p.id) AS post_count,
                COUNT(DISTINCT f.id) AS follower_count
         FROM users u
         LEFT JOIN posts p ON p.artist_id = u.id AND p.status = 'active'
         LEFT JOIN follows f ON f.following_id = u.id
         WHERE u.role = 'artist'
         GROUP BY u.id
         ORDER BY post_count DESC
         LIMIT 12"
    );

    $tags = db_query(
        $conn,
        "SELECT pt.tag, COUNT(DISTINCT pt.post_id) AS tag_count
         FROM post_tags pt
         JOIN posts p2 ON pt.post_id = p2.id AND p2.status = 'active'
         GROUP BY pt.tag
         ORDER BY tag_count DESC
         LIMIT 12"
    );
} else {
    $artworks = db_query(
        $conn,
        "SELECT p.id, p.title, p.description, p.image_url, p.price, p.is_free, p.is_nsfw,
                u.username AS artist,
                COALESCE(GROUP_CONCAT(DISTINCT pt.tag ORDER BY pt.id SEPARATOR ' '), '') AS tags
         FROM posts p
         JOIN users u ON p.artist_id = u.id
         LEFT JOIN post_tags pt ON pt.post_id = p.id
         WHERE p.status = 'active' AND (
             p.title LIKE ? OR
             p.description LIKE ? OR
             u.username LIKE ? OR
             pt.tag LIKE ?
         )
         GROUP BY p.id
         ORDER BY p.created_at DESC
         LIMIT 24",
        'ssss',
        [$searchTerm, $searchTerm, $searchTerm, '%' . $tagSearch . '%']
    );

    $artists = db_query(
        $conn,
        "SELECT u.id, u.username, COALESCE(NULLIF(u.avatar_url, ''), CONCAT('https://api.dicebear.com/7.x/avataaars/svg?seed=', u.username)) AS avatar_url,
                COALESCE(u.bio, '') AS bio,
                COUNT(DISTINCT p.id) AS post_count,
                COUNT(DISTINCT f.id) AS follower_count
         FROM users u
         LEFT JOIN posts p ON p.artist_id = u.id AND p.status = 'active'
         LEFT JOIN follows f ON f.following_id = u.id
         WHERE u.role = 'artist' AND (
             u.username LIKE ? OR
             u.bio LIKE ?
         )
         GROUP BY u.id
         ORDER BY post_count DESC
         LIMIT 12",
        'ss',
        [$searchTerm, $searchTerm]
    );

    $tags = db_query(
        $conn,
        "SELECT pt.tag, COUNT(DISTINCT pt.post_id) AS tag_count
         FROM post_tags pt
         JOIN posts p2 ON pt.post_id = p2.id AND p2.status = 'active'
         WHERE pt.tag LIKE ?
         GROUP BY pt.tag
         ORDER BY tag_count DESC
         LIMIT 12",
        's',
        ['%' . $tagSearch . '%']
    );
}

$artworkCount = count($artworks);
$artistCount = count($artists);
$tagCount = count($tags);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - GalateArt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <div class="ga-srch-page">
        <form action="search-results.php" method="get" class="ga-srch-bar-large">
            <input type="text" id="mainSearchInput" name="q" placeholder="Cari karya seni, artis, atau tag..." value="<?php echo htmlspecialchars($q, ENT_QUOTES); ?>">
            <button type="submit" class="ga-srch-btn-go">
                <i class="fas fa-search"></i> Cari
            </button>
        </form>

        <div class="ga-srch-header">
            <h1>Hasil untuk "<span id="queryLabel"><?php echo htmlspecialchars($q ?: 'semua', ENT_QUOTES); ?></span>"</h1>
            <p class="ga-srch-meta" id="resultsMeta">Menampilkan <?php echo $tab === 'artist' ? $artistCount : ($tab === 'tag' ? $tagCount : $artworkCount); ?> hasil � <?php echo $tab === 'artist' ? 'Artis' : ($tab === 'tag' ? 'Tag' : 'Artwork'); ?> � Diurutkan: Relevansi</p>
        </div>

        <div class="ga-srch-tabs">
            <button class="ga-srch-tab <?php echo $tab === 'artwork' ? 'ga-srch-active' : ''; ?>" onclick="switchTab(this,'tab-artwork')"><i class="fas fa-image"></i> Artwork</button>
            <button class="ga-srch-tab <?php echo $tab === 'artist' ? 'ga-srch-active' : ''; ?>" onclick="switchTab(this,'tab-artist')"><i class="fas fa-user"></i> Artis</button>
            <button class="ga-srch-tab <?php echo $tab === 'tag' ? 'ga-srch-active' : ''; ?>" onclick="switchTab(this,'tab-tag')"><i class="fas fa-hashtag"></i> Tag</button>
        </div>

        <div class="ga-srch-filters">
            <span class="ga-srch-filter-label">Filter:</span>
            <button type="button" class="ga-srch-filter-chip ga-srch-active" onclick="toggleChip(this)">Semua</button>
            <button type="button" class="ga-srch-filter-chip" onclick="toggleChip(this)">Commission Open</button>
            <button type="button" class="ga-srch-filter-chip" onclick="toggleChip(this)">Free</button>
            <button type="button" class="ga-srch-filter-chip" onclick="toggleChip(this)">Character</button>
            <button type="button" class="ga-srch-filter-chip" onclick="toggleChip(this)">Fullbody</button>
            <select class="ga-srch-filter-select" onchange="updateSort(this.value)">
                <option value="relevance">Relevansi</option>
                <option value="newest">Terbaru</option>
                <option value="popular">Terpopuler</option>
                <option value="price_low">Harga Terendah</option>
            </select>
        </div>

        <div class="ga-srch-body">
            <div class="ga-srch-results-col">
                <div id="tab-artwork" style="display: <?php echo $tab === 'artwork' ? 'block' : 'none'; ?>;">
                    <div class="ga-srch-art-grid" id="artworkGrid">
                        <?php if (empty($artworks)): ?>
                            <div class="ga-srch-empty-state">Tidak ada artwork yang cocok.</div>
                        <?php else: ?>
                            <?php foreach ($artworks as $art): ?>
                                <div class="art-card" style="cursor:pointer;">
                                    <img src="<?php echo htmlspecialchars($art['image_url'] ?: 'Assets/draw2.png', ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($art['title'] ?: 'Artwork', ENT_QUOTES); ?>">
                                    <div class="art-info">
                                        <p class="hashtags"><?php echo htmlspecialchars($art['tags'] ?: '#nohashtag', ENT_QUOTES); ?></p>
                                        <p class="artist-name">@<?php echo htmlspecialchars($art['artist'], ENT_QUOTES); ?></p>
                                        <p style="font-size:12px;color:#b3b3b3;margin:4px 0 0;"><i class="fas fa-heart" style="color:#ff4d6a;margin-right:4px;"></i><?php echo number_format((int)($art['like_count'] ?? 0), 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="tab-artist" style="display: <?php echo $tab === 'artist' ? 'block' : 'none'; ?>;">
                    <div class="ga-srch-artist-result-list">
                        <?php if (empty($artists)): ?>
                            <div class="ga-srch-empty-state">Tidak ada artis yang cocok.</div>
                        <?php else: ?>
                            <?php foreach ($artists as $artist): ?>
                                <div class="ga-srch-artist-result-card">
                                    <img class="ga-srch-ar-avatar" src="<?php echo htmlspecialchars($artist['avatar_url'], ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($artist['username'], ENT_QUOTES); ?>">
                                    <div class="ga-srch-ar-body">
                                        <p class="ga-srch-ar-name"><?php echo htmlspecialchars($artist['username'], ENT_QUOTES); ?></p>
                                        <p class="ga-srch-ar-handle">@<?php echo htmlspecialchars($artist['username'], ENT_QUOTES); ?></p>
                                        <p class="ga-srch-ar-bio"><?php echo htmlspecialchars($artist['bio'] ?: 'Artis lokal aktif.', ENT_QUOTES); ?></p>
                                        <p class="ga-srch-ar-stats"><strong><?php echo number_format((int)$artist['follower_count'], 0, ',', '.'); ?></strong> followers � <strong><?php echo number_format((int)$artist['post_count'], 0, ',', '.'); ?></strong> posts</p>
                                    </div>
                                    <button class="ga-srch-btn-follow">Follow</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="tab-tag" style="display: <?php echo $tab === 'tag' ? 'block' : 'none'; ?>;">
                    <div class="ga-srch-tag-results">
                        <?php if (empty($tags)): ?>
                            <div class="ga-srch-empty-state">Tidak ada tag yang cocok.</div>
                        <?php else: ?>
                            <?php foreach ($tags as $tag): ?>
                                <div class="ga-srch-tag-card">
                                    <div class="ga-srch-tag-name">#<?php echo htmlspecialchars($tag['tag'], ENT_QUOTES); ?></div>
                                    <div class="ga-srch-tag-count"><?php echo number_format((int)$tag['tag_count'], 0, ',', '.'); ?> postingan</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="ga-srch-sidebar">
                <div class="ga-srch-sidebar-widget">
                    <h3><i class="fas fa-fire" style="color:var(--accent);margin-right:6px;"></i>Tag Populer</h3>
                    <?php if (empty($tags)): ?>
                        <div class="ga-srch-trending-tag-item"><span>Tidak ada tag</span></div>
                    <?php else: ?>
                        <?php foreach ($tags as $tag): ?>
                            <div class="ga-srch-trending-tag-item"><span>#<?php echo htmlspecialchars($tag['tag'], ENT_QUOTES); ?></span><span><?php echo number_format((int)$tag['tag_count'], 0, ',', '.'); ?> posts</span></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="ga-srch-sidebar-widget">
                    <h3><i class="fas fa-user-plus" style="color:var(--purple);margin-right:6px;"></i>Artis yang Mungkin Kamu Suka</h3>
                    <?php if (empty($artists)): ?>
                        <div class="ga-srch-suggested-artist"><span>Tidak ada artis</span></div>
                    <?php else: ?>
                        <?php foreach (array_slice($artists, 0, 2) as $artist): ?>
                            <div class="ga-srch-suggested-artist">
                                <img class="ga-srch-sa-avatar" src="<?php echo htmlspecialchars($artist['avatar_url'], ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($artist['username'], ENT_QUOTES); ?>">
                                <div class="ga-srch-sa-info"><strong><?php echo htmlspecialchars($artist['username'], ENT_QUOTES); ?></strong><span>@<?php echo htmlspecialchars($artist['username'], ENT_QUOTES); ?></span></div>
                                <button class="ga-srch-btn-follow" style="font-size:11px;padding:5px 10px;">+Follow</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/art-modal.js"></script>
    <script>
        const counts = {
            'tab-artwork': <?php echo $artworkCount; ?>,
            'tab-artist': <?php echo $artistCount; ?>,
            'tab-tag': <?php echo $tagCount; ?>
        };

        function switchTab(btn, tabId) {
            document.querySelectorAll('.ga-srch-tab').forEach(b => b.classList.remove('ga-srch-active'));
            btn.classList.add('ga-srch-active');
            ['tab-artwork','tab-artist','tab-tag'].forEach(id => {
                document.getElementById(id).style.display = id === tabId ? '' : 'none';
            });
            document.getElementById('resultsMeta').textContent = `Menampilkan ${counts[tabId]} hasil � "${document.getElementById('queryLabel').textContent}"`;
        }

        function toggleChip(btn) {
            document.querySelectorAll('.ga-srch-filter-chip').forEach(c => c.classList.remove('ga-srch-active'));
            btn.classList.add('ga-srch-active');
        }

        function updateSort(val) {
            // placeholder: sort belum diimplementasikan server-side
        }

        function doSearch(q) {
            if (!q.trim()) return;
            const url = new URL(window.location.href);
            url.searchParams.set('q', q);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
