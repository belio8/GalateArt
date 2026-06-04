'use strict';

// ── STATE MODAL YANG SEDANG TERBUKA ───────────────────────────
let _postLiked = false;
let _postSaved = false;
let _postLikes = 0;

function _updateLikeUI() {
    const btn  = $('#likePostBtn');
    const text = $('#likeCountText');
    if (btn) {
        btn.innerHTML = _postLiked
            ? '<i class="fas fa-heart"></i>'
            : '<i class="far fa-heart"></i>';
        btn.classList.toggle('liked', _postLiked);
    }
    if (text) {
        const strong = text.querySelector('strong') || document.createElement('strong');
        const span   = text.querySelector('span')   || document.createElement('span');
        strong.textContent = _postLikes.toLocaleString('id');
        span.textContent   = ' orang menyukai ini';
        if (!text.contains(strong)) { text.appendChild(strong); text.appendChild(span); }

    }
}

/** Toggle like — dipanggil dari atribut onclick di HTML. */
function toggleLikePost() {
    _postLiked  = !_postLiked;
    _postLikes += _postLiked ? 1 : -1;
    _updateLikeUI();
}

/** Toggle save — dipanggil dari atribut onclick di HTML. */
function toggleSavePost() {
    _postSaved = !_postSaved;
    const btn = $('#savePostBtn');
    if (btn) {
        btn.innerHTML = _postSaved
            ? '<i class="fas fa-bookmark"></i>'
            : '<i class="far fa-bookmark"></i>';
        btn.classList.toggle('saved', _postSaved);
    }
}

/** Fokus ke input komentar modal — dipanggil dari ikon komentar. */
function focusCommentInput() {
    const input = $('#commentInput');
    if (input) input.focus();
}

function _resetModalState(initialLikes) {
    _postLiked = false;
    _postSaved = false;
    _postLikes = initialLikes;

    const saveBtn = $('#savePostBtn');
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="far fa-bookmark"></i>';
        saveBtn.classList.remove('saved');
    }
    _updateLikeUI();
}

// ── GLOBAL VARIABLE UNTUK CURRENT POST ─────────────────────────
let _currentPostId = null;


