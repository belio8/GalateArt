/* ============================================================
   GalateArt — script.js
   Satu file JS untuk semua halaman:
   landing · profile · top-artists · trending · artist-profile
   messages · commission · cart · payment · search-results
   ============================================================ */

'use strict';

// ============================================================
// 0. UTILITAS
// ============================================================

/** Ambil elemen tunggal, null-safe. */
const $  = (sel, ctx = document) => ctx.querySelector(sel);
/** Ambil semua elemen, selalu array. */
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/** Format angka ke Rupiah. */
function formatRupiah(num) {
    return 'Rp ' + Number(num).toLocaleString('id-ID');
}

/** Simpan / baca / hapus dari localStorage dengan try-catch. */
const Store = {
    get:    (k)    => { try { return localStorage.getItem(k); }    catch { return null; } },
    set:    (k, v) => { try { localStorage.setItem(k, v); }        catch {} },
    remove: (k)    => { try { localStorage.removeItem(k); }        catch {} },
};


// ============================================================
// 1. NAVIGASI — MENU HAMBURGER
// ============================================================
(function initMenu() {
    const toggle = $('#menuToggle');
    const menu   = $('#dropdownMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', e => {
        menu.classList.toggle('show');
        e.stopPropagation();
    });

    document.addEventListener('click', e => {
        if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
})();


// ============================================================
// 2. NOTIFIKASI DROPDOWN
// ============================================================
const NOTIFICATIONS = [
    { text: '<strong>@artis_lokal</strong> mengunggah karya baru!', time: '2 menit lalu' },
    { text: 'Pesanan aset digital kamu telah selesai.',              time: '1 jam lalu'  },
    { text: 'Seseorang menyukai karya Anda.',                        time: '3 jam lalu'  },
];

function renderNotifications() {
    const body = $('#notifBody');
    if (!body) return;

    if (!NOTIFICATIONS.length) {
        body.innerHTML = `
            <div style="padding:30px;text-align:center;color:#b3b3b3;">
                <i class="far fa-bell-slash" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                <p>Tidak ada notifikasi</p>
            </div>`;
        return;
    }

    body.innerHTML = NOTIFICATIONS.map(n =>
        `<div class="notif-item"><p>${n.text}</p><span>${n.time}</span></div>`
    ).join('');
}

(function initNotifDropdown() {
    const toggle   = $('#notifToggle');
    const dropdown = $('#notifDropdown');
    const menuDD   = $('#dropdownMenu');
    if (!toggle || !dropdown) return;

    toggle.addEventListener('click', e => {
        dropdown.classList.toggle('show');
        if (menuDD) menuDD.classList.remove('show');
        e.stopPropagation();
    });

    document.addEventListener('click', e => {
        if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    renderNotifications();
})();


// ============================================================
// 3. STATUS LOGIN — tampilkan / sembunyikan elemen navbar
// ============================================================

/**
 * Terapkan state login ke elemen-elemen navbar.
 * Dipanggil saat daftar, masuk, atau saat halaman dimuat.
 */
function applyLoginState() {
    const role = Store.get('userRole'); // 'artist' | 'regular' | null

    // Elemen khusus halaman landing
    const btnSignup  = $('#btnSignup');
    const btnLogin   = $('#btnLogin');
    const btnArtist  = $('#btnArtist');
    const navIcons   = $('.nav-icons');
    const profileLink = $('#userProfileLink');

    if (role) {
        // Sudah login → sembunyikan tombol auth, tampilkan ikon
        if (btnSignup)  btnSignup.style.display  = 'none';
        if (btnLogin)   btnLogin.style.display    = 'none';
        if (btnArtist)  btnArtist.style.display   = 'none';
        if (navIcons)   navIcons.style.display     = 'flex';
        if (profileLink) profileLink.style.display = 'flex';
    } else {
        // Belum login → tampilkan tombol auth, sembunyikan ikon
        if (btnSignup)  btnSignup.style.display  = '';
        if (btnLogin)   btnLogin.style.display    = '';
        if (btnArtist)  btnArtist.style.display   = '';
        if (navIcons)   navIcons.style.display     = 'none';
        if (profileLink) profileLink.style.display = 'none';
    }
}

// Terapkan saat DOM siap
document.addEventListener('DOMContentLoaded', applyLoginState);


// ============================================================
// 4. MODAL AUTH (DAFTAR / MASUK / ARTIS) — hanya landing.html
// ============================================================
(function initAuthModals() {
    // Elemen modal
    const registerModal   = $('#registerModal');
    const artistModal     = $('#artistModal');
    const loginModal      = $('#loginModal');

    if (!registerModal && !artistModal && !loginModal) return; // bukan halaman landing

    const btnSignup       = $('#btnSignup');
    const btnLogin        = $('#btnLogin');
    const btnArtist       = $('#btnArtist');
    const closeRegister   = $('#closeModal');
    const closeArtist     = $('#closeArtistModal');
    const closeLogin      = $('#closeLoginModal');
    const switchToSignup  = $('#switchToSignup');
    const loginForm       = $('#loginForm');
    const userForm        = $('#registerModal form');
    const artistForm      = $('#artistModal form');

    // --- Helper buka/tutup ---
    function openModal(modal)  { modal && modal.classList.add('show'); }
    function closeModal(modal) { modal && modal.classList.remove('show'); }

    // Tutup semua saat klik overlay gelap
    window.addEventListener('click', e => {
        if (e.target === registerModal) closeModal(registerModal);
        if (e.target === artistModal)   closeModal(artistModal);
        if (e.target === loginModal)    closeModal(loginModal);
    });

    // Buka modal
    btnSignup && btnSignup.addEventListener('click', () => openModal(registerModal));
    btnLogin  && btnLogin.addEventListener('click',  () => openModal(loginModal));
    btnArtist && btnArtist.addEventListener('click', () => openModal(artistModal));

    // Tutup modal via tombol ×
    closeRegister && closeRegister.addEventListener('click', () => closeModal(registerModal));
    closeArtist   && closeArtist.addEventListener('click',   () => closeModal(artistModal));
    closeLogin    && closeLogin.addEventListener('click',    () => closeModal(loginModal));

    // Beralih dari login ke daftar
    if (switchToSignup) {
        switchToSignup.addEventListener('click', e => {
            e.preventDefault();
            closeModal(loginModal);
            openModal(registerModal);
        });
    }

    // --- Submit: Daftar User Biasa ---
    if (userForm) {
        userForm.addEventListener('submit', e => {
            e.preventDefault();
            Store.set('userRole', 'regular');
            alert('Pendaftaran berhasil! Selamat datang di GalateArt.');
            closeModal(registerModal);
            applyLoginState();
        });
    }

    // --- Submit: Daftar Artis ---
    if (artistForm) {
        artistForm.addEventListener('submit', e => {
            e.preventDefault();
            Store.set('userRole', 'artist');
            alert('Pendaftaran Artis berhasil! Portofolio Anda sedang ditinjau.');
            closeModal(artistModal);
            applyLoginState();
        });
    }

    // --- Submit: Masuk ---
    // Username artis demo yang diizinkan (bisa diperluas)
    const ARTIST_ACCOUNTS = ['Miew', 'miew'];

    if (loginForm) {
        loginForm.addEventListener('submit', e => {
            e.preventDefault();
            const username = $('#loginUsername').value.trim();
            const role = ARTIST_ACCOUNTS.includes(username) ? 'artist' : 'regular';
            Store.set('userRole', role);
            alert('Berhasil masuk!');
            closeModal(loginModal);
            applyLoginState();
        });
    }
})();


// ============================================================
// 5. LOGOUT — hanya profile.html
// ============================================================
(function initLogout() {
    const btnLogout = $('#btnLogout');
    if (!btnLogout) return;

    btnLogout.addEventListener('click', () => {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            Store.remove('userRole');
            window.location.href = 'landing.html';
        }
    });
})();


// ============================================================
// 6. HALAMAN PROFIL — tab & badge role
// ============================================================

/** Ganti tab aktif di halaman profil. */
function switchTab(clickedBtn, targetId) {
    $$('.tab-btn').forEach(b => b.classList.remove('active'));
    $$('.tab-content').forEach(c => c.classList.remove('active'));
    clickedBtn.classList.add('active');
    const target = $('#' + targetId);
    if (target) target.classList.add('active');
}

(function initProfilePage() {
    const wrapper = $('#profileWrapper');
    if (!wrapper) return;

    const badge = $('#accountBadgeText');
    const role  = Store.get('userRole') || 'regular';

    if (role === 'artist') {
        wrapper.classList.remove('is-regular');
        wrapper.classList.add('is-artist');
        if (badge) badge.innerText = 'Artist Account';
    } else {
        wrapper.classList.remove('is-artist');
        wrapper.classList.add('is-regular');
        if (badge) badge.innerText = 'Regular Account';

        // Untuk regular, tab Posts disembunyikan via CSS — paksa ke Bio
        const bioBtn = $('.profile-tabs button:first-child');
        if (bioBtn) switchTab(bioBtn, 'content-bio');
    }
})();


// ============================================================
// 7. MODAL POPUP KARYA SENI (art-card / post-card)
// ============================================================

// State like & save untuk post yang sedang terbuka
let _postLiked  = false;
let _postSaved  = false;
let _postLikes  = 0;

function _updateLikeUI() {
    const btn  = $('#likePostBtn');
    const text = $('#likeCountText');
    if (btn)  btn.innerHTML = _postLiked ? '<i class="fas fa-heart"></i>' : '<i class="far fa-heart"></i>';
    if (btn)  btn.classList.toggle('liked', _postLiked);
    if (text) text.innerHTML = `<strong>${_postLikes.toLocaleString('id')}</strong> <span>orang menyukai ini</span>`;
}

function toggleLikePost() {
    _postLiked  = !_postLiked;
    _postLikes += _postLiked ? 1 : -1;
    _updateLikeUI();
}

function toggleSavePost() {
    _postSaved = !_postSaved;
    const btn = $('#savePostBtn');
    if (btn) {
        btn.innerHTML = _postSaved ? '<i class="fas fa-bookmark"></i>' : '<i class="far fa-bookmark"></i>';
        btn.classList.toggle('saved', _postSaved);
    }
}

/** Fokus ke input komentar modal (dipanggil dari tombol ikon komentar). */
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

(function initArtModal() {
    const modalBg   = $('#modalBg');
    const modalBox  = $('#modalBox');
    const closeBtn  = $('#closeModalPost');
    const modalImg  = $('#modalImageDisplay');
    const phName    = $('#phName');
    const capName   = $('#captionName');
    const capTags   = $('#captionTags');
    const postBtn   = $('#postBtn');
    const commentInput = $('#commentInput');
    const commentFeed  = $('#commentFeed');

    if (!modalBg) return;

    // --- Buka modal saat klik art-card atau post-card ---
    function openModal(card) {
        const imgEl   = card.querySelector('img');
        const nameEl  = card.querySelector('.artist-name');
        const tagsEl  = card.querySelector('.hashtags');
        const likesEl = card.querySelector('.likes');

        if (modalImg && imgEl)   modalImg.src     = imgEl.src;
        if (phName   && nameEl)  phName.innerText  = nameEl.innerText;
        if (capName  && nameEl)  capName.innerText  = nameEl.innerText;
        if (capTags  && tagsEl)  capTags.innerText  = tagsEl.innerText;

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

    // Pasang klik pada semua card (termasuk yang ditambahkan secara statis)
    $$('.art-card, .post-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', e => {
            e.stopPropagation();
            openModal(card);
        });
    });

    // Tombol tutup (×)
    if (closeBtn) {
        closeBtn.addEventListener('click', e => {
            e.stopPropagation();
            closeArtModal();
        });
    }

    // Tutup saat klik background
    modalBg.addEventListener('click', e => {
        if (e.target === modalBg) closeArtModal();
    });

    // Cegah klik di dalam modal menutup modal
    if (modalBox) {
        modalBox.addEventListener('click', e => e.stopPropagation());
    }

    // --- Kirim komentar di dalam modal ---
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

    if (postBtn)      postBtn.addEventListener('click', submitComment);
    if (commentInput) commentInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') submitComment();
    });
})();


