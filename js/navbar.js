
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
async function fetchAndRenderNotifications() {
    const body = $('#notifBody');
    if (!body) return;

    try {
        const res  = await fetch('api/notifications.php');
        const data = await res.json();

        if (data.status !== 'ok' || !data.notifications || data.notifications.length === 0) {
            body.innerHTML = `
                <div style="padding:30px;text-align:center;color:#b3b3b3;">
                    <i class="far fa-bell-slash" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                    <p>Tidak ada notifikasi</p>
                </div>`;
            return;
        }

        body.innerHTML = data.notifications.map(n =>
            `<div class="notif-item"><p>${n.text}</p><span>${escapeHtml(n.time)}</span></div>`
        ).join('');

        // Update badge unread count jika ada
        const badge = $('#notifBadge');
        if (badge) {
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        }
    } catch (err) {
        console.error('Gagal memuat notifikasi:', err);
        body.innerHTML = `
            <div style="padding:30px;text-align:center;color:#b3b3b3;">
                <i class="far fa-bell-slash" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                <p>Tidak ada notifikasi</p>
            </div>`;
    }
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

    fetchAndRenderNotifications();
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
