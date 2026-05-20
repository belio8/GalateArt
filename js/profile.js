/* ============================================================
   GalateArt — profile.js
   Fungsionalitas halaman profil (profile.html):
     - Tab switcher (Bio / Posts / Saved / Liked)
     - Badge role (Artist Account / Regular Account)
   Dependensi: utils.js, auth.js
   ============================================================ */

'use strict';

// ── TAB SWITCHER ───────────────────────────────────────────────
/**
 * Ganti tab aktif.
 * Dipanggil dari atribut onclick di HTML: switchTab(this, 'content-bio')
 */
function switchTab(clickedBtn, targetId) {
    $$('.tab-btn').forEach(b => b.classList.remove('active'));
    $$('.tab-content').forEach(c => c.classList.remove('active'));
    clickedBtn.classList.add('active');
    const target = $('#' + targetId);
    if (target) target.classList.add('active');
}


// ── BADGE ROLE & INISIALISASI ──────────────────────────────────
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

        // Regular: tab Posts tersembunyi via CSS — paksa ke Bio
        const bioBtn = $('.profile-tabs button:first-child');
        if (bioBtn) switchTab(bioBtn, 'content-bio');
    }
})();