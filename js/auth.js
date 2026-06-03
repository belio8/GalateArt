'use strict';

// ── Modal open/close logic ──────────────────────────────────────
(function initAuthModals() {
    const registerModal = document.getElementById('registerModal');
    const artistModal = document.getElementById('artistModal');
    const loginModal = document.getElementById('loginModal');

    const btnSignup = document.getElementById('btnSignup');
    const btnLogin = document.getElementById('btnLogin');
    const btnArtist = document.getElementById('btnArtist');
    const closeRegister = document.getElementById('closeModal');
    const closeArtist = document.getElementById('closeArtistModal');
    const closeLogin = document.getElementById('closeLoginModal');
    const switchToSignup = document.getElementById('switchToSignup');
    const openLoginFromSignup = document.getElementById('openLoginFromSignup');

    function openModal(modal) {
        if (modal) modal.classList.add('show');
    }

    function closeModal(modal) {
        if (modal) modal.classList.remove('show');
    }

    btnSignup && btnSignup.addEventListener('click', () => openModal(registerModal));
    btnLogin && btnLogin.addEventListener('click', () => openModal(loginModal));
    btnArtist && btnArtist.addEventListener('click', () => openModal(artistModal));

    closeRegister && closeRegister.addEventListener('click', () => closeModal(registerModal));
    closeArtist && closeArtist.addEventListener('click', () => closeModal(artistModal));
    closeLogin && closeLogin.addEventListener('click', () => closeModal(loginModal));

    switchToSignup && switchToSignup.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal(loginModal);
        openModal(registerModal);
    });

    openLoginFromSignup && openLoginFromSignup.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal(registerModal);
        openModal(loginModal);
    });

    window.addEventListener('click', (event) => {
        if (event.target === registerModal) closeModal(registerModal);
        if (event.target === artistModal) closeModal(artistModal);
        if (event.target === loginModal) closeModal(loginModal);
    });
})();

// ── Guard guest interactions ────────────────────────────────────
(function guardGuestInteractions() {
    const protectedSelectors = [
        '.btn-follow',
        '.follow-btn',
        '#likePostBtn',
        '#savePostBtn',
        '#postBtn',
        '#orderBtn',
    ];

    protectedSelectors.forEach((selector) => {
        document.querySelectorAll(selector).forEach((element) => {
            element.addEventListener('click', (event) => {
                const loginModal = document.getElementById('loginModal');
                if (!loginModal) return;

                event.preventDefault();
                event.stopImmediatePropagation();
                loginModal.classList.add('show');
            }, true);
        });
    });
})();

// ── Google Sign-In (Direct OAuth Redirect) ──────────────────────
// This completely bypasses the GIS library to avoid "origin mismatch" errors on localhost.
// When the button is clicked, we redirect the user to Google's consent screen.
(function initGoogleSignIn() {
    const GOOGLE_CLIENT_ID = '718726007331-6p772e3imvn502t2st9t5vapj6aq5mdg.apps.googleusercontent.com';
    
    // The redirect URI MUST match exactly what is in Google Cloud Console
    // Based on user's screenshot, it's: http://localhost/GalateArt/landing.php
    // Wait, the callback needs to handle the hash token. 
    // If the authorized redirect is exactly 'http://localhost/GalateArt/landing.php', 
    // then Google will redirect back there. 
    // Let's use landing.php as the callback and handle it there, OR update auth.js to handle the token if it's in the URL on ANY page.
    // Let's make landing.php the redirect_uri, but process the token globally in auth.js.

    // Global function called from onclick in the HTML buttons
    window.triggerGoogleLogin = function() {
        // Find the base URL dynamically based on current location (case-insensitive for GalateArt)
        const pathArray = window.location.pathname.split('/');
        const projectIndex = pathArray.findIndex(p => p.toLowerCase() === 'galateart');
        const appPath = projectIndex !== -1 
            ? pathArray.slice(0, projectIndex + 1).join('/') 
            : '/GalateArt'; // Fallback
        const redirectUri = window.location.origin + appPath + '/landing.php';

        const oauth2Endpoint = 'https://accounts.google.com/o/oauth2/v2/auth';
        
        // Parameters for OAuth 2.0 Implicit Flow
        const params = {
            client_id: GOOGLE_CLIENT_ID,
            redirect_uri: redirectUri,
            response_type: 'token',
            scope: 'email profile',
            include_granted_scopes: 'true',
            state: 'google_login'
        };
        
        // Build query string
        const queryString = Object.keys(params).map(key => key + '=' + encodeURIComponent(params[key])).join('&');
        
        // Redirect to Google
        window.location.href = `${oauth2Endpoint}?${queryString}`;
    };

    // Check if we just returned from Google OAuth (implicit flow puts token in hash)
    window.addEventListener('load', () => {
        const hash = window.location.hash.substring(1);
        if (hash.includes('access_token=') && hash.includes('state=google_login')) {
            const params = new URLSearchParams(hash);
            const accessToken = params.get('access_token');
            
            if (accessToken) {
                // Show loading state
                const allBtns = document.querySelectorAll('.btn-google');
                allBtns.forEach(btn => {
                    btn.disabled = true;
                    btn.style.opacity = '0.6';
                    btn.innerHTML = 'Memproses...';
                });
                
                // Clear the hash from URL so it doesn't stay there
                history.replaceState(null, null, ' ');

                // Send to backend
                fetch('api/google-auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ access_token: accessToken }),
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok' && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message || 'Login Google gagal.');
                        window.location.reload();
                    }
                })
                .catch(err => {
                    console.error('Network error:', err);
                    alert('Terjadi kesalahan jaringan saat verifikasi Google.');
                    window.location.reload();
                });
            }
        }
    });
})();
