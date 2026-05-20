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
        text.innerHTML = `<strong>${_postLikes.toLocaleString('id')}</strong> <span>orang menyukai ini</span>`;
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

    // Buka modal dengan data kartu yang diklik
    function openModal(card) {
        const imgEl   = card.querySelector('img');
        const nameEl  = card.querySelector('.artist-name');
        const tagsEl  = card.querySelector('.hashtags');
        const likesEl = card.querySelector('.likes');

        if (modalImg && imgEl)  modalImg.src     = imgEl.src;
        if (phName  && nameEl)  phName.innerText  = nameEl.innerText;
        if (capName && nameEl)  capName.innerText = nameEl.innerText;
        if (capTags && tagsEl)  capTags.innerText = tagsEl.innerText;

        const initialLikes = likesEl
            ? parseInt(likesEl.innerText.replace(/\D/g, '')) || 0
            : 0;

        _resetModalState(initialLikes);
        modalBg.classList.add('open');
        document.body.style.overflow = 'hidden';
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
    function submitComment() {
        if (!commentInput || !commentFeed) return;
        const text = commentInput.value.trim();
        if (!text) return;

        const item = document.createElement('div');
        item.className = 'comment-item';
        item.innerHTML = `
            <img class="c-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=me" alt="">
            <div class="c-body">
                <div class="c-top">
                    <strong>@saya</strong>
                    <span class="c-text">${text}</span>
                </div>
                <div class="c-bottom">
                    <span class="c-time">Baru saja</span>
                </div>
            </div>`;
        commentFeed.appendChild(item);
        commentFeed.scrollTop = commentFeed.scrollHeight;
        commentInput.value = '';
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