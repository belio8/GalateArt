<?php
require_once __DIR__ . '/components/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trending - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container">
        <section class="trending-posts">
            <h1>Trending</h1>
            <p>Karya seni terpopuler saat ini di GalateArt.</p>
            
            <div class="posts-grid">
                <div class="post-card">
                    <img src="Assets/draw2.png" alt="Post 1">
                    <div class="post-info">
                        <p class="hashtags">#original #illustration #character</p>
                        <p class="artist-name">@artis_lokal</p>
                        <p class="likes"><i class="fas fa-heart"></i> 250 likes</p>
                    </div>
                </div>
                <div class="post-card">
                    <img src="Assets/draw2.png" alt="Post 2">
                    <div class="post-info">
                        <p class="hashtags">#digitalart #vtuber #design</p>
                        <p class="artist-name">@seniman_digital</p>
                        <p class="likes"><i class="fas fa-heart"></i> 185 likes</p>
                    </div>
                </div>
                <div class="post-card">
                    <img src="Assets/draw2.png" alt="Post 3">
                    <div class="post-info">
                        <p class="hashtags">#fantasy #conceptart</p>
                        <p class="artist-name">@ichigowarano</p>
                        <p class="likes"><i class="fas fa-heart"></i> 320 likes</p>
                    </div>
                </div>
                <div class="post-card">
                    <img src="Assets/draw2.png" alt="Post 4">
                    <div class="post-info">
                        <p class="hashtags">#anime #manga #style</p>
                        <p class="artist-name">@keenbiscuit</p>
                        <p class="likes"><i class="fas fa-heart"></i> 198 likes</p>
                    </div>
                </div>
                <div class="post-card">
                    <img src="Assets/draw2.png" alt="Post 5">
                    <div class="post-info">
                        <p class="hashtags">#landscape #nature #digital</p>
                        <p class="artist-name">@jasper_xandros</p>
                        <p class="likes"><i class="fas fa-heart"></i> 275 likes</p>
                    </div>
                </div>
                <div class="post-card">
                    <img src="Assets/draw2.png" alt="Post 6">
                    <div class="post-info">
                        <p class="hashtags">#portrait #characterdesign</p>
                        <p class="artist-name">@artis_lokal</p>
                        <p class="likes"><i class="fas fa-heart"></i> 210 likes</p>
                    </div>
                </div>
                <!-- Tambahkan lebih banyak post-card sesuai kebutuhan -->
            </div>
        </section>

        <!-- âœ… Modal dipindahkan ke luar post-card agar event bubbling tidak menyebabkan glitch -->
        <div class="modal-bg" id="modalBg">
            <div class="modal-box" id="modalBox">
                <button class="modal-close" id="closeModalPost"><i class="fas fa-times"></i></button>
                
                <div class="modal-img">
                    <img src="" alt="Art Image" id="modalImageDisplay">
                </div>
                
                <div class="modal-panel">
                    <div class="post-header">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=artis" alt="Avatar" class="post-av" id="phAv">
                        <div class="post-author">
                            <strong id="phName">@artist_name</strong>
                            <span id="phSpec">Trending Artist</span>
                        </div>
                        <button class="order-btn" id="orderBtn" onclick="location.href='commission.php'"><i class="fas fa-shopping-cart"></i> Order</button>
                        <button class="follow-btn" id="followBtn">Follow</button>
                    </div>
                    
                    <div class="comment-feed" id="commentFeed">
                        <div class="caption-block">
                            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=artis" alt="" class="c-av" id="capAv">
                            <div class="c-body">
                                <strong id="captionName">@artist_name</strong>
                                <span>Karya trending terbaru!</span>
                                <div class="tags" id="captionTags">#trending</div>
                                <span class="c-time">Baru saja trending</span>
                            </div>
                        </div>
                        <div class="feed-divider"></div>
                        <div class="comment-count">1 Komentar</div>
                        <div class="comment-item">
                            <img class="c-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=user1" alt="">
                            <div class="c-body">
                                <div class="c-top">
                                    <strong>@user_galateart</strong> <span class="c-text">Karyanya keren banget! Pantas trending.</span>
                                </div>
                                <div class="c-bottom">
                                    <span class="c-time">5m</span>
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
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/art-modal.js"></script>
    <script src="report-modal.js"></script>
</body>
</html>