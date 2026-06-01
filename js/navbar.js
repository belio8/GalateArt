
'use strict';

// ── 1. MENU HAMBURGER ──────────────────────────────────────────
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


// ── 2. NOTIFIKASI DROPDOWN ─────────────────────────────────────
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


// ── 3. CART ICON ───────────────────────────────────────────────
(function initCartIcon() {
    $$('.nav-icons .fa-shopping-cart').forEach(icon => {
        icon.addEventListener('click', () => {
            window.location.href = 'cart.php';
        });
    });
})();


// ── 4. MESSAGE ICON ────────────────────────────────────────────
(function initMessageIcon() {
    $$('.nav-icons .fa-comment').forEach(icon => {
        icon.addEventListener('click', () => {
            window.location.href = 'messages.php';
        });
    });
})();


// ── 5. SEARCH BAR ──────────────────────────────────────────────
(function initSearchBar() {
    $$('.search-bar input').forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key !== 'Enter') return;
            const q = input.value.trim();
            if (q) window.location.href = `search-results.php?q=${encodeURIComponent(q)}`;
        });
    });
})();
