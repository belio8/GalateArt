'use strict';

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

(function guardGuestInteractions() {
    const protectedSelectors = [
        '.btn-follow',
        '.follow-btn',
        '#likePostBtn',
        '#savePostBtn',
        '#postBtn',
        '#orderBtn',
        '#fabPostBtn',
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
