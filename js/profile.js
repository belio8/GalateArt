
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

// Inisialisasi tidak lagi diperlukan karena di-handle oleh PHP template.
