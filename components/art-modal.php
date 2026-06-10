<div class="modal-bg" id="modalBg">
    <div class="modal-box" id="modalBox">
        <button class="modal-close" id="closeModalPost"><i class="fas fa-times"></i></button>
        
        <div class="modal-img" id="modalImgWrap">
            <img src="" alt="Art Image" id="modalImageDisplay">
        </div>
        
        <div class="modal-panel">
            <div class="post-header">
                <img src="Assets/galateart_icon.png" alt="Avatar" class="post-av" id="phAv" referrerpolicy="no-referrer">
                <div class="post-author">
                    <strong id="phName"></strong>
                    <span id="phSpec">Karya Seni</span>
                </div>
                <button class="order-btn" id="orderBtn"><i class="fas fa-shopping-cart"></i> Order</button>
                <button class="follow-btn" id="followBtn">Follow</button>
            </div>
            
            <div class="comment-feed" id="commentFeed">
                <div class="comment-count">Memuat komentar...</div>
            </div>
            
            <!-- ✓ Like & Save action bar -->
            <?php if (isset($_SESSION['user_id'])): ?>
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
            <?php endif; ?>
            <div class="like-count-bar" id="likeCountBar">
                <span id="likeCountText"><strong>0</strong> <span>orang menyukai ini</span></span>
            </div>

            <!-- Area Input Komentar -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="input-area" style="flex-shrink: 0; min-height: 60px; display: flex;">
                <?php
                    $me_av = !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'Assets/galateart_icon.png';
                ?>
                <img class="input-av" src="<?= e($me_av) ?>" alt="" referrerpolicy="no-referrer">
                <div class="input-wrap">
                    <input type="text" class="comment-input" id="commentInput" placeholder="Tambahkan komentar..." autocomplete="off">
                </div>
                <button class="post-btn" id="postBtn">Kirim</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
