<?php
require_once __DIR__ . '/components/bootstrap.php';
require_once __DIR__ . '/config/Db.php';
require_login('regular');

$posts = db_query(
    "SELECT p.id, p.title, p.image_url, u.username, GROUP_CONCAT(pt.tag ORDER BY pt.tag SEPARATOR ' ') AS tags
     FROM posts p
     JOIN users u ON u.id = p.artist_id
     LEFT JOIN post_tags pt ON pt.post_id = p.id
     WHERE p.status = ?
     GROUP BY p.id, p.title, p.image_url, u.username
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

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container">
                <section class="hero-grid">
            <div class="hero-card main-card" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('https://via.placeholder.com/600x400');">
                <div class="hero-text">
                    <img src="Assets/draw2.png">
                    <h2>Dibuat oleh artis lokal</h2>
                    <p>Semua yang Anda butuhkan untuk vtuber, game, dan aset digital ada di sini!</p>
                </div>
            </div>
            <div class="hero-card ai-card" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('https://via.placeholder.com/600x400');">
                <div class="hero-text">
                    <img src="Assets/draw2.png">
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
                <div class="art-card">
                    <img src="<?= e($image) ?>" alt="<?= e($post['title']) ?>">
                    <div class="art-info">
                        <p class="hashtags"><?= e($hashtags) ?></p>
                        <p class="artist-name">@<?= e($post['username']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

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
                            <button class="order-btn" id="orderBtn" onclick="location.href='commission.php'"><i class="fas fa-shopping-cart"></i> Order</button>
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
                            <div class="comment-count">1 Komentar</div>
                            
                            <div class="comment-item">
                                <img class="c-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=user1" alt="">
                                <div class="c-body">
                                    <div class="c-top">
                                        <strong>@user_galateart</strong> <span class="c-text">Wah, warnanya sangat bagus! Lighting-nya juga keren kak.</span>
                                    </div>
                                    <div class="c-bottom">
                                        <span class="c-time">10m</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- âœ… Like & Save action bar -->
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
                                <input type="text" class="comment-input" id="commentInput" placeholder="Tambahkan komentar..." autocomplete="off">
                            </div>
                            <button class="post-btn" id="postBtn">Kirim</button>
                        </div>
                    </div>
                </div>
            </div>

    </main>
    <!-- Core utilities (wajib dimuat pertama) -->
    <script src="js/utils.js"></script>
    <!-- Komponen navbar: hamburger, notifikasi, cart, pesan, search -->
    <script src="js/navbar.js"></script>
    <!-- Autentikasi: login state, modal daftar/masuk/artis -->
    <script src="js/auth.js"></script>
    <!-- Modal popup karya seni + follow button -->
    <script src="js/art-modal.js"></script>
    <script src="js/report-modal.js"></script>
</body>
</html>