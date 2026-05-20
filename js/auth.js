
'use strict';

// ── 1. TERAPKAN STATE LOGIN KE NAVBAR ─────────────────────────

function applyLoginState() {
    const role = Store.get('userRole'); // 'artist' | 'regular' | null

    const btnSignup   = $('#btnSignup');
    const btnLogin    = $('#btnLogin');
    const btnArtist   = $('#btnArtist');
    const navIcons    = $('.nav-icons');
    const profileLink = $('#userProfileLink');

    if (role === 'artist' || role === 'regular') {
        if (btnSignup)   btnSignup.style.display   = 'none';
        if (btnLogin)    btnLogin.style.display     = 'none';
        if (btnArtist)   btnArtist.style.display    = 'none';
        if (navIcons)    navIcons.style.display      = 'flex';
        if (profileLink) profileLink.style.display   = 'flex';
    } else if (role === 'admin') {
        const isAdminPage = location.pathname.endsWith('/admin.html');
        if (!isAdminPage) {
            location.href = 'admin.html';
        }
    } else {
        if (btnSignup)   btnSignup.style.display   = '';
        if (btnLogin)    btnLogin.style.display     = '';
        if (btnArtist)   btnArtist.style.display    = '';
        if (navIcons)    navIcons.style.display      = 'none';
        if (profileLink) profileLink.style.display   = 'none';
    }
}

document.addEventListener('DOMContentLoaded', applyLoginState);


// ── 2. MODAL AUTH (landing.html) ──────────────────────────────
(function initAuthModals() {
    const registerModal = $('#registerModal');
    const artistModal   = $('#artistModal');
    const loginModal    = $('#loginModal');
    if (!registerModal && !artistModal && !loginModal) return;

    const btnSignup      = $('#btnSignup');
    const btnLogin       = $('#btnLogin');
    const btnArtist      = $('#btnArtist');
    const closeRegister  = $('#closeModal');
    const closeArtist    = $('#closeArtistModal');
    const closeLogin     = $('#closeLoginModal');
    const switchToSignup = $('#switchToSignup');
    const loginForm      = $('#loginForm');
    const userForm       = registerModal ? registerModal.querySelector('form') : null;
    const artistForm     = artistModal   ? artistModal.querySelector('form')   : null;

    function openModal(modal)  { modal && modal.classList.add('show'); }
    function closeModal(modal) { modal && modal.classList.remove('show'); }

    // Tutup saat klik overlay gelap
    window.addEventListener('click', e => {
        if (e.target === registerModal) closeModal(registerModal);
        if (e.target === artistModal)   closeModal(artistModal);
        if (e.target === loginModal)    closeModal(loginModal);
    });

    // Buka modal
    btnSignup && btnSignup.addEventListener('click', () => openModal(registerModal));
    btnLogin  && btnLogin.addEventListener('click',  () => openModal(loginModal));
    btnArtist && btnArtist.addEventListener('click', () => openModal(artistModal));

    // Tutup via ×
    closeRegister && closeRegister.addEventListener('click', () => closeModal(registerModal));
    closeArtist   && closeArtist.addEventListener('click',   () => closeModal(artistModal));
    closeLogin    && closeLogin.addEventListener('click',    () => closeModal(loginModal));

    // Beralih dari login → daftar
    switchToSignup && switchToSignup.addEventListener('click', e => {
        e.preventDefault();
        closeModal(loginModal);
        openModal(registerModal);
    });

    // Submit: Daftar User Biasa
    userForm && userForm.addEventListener('submit', e => {
        e.preventDefault();
        Store.set('userRole', 'regular');
        alert('Pendaftaran berhasil! Selamat datang di GalateArt.');
        closeModal(registerModal);
        applyLoginState();
        window.location.href = 'landing-reguler.html';
    });

    // Submit: Daftar Artis
    artistForm && artistForm.addEventListener('submit', e => {
        e.preventDefault();
        Store.set('userRole', 'artist');
        alert('Pendaftaran Artis berhasil! Portofolio Anda sedang ditinjau.');
        closeModal(artistModal);
        applyLoginState();
        window.location.href = 'landing-artist.html';
    });

    // Submit: Masuk
    const DEFAULT_ADMIN = ['admin', 'admin123'];
    const ARTIST_ACCOUNTS = ['Miew', 'miew'];

    loginForm && loginForm.addEventListener('submit', e => {
        e.preventDefault();
        const username = $('#loginUsername').value.trim();
        let role;
        if (username === DEFAULT_ADMIN[0]) {
            role = 'admin';
        } else if (ARTIST_ACCOUNTS.includes(username)) {
            role = 'artist';
        } else {
            role = 'regular';
        }
        Store.set('userRole', role);
        alert('Berhasil masuk!');
        closeModal(loginModal);
        applyLoginState();
        if (role === 'admin') {
            window.location.href = 'admin.html';
        } else if (role === 'artist') {
            window.location.href = 'landing-artist.html';
        } else {
            window.location.href = 'landing-reguler.html';
        }
    });
})();


// ── 3. LOGOUT (profile.html / admin.html) ────────────────────
(function initLogout() {
    const btnLogout = $('#btnLogout') || $('#logoutBtn');
    if (!btnLogout) return;

    btnLogout.addEventListener('click', () => {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            Store.remove('userRole');
            window.location.href = 'landing.html';
        }
    });
})();