// ============================================================
// 8. FOLLOW BUTTON — toggle teks & style (semua halaman)
// ============================================================
(function initFollowButtons() {
    // Delegasi event ke document agar menangkap tombol yang mungkin
    // dirender secara dinamis
    document.addEventListener('click', e => {
        const btn = e.target.closest('.btn-follow, .follow-btn');
        if (!btn) return;

        const isFollowing = btn.dataset.following === 'true';
        btn.dataset.following = String(!isFollowing);

        if (btn.classList.contains('btn-follow')) {
            btn.textContent = isFollowing ? 'Follow' : 'Following';
        } else {
            // .follow-btn di dalam modal
            btn.textContent = isFollowing ? 'Follow' : 'Following';
        }
    });
})();


// ============================================================
// 9. SEARCH BAR — navigasi ke search-results.html
// ============================================================
(function initSearchBar() {
    $$('.search-bar input').forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key !== 'Enter') return;
            const q = input.value.trim();
            if (q) window.location.href = `search-results.html?q=${encodeURIComponent(q)}`;
        });
    });
})();


// ============================================================
// 10. SHOPPING CART ICON — arahkan ke cart.html
// ============================================================
(function initCartIcon() {
    $$('.nav-icons .fa-shopping-cart').forEach(icon => {
        icon.addEventListener('click', () => {
            window.location.href = 'cart.html';
        });
    });
})();


// ============================================================
// 11. MESSAGE ICON — arahkan ke messages.html
// ============================================================
(function initMessageIcon() {
    $$('.nav-icons .fa-comment').forEach(icon => {
        icon.addEventListener('click', () => {
            window.location.href = 'messages.html';
        });
    });
})();