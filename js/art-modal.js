'use strict';

// ── STATE MODAL YANG SEDANG TERBUKA ───────────────────────────
let _postLiked = false;
let _postSaved = false;
let _postLikes = 0;

function _updateLikeUI() {
    const btn = $('#likePostBtn');
    const text = $('#likeCountText');
    if (btn) {
        btn.innerHTML = _postLiked
            ? '<i class="fas fa-heart"></i>'
            : '<i class="far fa-heart"></i>';
        btn.classList.toggle('liked', _postLiked);
    }
    if (text) {
        const strong = text.querySelector('strong') || document.createElement('strong');
        const span = text.querySelector('span') || document.createElement('span');
        strong.textContent = _postLikes.toLocaleString('id');
        span.textContent = ' orang menyukai ini';
        if (!text.contains(strong)) { text.appendChild(strong); text.appendChild(span); }

    }
}

/** Toggle like — dipanggil dari atribut onclick di HTML. */
async function toggleLikePost() {
    if (!_currentPostId) return;
    const btn = $('#likePostBtn');
    if (btn) btn.disabled = true;
    try {
        const res = await fetch('api/like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: _currentPostId })
        });
        const data = await res.json();
        if (data.status === 'ok') {
            _postLiked = data.liked;
            _postLikes = data.likes_count;
            _updateLikeUI();
        } else if (data.message === 'Belum login.') {
            // Let auth.js handle it or show alert
        }
    } catch (e) {
        console.error('Error liking post:', e);
    }
    if (btn) btn.disabled = false;
}

