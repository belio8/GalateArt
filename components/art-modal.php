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
                <div class="comment-count">Memuat komentar...</div>
            </div>
            
            <!-- ✓ Like & Save action bar -->
            <div class="like-action-bar" id="likeActionBar" style="flex-shrink: 0; min-height: 40px; display: flex;">
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

            <!-- Area Input Komentar -->
            <div class="input-area" style="flex-shrink: 0; min-height: 60px; display: flex;">
                <?php
                    $me_av = 'https://api.dicebear.com/7.x/avataaars/svg?seed=guest';
                    if (isset($_SESSION['user_id'])) {
                        // Jika ada session data avatar, bisa digunakan
                        $me_av = $_SESSION['avatar_url'] ?? ('https://api.dicebear.com/7.x/avataaars/svg?seed=' . ($_SESSION['username'] ?? 'me'));
                    }
                ?>
                <img class="input-av" src="<?= e($me_av) ?>" alt="">
                <div class="input-wrap">
                    <input type="text" class="comment-input" id="commentInput" placeholder="Tambahkan komentar..." autocomplete="off">
                </div>
                <button class="post-btn" id="postBtn">Kirim</button>
            </div>
        </div>
    </div>
</div>
