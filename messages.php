<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login();
require_once __DIR__ . '/config/Db.php';

$me_id       = $_SESSION['user_id'];
$me_username = $_SESSION['username'] ?? '';
$me_avatar   = 'Assets/galateart_icon.png';

// Ambil avatar asli kalau ada
$me_row = db_row($conn, "SELECT avatar_url FROM users WHERE id = ? LIMIT 1", "s", [$me_id]);
if (!empty($me_row['avatar_url'])) {
    $me_avatar = $me_row['avatar_url'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan - GalateArt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <div class="ga-msg-page">
        <!-- Sidebar Conversations -->
        <div class="ga-msg-sidebar">
            <div class="ga-msg-sidebar-top">
                <h2><i class="far fa-comment" style="color:var(--accent);margin-right:8px;"></i>Pesan</h2>
                <button class="ga-msg-new-chat-btn" onclick="openNewChatModal()" title="Percakapan Baru">
                    <i class="fas fa-edit"></i>
                </button>
            </div>
            <input class="ga-msg-search" type="text" placeholder="Cari percakapan..." oninput="filterConvs(this.value)">
            <div class="ga-msg-conv-list" id="convList">
                <!-- Diisi oleh JavaScript -->
                <div class="ga-msg-loading" id="convLoading">
                    <i class="fas fa-spinner fa-spin"></i> Memuat percakapan...
                </div>
            </div>
        </div>

        <!-- Chat Panel -->
        <div class="ga-msg-chat" id="chatPanel">
            <!-- Empty state default -->
            <div class="ga-msg-empty-state" id="emptyState">
                <i class="far fa-comments"></i>
                <h3>Pilih Percakapan</h3>
                <p>Pilih kontak di sebelah kiri atau mulai percakapan baru untuk memulai obrolan.</p>
                <button class="ga-msg-start-chat-btn" onclick="openNewChatModal()">
                    <i class="fas fa-plus"></i> Percakapan Baru
                </button>
            </div>

            <!-- Chat header (hidden by default) -->
            <div class="ga-msg-chat-header" id="chatHeader" style="display:none;">
                <button class="ga-msg-back-btn" onclick="closeChat()"><i class="fas fa-arrow-left"></i></button>
                <img src="" alt="Avatar" id="chatAvatar">
                <div class="ga-msg-chat-header-info">
                    <strong id="chatName"></strong>
                    <span class="ga-msg-chat-status" id="chatStatus"></span>
                </div>
            </div>

            <!-- Messages area (hidden by default) -->
            <div class="ga-msg-chat-messages" id="chatMessages" style="display:none;"></div>

            <!-- Input bar (hidden by default) -->
            <div class="ga-msg-chat-input-bar" id="chatInputBar" style="display:none;">
                <input class="ga-msg-chat-text-input" type="text" id="chatInput"
                       placeholder="Tulis pesan..." onkeydown="if(event.key==='Enter') sendMessage()">
                <button class="ga-msg-btn-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <!-- Modal: Percakapan Baru -->
    <div class="ga-msg-modal-overlay" id="newChatModal" style="display:none;" onclick="closeNewChatModal(event)">
        <div class="ga-msg-modal" onclick="event.stopPropagation()">
            <div class="ga-msg-modal-header">
                <h3><i class="fas fa-plus-circle" style="color:var(--accent);margin-right:8px;"></i>Percakapan Baru</h3>
                <button class="ga-msg-modal-close" onclick="closeNewChatModal()">&times;</button>
            </div>
            <input class="ga-msg-modal-search" type="text" id="userSearchInput"
                   placeholder="Cari username..." oninput="searchUsers(this.value)">
            <div class="ga-msg-modal-results" id="userSearchResults">
                <p class="ga-msg-modal-hint">Ketik username untuk mencari pengguna.</p>
            </div>
        </div>
    </div>

    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script>
    // ── State ────────────────────────────────────────────────
    const ME_ID     = '<?= e($me_id) ?>';
    const ME_AVATAR = '<?= e($me_avatar) ?>';
    let activePartnerId = null;
    let pollInterval    = null;
    let convPollInterval = null;
    let searchTimeout   = null;

    // ── Init ─────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        loadConversations();
        // Polling daftar percakapan setiap 5 detik
        convPollInterval = setInterval(loadConversations, 5000);
    });

    // ── API helper ───────────────────────────────────────────
    async function api(action, params = {}, method = 'GET') {
        let url = `api/chat.php?action=${action}`;
        let opts = { method, headers: {} };

        if (method === 'GET') {
            const qs = new URLSearchParams(params).toString();
            if (qs) url += '&' + qs;
        } else {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify({ action, ...params });
        }

        const res = await fetch(url, opts);
        return res.json();
    }

    // ============================================================
    //  SIDEBAR — Daftar Percakapan
    // ============================================================
    async function loadConversations() {
        const data = await api('conversations');
        const list = document.getElementById('convList');
        const loading = document.getElementById('convLoading');

        if (data.status !== 'ok') return;
        if (loading) loading.remove();

        const conversations = data.conversations || [];

        if (conversations.length === 0) {
            list.innerHTML = `
                <div class="ga-msg-conv-empty">
                    <i class="far fa-comment-dots"></i>
                    <p>Belum ada percakapan.</p>
                    <p class="ga-msg-conv-empty-sub">Mulai percakapan baru dengan tombol <i class="fas fa-edit"></i> di atas.</p>
                </div>`;
            return;
        }

        // Simpan scroll position
        const prev = list.scrollTop;

        list.innerHTML = conversations.map(c => `
            <div class="ga-msg-conv-item ${c.partner_id === activePartnerId ? 'ga-msg-active' : ''}"
                 onclick="openChat('${c.partner_id}')" data-partner="${c.partner_id}">
                <img class="ga-msg-conv-avatar" src="${escHtml(c.avatar_url)}" alt="${escHtml(c.username)}">
                <div class="ga-msg-conv-body">
                    <p class="ga-msg-conv-name">${escHtml(c.username)}</p>
                    <p class="ga-msg-conv-preview">${c.last_sender_id === ME_ID ? '<span class="ga-msg-you-label">Anda: </span>' : ''}${escHtml(c.last_message)}</p>
                </div>
                <div class="ga-msg-conv-meta">
                    <span class="ga-msg-conv-time">${escHtml(c.last_time_formatted)}</span>
                    ${c.unread_count > 0 ? `<span class="ga-msg-conv-unread">${c.unread_count}</span>` : ''}
                </div>
            </div>
        `).join('');

        list.scrollTop = prev;
    }

    // ============================================================
    //  CHAT — Buka percakapan
    // ============================================================
    async function openChat(partnerId) {
        activePartnerId = partnerId;

        // Tampilkan panel chat, sembunyikan empty state
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('chatHeader').style.display = '';
        document.getElementById('chatMessages').style.display = '';
        document.getElementById('chatInputBar').style.display = '';

        // Highlight sidebar
        document.querySelectorAll('.ga-msg-conv-item').forEach(el => {
            el.classList.toggle('ga-msg-active', el.dataset.partner === partnerId);
        });

        // Loading state
        const feed = document.getElementById('chatMessages');
        feed.innerHTML = '<div class="ga-msg-loading"><i class="fas fa-spinner fa-spin"></i> Memuat pesan...</div>';

        const data = await api('history', { partner_id: partnerId });

        if (data.status !== 'ok') {
            feed.innerHTML = '<div class="ga-msg-loading">Gagal memuat pesan.</div>';
            return;
        }

        // Update header
        document.getElementById('chatAvatar').src = data.partner.avatar_url;
        document.getElementById('chatName').textContent = data.partner.username;
        document.getElementById('chatStatus').innerHTML = '';

        // Render messages
        renderMessages(data.messages, data.partner);

        // Scroll ke bawah
        feed.scrollTop = feed.scrollHeight;

        // Focus input
        document.getElementById('chatInput').focus();

        // Mulai polling chat setiap 3 detik
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(() => refreshChat(partnerId), 3000);

        // Refresh sidebar untuk update unread
        loadConversations();
    }

    function renderMessages(messages, partner) {
        const feed = document.getElementById('chatMessages');
        let html = '';
        let lastDate = '';

        messages.forEach(msg => {
            // Date divider
            if (msg.date_formatted !== lastDate) {
                html += `<div class="ga-msg-date-divider">${escHtml(msg.date_formatted)}</div>`;
                lastDate = msg.date_formatted;
            }

            const isMe = msg.is_me;
            const avatar = isMe ? ME_AVATAR : partner.avatar_url;

            html += `
                <div class="ga-msg-bubble-row ${isMe ? 'ga-msg-me' : ''}">
                    <img class="ga-msg-bubble-av" src="${escHtml(avatar)}" alt="">
                    <div>
                        <div class="ga-msg-bubble ${isMe ? 'ga-msg-me' : 'ga-msg-them'}">${escHtml(msg.content)}</div>
                        <div class="ga-msg-bubble-time" ${isMe ? 'style="text-align:right;"' : ''}>${escHtml(msg.time_formatted)}</div>
                    </div>
                </div>`;
        });

        if (messages.length === 0) {
            html = `
                <div class="ga-msg-chat-empty">
                    <i class="far fa-hand-peace"></i>
                    <p>Belum ada pesan. Mulai percakapan!</p>
                </div>`;
        }

        feed.innerHTML = html;
    }

    async function refreshChat(partnerId) {
        if (partnerId !== activePartnerId) return;

        const data = await api('history', { partner_id: partnerId });
        if (data.status !== 'ok') return;

        const feed = document.getElementById('chatMessages');
        const wasAtBottom = (feed.scrollHeight - feed.scrollTop - feed.clientHeight) < 60;

        renderMessages(data.messages, data.partner);

        if (wasAtBottom) {
            feed.scrollTop = feed.scrollHeight;
        }
    }

    // ============================================================
    //  SEND — Kirim pesan
    // ============================================================
    async function sendMessage() {
        const input = document.getElementById('chatInput');
        const text = input.value.trim();
        if (!text || !activePartnerId) return;

        input.value = '';
        input.focus();

        // Optimistic UI: langsung tampilkan pesan di chat
        const feed = document.getElementById('chatMessages');

        // Hapus empty state kalau ada
        const emptyChat = feed.querySelector('.ga-msg-chat-empty');
        if (emptyChat) emptyChat.remove();

        const now = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const row = document.createElement('div');
        row.className = 'ga-msg-bubble-row ga-msg-me';
        row.innerHTML = `
            <img class="ga-msg-bubble-av" src="${escHtml(ME_AVATAR)}" alt="">
            <div>
                <div class="ga-msg-bubble ga-msg-me">${escHtml(text)}</div>
                <div class="ga-msg-bubble-time" style="text-align:right;">${now}</div>
            </div>`;
        feed.appendChild(row);
        feed.scrollTop = feed.scrollHeight;

        // Kirim ke server
        const data = await api('send', {
            receiver_id: activePartnerId,
            content: text,
        }, 'POST');

        if (data.status !== 'ok') {
            row.querySelector('.ga-msg-bubble').style.opacity = '0.5';
            row.querySelector('.ga-msg-bubble').textContent += ' (gagal terkirim)';
        }

        // Refresh sidebar
        loadConversations();
    }

    // ============================================================
    //  FILTER — Pencarian sidebar
    // ============================================================
    function filterConvs(q) {
        const items = document.querySelectorAll('.ga-msg-conv-item');
        const query = q.toLowerCase();
        items.forEach(item => {
            const name = item.querySelector('.ga-msg-conv-name')?.textContent.toLowerCase() || '';
            item.style.display = name.includes(query) ? '' : 'none';
        });
    }

    // ============================================================
    //  NEW CHAT MODAL
    // ============================================================
    function openNewChatModal() {
        document.getElementById('newChatModal').style.display = 'flex';
        document.getElementById('userSearchInput').value = '';
        document.getElementById('userSearchResults').innerHTML =
            '<p class="ga-msg-modal-hint">Ketik username untuk mencari pengguna.</p>';
        setTimeout(() => document.getElementById('userSearchInput').focus(), 100);
    }

    function closeNewChatModal(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('newChatModal').style.display = 'none';
    }

    async function searchUsers(q) {
        const results = document.getElementById('userSearchResults');

        if (q.trim().length < 1) {
            results.innerHTML = '<p class="ga-msg-modal-hint">Ketik username untuk mencari pengguna.</p>';
            return;
        }

        // Debounce
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            results.innerHTML = '<div class="ga-msg-loading"><i class="fas fa-spinner fa-spin"></i> Mencari...</div>';

            const data = await api('users', { q: q.trim() });

            if (data.status !== 'ok' || !data.users.length) {
                results.innerHTML = '<p class="ga-msg-modal-hint">Tidak ada pengguna ditemukan.</p>';
                return;
            }

            results.innerHTML = data.users.map(u => `
                <div class="ga-msg-user-item" onclick="startChatWith('${u.id}')">
                    <img src="${escHtml(u.avatar_url)}" alt="${escHtml(u.username)}">
                    <div>
                        <strong>${escHtml(u.username)}</strong>
                        <span class="ga-msg-user-role">${escHtml(u.role)}</span>
                    </div>
                </div>
            `).join('');
        }, 300);
    }

    function startChatWith(userId) {
        document.getElementById('newChatModal').style.display = 'none';
        openChat(userId);
    }

    // ============================================================
    //  MOBILE — Kembali ke sidebar
    // ============================================================
    function closeChat() {
        activePartnerId = null;
        if (pollInterval) clearInterval(pollInterval);

        document.getElementById('emptyState').style.display = '';
        document.getElementById('chatHeader').style.display = 'none';
        document.getElementById('chatMessages').style.display = 'none';
        document.getElementById('chatInputBar').style.display = 'none';

        document.querySelectorAll('.ga-msg-conv-item').forEach(el => {
            el.classList.remove('ga-msg-active');
        });
    }

    // ── Utility ──────────────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }
    </script>
</body>
</html>