/** Toggle save — dipanggil dari atribut onclick di HTML. */
async function toggleSavePost() {
    if (!_currentPostId) return;
    const btn = $('#savePostBtn');
    if (btn) btn.disabled = true;
    try {
        const res = await fetch('api/save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: _currentPostId })
        });
        const data = await res.json();
        if (data.status === 'ok') {
            _postSaved = data.saved;
            if (btn) {
                btn.innerHTML = _postSaved
                    ? '<i class="fas fa-bookmark"></i>'
                    : '<i class="far fa-bookmark"></i>';
                btn.classList.toggle('saved', _postSaved);
            }
        }
    } catch (e) {
        console.error('Error saving post:', e);
    }
    if (btn) btn.disabled = false;
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
    const modalBg = $('#modalBg');
    const modalBox = $('#modalBox');
    const closeBtn = $('#closeModalPost');
    const modalImg = $('#modalImageDisplay');
    const phName = $('#phName');
    const capName = $('#captionName');
    const capTags = $('#captionTags');
    const postBtn = $('#postBtn');
    const commentInput = $('#commentInput');
    const commentFeed = $('#commentFeed');

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
        let title = data.title || (data.dataset ? data.dataset.title : '');

        // Fallback fallback
        if (!img && data.querySelector) {
            img = data.querySelector('img')?.src || '';
            artist = data.querySelector('.card-avatar-tooltip')?.innerText || data.dataset?.artist || '';
            tags = data.querySelector('.hashtags')?.innerText || '';
            let likesText = data.querySelector('.likes')?.innerText || '';
            likes = parseInt(likesText.replace(/\D/g, '')) || 0;
            title = data.querySelector('.art-title')?.innerText || title;
        }

        _currentPostId = postId;

        if (modalImg) modalImg.src = img;
        if (phName) phName.innerText = artist;
        if (capName) capName.innerText = artist;
        if (capTags) capTags.innerText = tags;

        const phAv = $('#phAv');
        const capAv = $('#capAv');

        // Add click listeners to navigate to profile
        const navigateToProfile = () => {
            const username = artist.replace(/^@/, '');
            if (username) {
                window.location.href = `visit-profile.php?user=${username}`;
            }
        };

        if (phName) {
            phName.style.cursor = 'pointer';
            phName.onclick = navigateToProfile;
        }
        if (phAv) {
            phAv.style.cursor = 'pointer';
            phAv.onclick = navigateToProfile;
        }
        if (capName) {
            capName.style.cursor = 'pointer';
            capName.onclick = navigateToProfile;
        }
        if (capAv) {
            capAv.style.cursor = 'pointer';
            capAv.onclick = navigateToProfile;
        }

        const setAvatar = (el) => {
            if (!el) return;
            el.src = avatarUrl || 'Assets/galateart_icon.png';
            el.onerror = function () {
                this.onerror = null;
                this.src = 'Assets/galateart_icon.png';
            };
        };

        setAvatar(phAv);
        setAvatar(capAv);

        _resetModalState(likes);

        // Render initial skeleton for comments
        if (commentFeed) {
            commentFeed.innerHTML = `
                <div class="caption-block" style="padding-top: 10px;">
                    <div class="c-body" style="margin-left: 0;">
                        ${title ? `<h3 style="margin: 0 0 8px 0; font-size: 1.1rem; color: #fff;">${escapeHtml(title)}</h3>` : ''}
                        <span style="font-weight: 500; font-size: 0.95rem;">Menampilkan karya seni terbaru!</span>
                        <div class="tags" style="margin-top: 4px;">${escapeHtml(tags)}</div>
                    </div>
                </div>
                <div class="feed-divider"></div>
                <div class="comment-count">Memuat komentar...</div>
            `;
        }

        modalBg.classList.add('open');
        document.body.style.overflow = 'hidden';

        // Load comments and post status if postId exists
        if (postId) {
            try {
                const [resStatus, resComments] = await Promise.all([
                    fetch('api/post-status.php?post_id=' + postId),
                    fetch('api/comments.php?post_id=' + postId)
                ]);

                const data = await resStatus.json();
                const result = await resComments.json();

                let descText = "Menampilkan karya seni terbaru!";
                if (data.status === 'ok') {
                    _postLiked = data.liked;
                    _postSaved = data.saved;
                    _postLikes = data.likes_count;

                    const saveBtn = $('#savePostBtn');
                    if (saveBtn) {
                        saveBtn.innerHTML = _postSaved
                            ? '<i class="fas fa-bookmark"></i>'
                            : '<i class="far fa-bookmark"></i>';
                        saveBtn.classList.toggle('saved', _postSaved);
                    }
                    _updateLikeUI();

                    // Update Follow button
                    const followBtn = $('#followBtn');
                    if (followBtn && data.artist_id) {
                        followBtn.dataset.artistId = data.artist_id;
                        followBtn.dataset.following = data.is_following ? 'true' : 'false';
                        followBtn.textContent = data.is_following ? 'Following' : 'Follow';
                    }

                    if (data.description) {
                        descText = data.description;
                    }
                    if (data.title) {
                        title = data.title;
                    }

                    // ── Render Purchase / Download Bar ──────────────
                    renderPurchaseBar(data, postId);
                }

                if (result.status === 'ok') {
                    renderComments(result.comments, artist, tags, capAv ? capAv.src : '', descText, title);
                } else {
                    updateCommentCount(0);
                }
            } catch (e) {
                console.error("Gagal memuat status post atau komentar:", e);
                updateCommentCount(0);
            }
        }
    }

    // Global expose
    window.openArtModal = openModal;

    function renderComments(comments, artist, tags, avatarUrl, descText = "Menampilkan karya seni terbaru!", title = "") {
        if (!commentFeed) return;

        let html = `
            <div class="caption-block" style="padding-top: 10px;">
                <div class="c-body" style="margin-left: 0;">
                    ${title ? `<h3 style="margin: 0 0 8px 0; font-size: 1.1rem; color: #fff;">${escapeHtml(title)}</h3>` : ''}
                    <span style="font-weight: 500; font-size: 0.95rem;">${escapeHtml(descText)}</span>
                    <div class="tags" style="margin-top: 4px;">${escapeHtml(tags)}</div>
                </div>
            </div>
            <div class="feed-divider"></div>
            <div class="comment-count">${comments.length} Komentar</div>
        `;

        comments.forEach(c => {
            html += `
                <div class="comment-item">
                    <img class="c-av" src="${escapeHtml(c.avatar_url)}" alt="" referrerpolicy="no-referrer">
                    <div class="c-body">
                        <div class="c-top" style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <strong style="color: #fff; font-size: 14px;">${escapeHtml(c.author)}</strong>
                            <span class="c-time" style="color: #888; font-size: 11px;">${escapeHtml(c.time)}</span>
                        </div>
                        <div class="c-text" style="color: #d1d1d1; font-size: 14px; line-height: 1.4;">${escapeHtml(c.content)}</div>
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

    // (Removed stopPropagation to allow delegated follow buttons to work)


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

    postBtn && postBtn.addEventListener('click', submitComment);
    commentInput && commentInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') submitComment();
    });
})();



// ── FOLLOW BUTTON (delegasi, semua halaman) ───────────────────
(function initFollowButtons() {
    document.addEventListener('click', async e => {
        const btn = e.target.closest('.btn-follow, .follow-btn');
        if (!btn) return;

        const artistId = btn.dataset.artistId;
        if (!artistId) return;

        const allArtistBtns = document.querySelectorAll(`.btn-follow[data-artist-id="${artistId}"], .follow-btn[data-artist-id="${artistId}"]`);

        allArtistBtns.forEach(b => b.disabled = true);
        try {
            const res = await fetch('api/follow.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ artist_id: artistId })
            });
            const data = await res.json();
            if (data.status === 'ok') {
                const followingStr = String(data.following);
                allArtistBtns.forEach(b => {
                    b.dataset.following = followingStr;
                    // Preserve '+Follow' styling if present on unfollow
                    b.textContent = data.following ? 'Following' : (b.textContent.includes('+') ? '+Follow' : 'Follow');
                });
            } else if (data.message) {
                alert(data.message);
                console.warn('Follow error:', data.message);
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
            console.error('Follow error:', err);
        }
        allArtistBtns.forEach(b => b.disabled = false);
    });
})();


// ── PURCHASE BAR RENDERING ────────────────────────────────────
function renderPurchaseBar(data, postId) {
    const btn = document.getElementById('orderBtn');
    if (!btn) return;

    // Reset inline styles
    btn.style.display = '';
    btn.style.background = '';
    btn.style.color = '';

    const hasPrice = data.price > 0 || data.is_free;
    const hasSource = data.has_source;

    // Jika post tidak punya harga dan bukan free download, kembalikan ke tombol order komisi biasa
    if (!hasPrice && !hasSource) {
        btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Order Komisi';
        btn.onclick = () => location.href = 'commission.php';
        return;
    }

    const priceLabel = data.is_free 
        ? 'Gratis' 
        : `Rp ${Number(data.price).toLocaleString('id-ID')}`;

    if (data.is_owner) {
        // Artis pemilik
        btn.innerHTML = `<i class="fas fa-download"></i> Unduh File Asli`;
        btn.style.background = 'var(--accent)';
        btn.style.color = '#fff';
        btn.onclick = () => window.open(`api/download-post.php?post_id=${postId}`, '_blank');
    } else if (data.is_purchased) {
        // Sudah dibeli
        btn.innerHTML = `<i class="fas fa-download"></i> Unduh File Asli`;
        btn.style.background = 'linear-gradient(135deg,#4ade80,#22c55e)';
        btn.style.color = '#fff';
        btn.onclick = () => window.open(`api/download-post.php?post_id=${postId}`, '_blank');
    } else if (data.is_in_cart) {
        // Sudah ada di cart
        btn.innerHTML = `<i class="fas fa-shopping-cart"></i> Lihat Keranjang`;
        btn.style.background = '#facc15';
        btn.style.color = '#1a1a2e';
        btn.onclick = () => location.href = 'cart.php';
    } else {
        // Belum dibeli — tampilkan tombol beli
        btn.innerHTML = `<i class="fas fa-cart-plus"></i> ${priceLabel}`;
        btn.style.background = 'linear-gradient(135deg,var(--accent),#8b5cf6)';
        btn.style.color = '#fff';
        btn.onclick = () => addPostToCart(postId, priceLabel);
    }
}

async function addPostToCart(postId, priceLabel) {
    const btn = document.getElementById('orderBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menambahkan...';
    }

    try {
        const res = await fetch('api/add-to-cart-post.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: postId })
        });
        
        // Handle 401 unauthenticated
        if (res.status === 401) {
            alert('Anda harus login terlebih dahulu.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-cart-plus"></i> ${priceLabel}`;
            }
            return;
        }

        const data = await res.json();

        if (data.status === 'ok') {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-shopping-cart"></i> Lihat Keranjang`;
                btn.style.background = '#facc15';
                btn.style.color = '#1a1a2e';
                btn.onclick = () => location.href = 'cart.php';
            }
        } else {
            alert(data.message || 'Gagal menambahkan ke keranjang.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-cart-plus"></i> ${priceLabel}`;
            }
        }
    } catch (e) {
        console.error('Error adding to cart:', e);
        alert('Terjadi kesalahan jaringan.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-cart-plus"></i> ${priceLabel}`;
        }
    }
}