<?php
require_once __DIR__ . '/components/bootstrap.php';
require_once __DIR__ . '/config/Db.php';

// Ambil tag yang paling sering digunakan pada postingan aktif
$trending_tags = db_query(
    $conn,
    "SELECT pt.tag, COUNT(DISTINCT pt.post_id) AS tag_count
     FROM post_tags pt
     JOIN posts p ON p.id = pt.post_id
     WHERE p.status = 'active'
     GROUP BY pt.tag
     ORDER BY tag_count DESC
     LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Karya berdasarkan Tagline - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>

    <!-- â•â• NAVBAR (sama persis dengan landing.php) â•â• -->
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <!-- â•â• HERO / SEARCH HEADER â•â• -->
    <div class="ga-tag-hero">
        <div class="ga-tag-hero-inner">
            <h1>Cari Karya berdasarkan <span>Tagline</span></h1>
            <p>Temukan ilustrasi, karakter, latar, dan lebih banyak lagi lewat hashtag yang relevan.</p>

            <div class="ga-tag-search-bar">
                <input
                    type="text"
                    id="tagSearchInput"
                    placeholder="Ketik tag, nama artis, atau jenis karya..."
                    autocomplete="off">
                <button class="ga-tag-btn-search" id="tagBtnSearch">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>

            <!-- Chip popular tags -->
            <div class="ga-tag-popular-wrap">
                <span class="ga-tag-popular-label">Populer:</span>
                <span class="ga-tag-chip" data-tag="#vtuber">#vtuber</span>
                <span class="ga-tag-chip" data-tag="#chibi">#chibi</span>
                <span class="ga-tag-chip" data-tag="#fanart">#fanart</span>
                <span class="ga-tag-chip" data-tag="#original">#original</span>
                <span class="ga-tag-chip" data-tag="#illustration">#illustration</span>
                <span class="ga-tag-chip" data-tag="#character">#character</span>
                <span class="ga-tag-chip" data-tag="#landscape">#landscape</span>
                <span class="ga-tag-chip" data-tag="#portrait">#portrait</span>
            </div>
        </div>
    </div>

    <!-- â•â• MAIN BODY â•â• -->
    <div class="ga-tag-page">

        <!-- ── SIDEBAR ── -->
        <aside class="ga-tag-sidebar">

            <!-- Trending tags -->
            <div class="ga-tag-widget">
                <h3><i class="fas fa-fire" style="color:var(--accent);margin-right:6px;"></i>Tag Trending</h3>
                <?php if (empty($trending_tags)): ?>
                    <p style="color:#888; font-size:13px; text-align:center;">Belum ada tag trending.</p>
                <?php else: ?>
                    <?php foreach ($trending_tags as $tag): ?>
                        <div class="ga-tag-trend-item" data-tag="#<?= e(ltrim($tag['tag'], '#')) ?>">
                            <span class="ga-tag-trend-name">#<?= e(ltrim($tag['tag'], '#')) ?></span>
                            <span class="ga-tag-trend-count"><?= number_format((int)$tag['tag_count'], 0, ',', '.') ?> karya</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </aside>

        <!-- ── RESULTS COLUMN ── -->
        <main class="ga-tag-main">

            <!-- Active tag banner -->
            <div class="ga-tag-active-banner" id="tagActiveBanner">
                <i class="fas fa-tag"></i>
                <span id="tagActiveBannerTxt"></span>
                <button class="ga-tag-btn-clear-tag" id="tagBtnClearTag" title="Hapus filter tag">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Filter bar -->
            <div class="ga-tag-filter-bar">
                <div class="ga-tag-filter-left">
                    <span class="ga-tag-filter-label">Tipe:</span>
                    <button class="ga-tag-filter-btn ga-tag-filter-active" data-type="">Semua</button>
                    <button class="ga-tag-filter-btn" data-type="Illustration">Illustration</button>
                    <button class="ga-tag-filter-btn" data-type="Character">Character</button>
                    <button class="ga-tag-filter-btn" data-type="Chibi">Chibi</button>
                    <button class="ga-tag-filter-btn" data-type="Fanart">Fanart</button>
                    <button class="ga-tag-filter-btn" data-type="Portrait">Portrait</button>
                </div>
                <select class="ga-tag-sort-select" id="tagSortSelect">
                    <option value="newest">Terbaru</option>
                    <option value="popular">Terpopuler</option>
                    <option value="oldest">Terlama</option>
                </select>
            </div>

            <!-- Results heading -->
            <p class="ga-tag-results-heading" id="tagResultsHeading">Memuat karya...</p>

            <!-- Art grid -->
            <div class="ga-tag-grid" id="tagGrid"></div>

            <!-- Empty state -->
            <div class="ga-tag-empty" id="tagEmpty">
                <i class="fas fa-search"></i>
                <p>Tidak ada karya yang cocok dengan pencarian ini.</p>
            </div>

            <!-- Load more -->
            <div class="ga-tag-load-more-wrap">
                <button class="ga-tag-btn-load-more" id="tagBtnLoadMore" style="display:none;">
                    <i class="fas fa-chevron-down"></i> Tampilkan lebih banyak
                </button>
            </div>

        </main>
    </div>

        <?php include __DIR__ . '/components/art-modal.php'; ?>

    <!-- â• â•  SCRIPTS â• â•  -->
    <script src="js/utils.js"></script>
    <script src="js/navbar.js?v=<?= time() ?>"></script>
    <script src="js/auth.js?v=<?= time() ?>"></script>
    <script src="js/tagline.js?v=<?= time() ?>"></script>
    <script src="js/art-modal.js?v=<?= time() ?>"></script>
    <script src="js/report-modal.js?v=<?= time() ?>"></script>
</body>
</html>