// ── INISIALISASI MODAL ─────────────────────────────────────────
(function initArtModal() {
    const modalBg      = $('#modalBg');
    const modalBox     = $('#modalBox');
    const closeBtn     = $('#closeModalPost');
    const modalImg     = $('#modalImageDisplay');
    const phName       = $('#phName');
    const capName      = $('#captionName');
    const capTags      = $('#captionTags');
    const postBtn      = $('#postBtn');
    const commentInput = $('#commentInput');
    const commentFeed  = $('#commentFeed');

    if (!modalBg) return;

    // Buka modal dengan data
    async function openModal(data) {
        // Handle input as an HTMLElement (card) or object (from dynamic render)
        let img = data.img || (data.dataset ? data.dataset.img : '');
        let artist = data.artist || (data.dataset ? data.dataset.artist : '');
        let tags = data.tags || (data.dataset ? data.dataset.tags : '');
        let likes = data.likes !== undefined ? parseInt(data.likes) : (data.dataset ? parseInt(data.dataset.likes || '0') : 0);
        let avatarUrl = data.avatar_url || (data.dataset ? data.dataset.avatarUrl : '');
        let postId = data.postId || (data.dataset ? data.dataset.postId : null);

        // Fallback fallback
        if (!img && data.querySelector) {
            img = data.querySelector('img')?.src || '';
            artist = data.querySelector('.artist-name')?.innerText || '';
            tags = data.querySelector('.hashtags')?.innerText || '';
            let likesText = data.querySelector('.likes')?.innerText || '';
            likes = parseInt(likesText.replace(/\D/g, '')) || 0;
        }

        _currentPostId = postId;

        if (modalImg)  modalImg.src     = img;
        if (phName)    phName.innerText  = artist;
        if (capName)   capName.innerText = artist;
        if (capTags)   capTags.innerText = tags;
        
        const phAv = $('#phAv');
        const capAv = $('#capAv');
        if (avatarUrl) {
            if (phAv) phAv.src = avatarUrl;
            if (capAv) capAv.src = avatarUrl;
        } else {
            const defaultAv = 'https://api.dicebear.com/7.x/avataaars/svg?seed=' + artist.replace('@', '');
            if (phAv) phAv.src = defaultAv;
            if (capAv) capAv.src = defaultAv;
        }

        _resetModalState(likes);
        
        // Render initial skeleton for comments
        if (commentFeed) {
            commentFeed.innerHTML = `
                <div class="caption-block">
                    <img src="${capAv ? capAv.src : ''}" alt="" class="c-av">
                    <div class="c-body">
                        <strong>${escapeHtml(artist)}</strong>
                        <span>Menampilkan karya seni terbaru!</span>
                        <div class="tags">${escapeHtml(tags)}</div>
                        <span class="c-time">Baru saja</span>
                    </div>
                </div>
                <div class="feed-divider"></div>
                <div class="comment-count">Memuat komentar...</div>
            `;
        }

        modalBg.classList.add('open');
        document.body.style.overflow = 'hidden';

        // Load comments if postId exists
        if (postId && commentFeed) {
            try {
                const res = await fetch('api/comments.php?post_id=' + postId);
                const result = await res.json();
                
                if (result.status === 'ok') {
                    renderComments(result.comments, artist, tags, capAv ? capAv.src : '');
                } else {
                    updateCommentCount(0);
                }
            } catch (e) {
                console.error("Gagal memuat komentar:", e);
                updateCommentCount(0);
            }
        }
    }
    
    // Global expose
    window.openArtModal = openModal;
    
    function renderComments(comments, artist, tags, avatarUrl) {
        if (!commentFeed) return;
        
        let html = `
            <div class="caption-block">
                <img src="${escapeHtml(avatarUrl)}" alt="" class="c-av">
                <div class="c-body">
                    <strong>${escapeHtml(artist)}</strong>
                    <span>Menampilkan karya seni terbaru!</span>
                    <div class="tags">${escapeHtml(tags)}</div>
                    <span class="c-time">Baru saja</span>
                </div>
            </div>
            <div class="feed-divider"></div>
            <div class="comment-count">${comments.length} Komentar</div>
        `;
        
        comments.forEach(c => {
            html += `
                <div class="comment-item">
                    <img class="c-av" src="${escapeHtml(c.avatar_url)}" alt="">
                    <div class="c-body">
                        <div class="c-top">
                            <strong>${escapeHtml(c.author)}</strong> <span class="c-text">${escapeHtml(c.content)}</span>
                        </div>
                        <div class="c-bottom">
                            <span class="c-time">${escapeHtml(c.time)}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        commentFeed.innerHTML = html;
    }
    
    function updateCommentCount(count) {
        const countEl = commentFeed.querySelector('.comment-count');
        if (countEl) countEl.innerText = count + ' Komentar';
    }

    function closeArtModal() {
        modalBg.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Pasang klik pada semua kartu
    $$('.art-card, .post-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', e => {
            e.stopPropagation();
            openModal(card);
        });
    });

    // Tutup via tombol ×
    closeBtn && closeBtn.addEventListener('click', e => {
        e.stopPropagation();
        closeArtModal();
    });

    // Tutup saat klik background
    modalBg.addEventListener('click', e => {
        if (e.target === modalBg) closeArtModal();
    });

    // Cegah klik di dalam modal menutup modal
    modalBox && modalBox.addEventListener('click', e => e.stopPropagation());


    // ── KIRIM KOMENTAR ─────────────────────────────────────────
    async function submitComment() {
        if (!commentInput || !commentFeed || !_currentPostId) return;
        const text = commentInput.value.trim();
        if (!text) return;
        
        commentInput.disabled = true;
        
        try {
            const res = await fetch('api/comments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    post_id: _currentPostId,
                    content: text
                })
            });
            const data = await res.json();
            
            if (data.status === 'ok') {
                const c = data.comment;
                const html = `
                    <div class="comment-item">
                        <img class="c-av" src="${escapeHtml(c.avatar_url)}" alt="">
                        <div class="c-body">
                            <div class="c-top">
                                <strong>${escapeHtml(c.author)}</strong> <span class="c-text">${escapeHtml(c.content)}</span>
                            </div>
                            <div class="c-bottom">
                                <span class="c-time">${escapeHtml(c.time)}</span>
                            </div>
                        </div>
                    </div>
                `;
                commentFeed.insertAdjacentHTML('beforeend', html);
                commentFeed.scrollTop = commentFeed.scrollHeight;
                
                // Update comment count
                const countEl = commentFeed.querySelector('.comment-count');
                if (countEl) {
                    const currentCount = parseInt(countEl.innerText) || 0;
                    countEl.innerText = (currentCount + 1) + ' Komentar';
                }
            } else {
                alert(data.message || 'Gagal mengirim komentar.');
            }
        } catch (err) {
            console.error('Submit comment error:', err);
            alert('Gagal mengirim komentar (Network Error).');
        }
        
        commentInput.disabled = false;
        commentInput.value = '';
        commentInput.focus();
    }
 
    postBtn      && postBtn.addEventListener('click', submitComment);
    commentInput && commentInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') submitComment();
    });
})();



// ── FOLLOW BUTTON (delegasi, semua halaman) ───────────────────
(function initFollowButtons() {
    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-follow, .follow-btn');
        if (!btn) return;

        const isFollowing = btn.dataset.following === 'true';
        btn.dataset.following = String(!isFollowing);
        btn.textContent = isFollowing ? 'Follow' : 'Following';
    });
})();