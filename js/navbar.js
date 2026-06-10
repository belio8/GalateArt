
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

        body.innerHTML = data.notifications.map(n => {
            let actionsHtml = '';
            if (n.type === 'commission' && n.order_status === 'pending') {
                actionsHtml = `
                <div style="margin-top:10px; display:flex; gap:10px;">
                    <button onclick="updateOrderStatus('${n.ref_id}', 'confirmed')" style="flex:1; background:#22c55e; color:white; border:none; padding:5px 10px; border-radius:6px; cursor:pointer; font-size:12px;">Terima</button>
                    <button onclick="updateOrderStatus('${n.ref_id}', 'cancelled')" style="flex:1; background:#ef4444; color:white; border:none; padding:5px 10px; border-radius:6px; cursor:pointer; font-size:12px;">Tolak</button>
                </div>`;
            } else if (n.type === 'commission' && n.order_status === 'confirmed') {
                actionsHtml = `<div style="margin-top:10px; font-size:12px; color:#22c55e;"><i class="fas fa-check"></i> Diterima</div>`;
            } else if (n.type === 'commission' && n.order_status === 'cancelled') {
                actionsHtml = `<div style="margin-top:10px; font-size:12px; color:#ef4444;"><i class="fas fa-times"></i> Ditolak</div>`;
            }

            return `<div class="notif-item"><p>${escapeHtml(n.text)}</p><span>${escapeHtml(n.time)}</span>${actionsHtml}</div>`;
        }).join('');

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

window.updateOrderStatus = async function(orderId, status) {
    if (!confirm(status === 'confirmed' ? 'Terima order commission ini?' : 'Tolak order commission ini?')) return;
    try {
        const res = await fetch('api/update-order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, status: status })
        });
        const data = await res.json();
        if (data.status === 'ok') {
            fetchAndRenderNotifications(); // Refresh notifications
            alert(data.message);
        } else {
            alert(data.message || 'Gagal mengubah status order.');
        }
    } catch(e) {
        alert('Terjadi kesalahan jaringan.');
    }
};

(function initNotifDropdown() {
    const toggle   = $('#notifToggle');
    const dropdown = $('#notifDropdown');
    const menuDD   = $('#dropdownMenu');
    if (!toggle || !dropdown) return;

    toggle.addEventListener('click', async e => {
        const isOpening = !dropdown.classList.contains('show');
        dropdown.classList.toggle('show');
        if (menuDD) menuDD.classList.remove('show');
        e.stopPropagation();

        if (isOpening) {
            const badge = $('#notifBadge');
            if (badge && badge.style.display !== 'none') {
                badge.style.display = 'none'; // Sembunyikan badge
                try {
                    await fetch('api/mark-notifications-read.php', { method: 'POST' });
                } catch (err) {
                    console.error('Gagal mark as read:', err);
                }
            }
        }
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
