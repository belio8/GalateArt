<?php
require_once __DIR__ . '/components/bootstrap.php';
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

    <link rel="stylesheet" href="style.css">
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
                <div class="ga-tag-trend-item" data-tag="#vtuber">
                    <span class="ga-tag-trend-name">#vtuber</span>
                    <span class="ga-tag-trend-count">1.2k karya</span>
                </div>
                <div class="ga-tag-trend-item" data-tag="#chibi">
                    <span class="ga-tag-trend-name">#chibi</span>
                    <span class="ga-tag-trend-count">874 karya</span>
                </div>
                <div class="ga-tag-trend-item" data-tag="#fanart">
                    <span class="ga-tag-trend-name">#fanart</span>
                    <span class="ga-tag-trend-count">641 karya</span>
                </div>
                <div class="ga-tag-trend-item" data-tag="#illustration">
                    <span class="ga-tag-trend-name">#illustration</span>
                    <span class="ga-tag-trend-count">589 karya</span>
                </div>
                <div class="ga-tag-trend-item" data-tag="#character">
                    <span class="ga-tag-trend-name">#character</span>
                    <span class="ga-tag-trend-count">502 karya</span>
                </div>
                <div class="ga-tag-trend-item" data-tag="#original">
                    <span class="ga-tag-trend-name">#original</span>
                    <span class="ga-tag-trend-count">448 karya</span>
                </div>
                <div class="ga-tag-trend-item" data-tag="#emoji">
                    <span class="ga-tag-trend-name">#emoji</span>
                    <span class="ga-tag-trend-count">317 karya</span>
                </div>
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

    <!-- â•â• ART MODAL (popup detail karya - sama dengan landing.php) â•â• -->
    <div class="modal-bg" id="modalBg">
        <div class="modal-box" id="modalBox">
            <button class="modal-close" id="closeModalPost"><i class="fas fa-times"></i></button>

            <div class="modal-img" id="modalImgWrap">
                <img src="" alt="Art Image" id="modalImageDisplay">
            </div>

            <div class="modal-panel">
                <div class="post-header">
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=artis" alt="Avatar" class="post-av" id="phAv">
                    <div class="post-author">
                        <strong id="phName">@artist_name</strong>
                        <span id="phSpec">Karya Seni</span>
                    </div>
                    <button class="order-btn" id="orderBtn" onclick="location.href='commission.php'">
                        <i class="fas fa-shopping-cart"></i> Order
                    </button>
                    <button class="follow-btn" id="followBtn">Follow</button>
                </div>

                <div class="comment-feed" id="commentFeed">
                    <div class="caption-block">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=artis" alt="" class="c-av" id="capAv">
                        <div class="c-body">
                            <strong id="captionName">@artist_name</strong>
                            <span>Menampilkan karya seni terbaru saya!</span>
                            <div class="tags" id="captionTags">#digitalart</div>
                            <span class="c-time">Beberapa jam yang lalu</span>
                        </div>
                    </div>
                    <div class="feed-divider"></div>
                    <div class="comment-count">0 Komentar</div>
                </div>

                <div class="like-action-bar" id="likeActionBar">
                    <div class="like-action-left">
                        <button class="like-post-btn" id="likePostBtn" onclick="toggleLikePost()">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <button class="save-post-btn" id="savePostBtn" onclick="toggleSavePost()">
                        <i class="far fa-bookmark"></i>
                    </button>
                </div>
                <div class="like-count-bar" id="likeCountBar">
                    <span id="likeCountText"><strong>0</strong> <span>orang menyukai ini</span></span>
                </div>

                <div class="input-area">
                    <img class="input-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=me" alt="">
                    <div class="input-wrap">
                        <input type="text" class="comment-input" id="commentInput"
                               placeholder="Tambahkan komentar..." autocomplete="off">
                    </div>
                    <button class="post-btn" id="postBtn">Kirim</button>
                </div>
            </div>
        </div>
    </div>

    <!-- â•â• SCRIPTS â•â• -->
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/art-modal.js"></script>
    <script src="js/report-modal.js"></script>
    <script src="js/tagline.js"></script>
</body>
</html>