'use strict';

// ── 1. TERAPKAN STATE LOGIN KE NAVBAR ─────────────────────────

function applyLoginState() {
    const role = Store.get('userRole'); // 'artist' | 'regular' | 'admin' | null

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

        // Tombol interaksi AKTIF untuk user yang sudah login
        $$('.btn-follow, .follow-btn').forEach(el => el.style.display = '');
        $$('#likePostBtn, #savePostBtn, #postBtn, #orderBtn').forEach(el => el && (el.style.display = ''));
        $('#fabPostBtn') && ($('#fabPostBtn').style.display = role === 'artist' ? 'flex' : 'none');

    } else if (role === 'admin') {
        const isAdminPage = location.pathname.endsWith('/admin.html');
        if (!isAdminPage) location.href = 'admin.html';

    } else {
        // Belum login — sembunyikan semua tombol interaksi
        if (btnSignup)   btnSignup.style.display   = '';
        if (btnLogin)    btnLogin.style.display     = '';
        if (btnArtist)   btnArtist.style.display    = '';
        if (navIcons)    navIcons.style.display      = 'none';
        if (profileLink) profileLink.style.display   = 'none';

        // Sembunyikan tombol interaksi yang butuh login
        $$('.btn-follow, .follow-btn').forEach(el => el.style.display = 'none');
        $$('#likePostBtn, #savePostBtn, #postBtn, #orderBtn').forEach(el => el && (el.style.display = 'none'));
        $('#fabPostBtn') && ($('#fabPostBtn').style.display = 'none');
    }
}

document.addEventListener('DOMContentLoaded', applyLoginState);


// ── 2. HELPER: fetch ke api/auth.php ─────────────────────────

async function authFetch(action, payload = {}) {
    try {
        const res = await fetch('api/auth.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ action, ...payload }),
        });
        return await res.json();
    } catch {
        return { status: 'error', message: 'Tidak dapat menghubungi server.' };
    }
}

function saveSession(user) {
    Store.set('userRole', user.role);
    Store.set('userId',   user.id);
    Store.set('username', user.username);
}

function clearSession() {
    Store.remove('userRole');
    Store.remove('userId');
    Store.remove('username');
}


// ── 3. MODAL AUTH ─────────────────────────────────────────────

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

    // Fungsi tampilkan pesan error di dalam modal
    function showError(formEl, msg) {
        let errEl = formEl.querySelector('.auth-error-msg');
        if (!errEl) {
            errEl = document.createElement('p');
            errEl.className = 'auth-error-msg';
            errEl.style.cssText = 'color:#ff4d4d;font-size:13px;margin:6px 0;text-align:center;';
            formEl.querySelector('.btn-submit').before(errEl);
        }
        errEl.textContent = msg;
    }
    function clearError(formEl) {
        const errEl = formEl.querySelector('.auth-error-msg');
        if (errEl) errEl.textContent = '';
    }

    // Tutup saat klik overlay
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

    // Beralih login → daftar
    switchToSignup && switchToSignup.addEventListener('click', e => {
        e.preventDefault();
        closeModal(loginModal);
        openModal(registerModal);
    });

    // ── Submit: Daftar User Biasa ──
    userForm && userForm.addEventListener('submit', async e => {
        e.preventDefault();
        clearError(userForm);

        const btn = userForm.querySelector('.btn-submit');
        btn.disabled = true;
        btn.textContent = 'Memproses...';

        const data = await authFetch('register', {
            username: $('#username')?.value.trim(),
            email:    $('#email')?.value.trim(),
            password: $('#password')?.value,
        });

        btn.disabled = false;
        btn.textContent = 'Buat Akun';

        if (data.status === 'ok') {
            saveSession(data.user);
            closeModal(registerModal);
            applyLoginState();
            window.location.href = 'landing-reguler.html';
        } else {
            showError(userForm, data.message);
        }
    });

    // ── Submit: Daftar Artis ──
    artistForm && artistForm.addEventListener('submit', async e => {
        e.preventDefault();
        clearError(artistForm);

        const btn = artistForm.querySelector('.btn-submit');
        btn.disabled = true;
        btn.textContent = 'Memproses...';

        const data = await authFetch('register_artist', {
            username:      $('#artistName')?.value.trim(),
            email:         $('#artistEmail')?.value.trim(),
            password:      $('#artistPassword')?.value,
            portfolio_url: $('#portfolioLink')?.value.trim(),
        });

        btn.disabled = false;
        btn.textContent = 'Daftar Menjadi Artis';

        if (data.status === 'ok') {
            saveSession(data.user);
            closeModal(artistModal);
            applyLoginState();
            window.location.href = 'landing-artist.html';
        } else {
            showError(artistForm, data.message);
        }
    });

    // ── Submit: Masuk ──
    loginForm && loginForm.addEventListener('submit', async e => {
        e.preventDefault();
        clearError(loginForm);

        const btn = loginForm.querySelector('.btn-submit');
        btn.disabled = true;
        btn.textContent = 'Memproses...';

        const data = await authFetch('login', {
            username: $('#loginUsername')?.value.trim(),
            password: $('#loginPassword')?.value,
        });

        btn.disabled = false;
        btn.textContent = 'Masuk';

        if (data.status === 'ok') {
            saveSession(data.user);
            closeModal(loginModal);
            applyLoginState();
            if (data.user.role === 'admin')       window.location.href = 'admin.html';
            else if (data.user.role === 'artist') window.location.href = 'landing-artist.html';
            else                                  window.location.href = 'landing-reguler.html';
        } else {
            showError(loginForm, data.message);
        }
    });
})();


// ── 4. GUARD — cegah aksi tanpa login ────────────────────────

/**
 * Pasang guard ke elemen interaktif yang butuh login.
 * Jika belum login, klik akan membuka modal login, bukan menjalankan aksi.
 */
function guardInteractions() {
    const loginModal = $('#loginModal');
    if (!loginModal) return;

    const SELECTORS = [
        '.btn-follow', '.follow-btn',
        '#likePostBtn', '#savePostBtn',
        '#postBtn',     '#orderBtn',
        '#fabPostBtn',
    ];

    SELECTORS.forEach(sel => {
        $$(sel).forEach(el => {
            el.addEventListener('click', e => {
                if (!Store.get('userRole')) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    loginModal.classList.add('show');
                }
            }, true); // capture phase — jalan sebelum handler asli
        });
    });
}

document.addEventListener('DOMContentLoaded', guardInteractions);


// ── 5. LOGOUT ────────────────────────────────────────────────

(function initLogout() {
    const btnLogout = $('#btnLogout') || $('#ga-adm-logout-btn');
    if (!btnLogout) return;

    btnLogout.addEventListener('click', async () => {
        if (!confirm('Apakah Anda yakin ingin keluar?')) return;
        await authFetch('logout');
        clearSession();
        window.location.href = 'landing.html';
    });
})();