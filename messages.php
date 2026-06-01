<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login();
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
                <input class="ga-msg-search" type="text" placeholder="Cari percakapan..." oninput="filterConvs(this.value)">
            </div>
            <div class="ga-msg-conv-list" id="convList">
                <div class="ga-msg-conv-item ga-msg-active" onclick="openChat('ichigo')">
                    <img class="ga-msg-conv-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=ichigo" alt="Ichigo">
                    <div class="ga-msg-conv-body">
                        <p class="ga-msg-conv-name">Ichigowarano</p>
                        <p class="ga-msg-conv-preview" id="preview-ichigo">Siap! Saya akan mulai sketsa besok ya ðŸ˜Š</p>
                    </div>
                    <div class="ga-msg-conv-meta">
                        <span class="ga-msg-conv-time">14:32</span>
                    </div>
                </div>
                <div class="ga-msg-conv-item" onclick="openChat('jasper')">
                    <img class="ga-msg-conv-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=jasper" alt="Jasper">
                    <div class="ga-msg-conv-body">
                        <p class="ga-msg-conv-name">Jasper Xandros</p>
                        <p class="ga-msg-conv-preview">Bisa kirimkan referensi warna yang diinginkan?</p>
                    </div>
                    <div class="ga-msg-conv-meta">
                        <span class="ga-msg-conv-time">Kemarin</span>
                        <span class="ga-msg-conv-unread">2</span>
                    </div>
                </div>
                <div class="ga-msg-conv-item" onclick="openChat('keen')">
                    <img class="ga-msg-conv-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=keen" alt="Keen">
                    <div class="ga-msg-conv-body">
                        <p class="ga-msg-conv-name">Keenbiscuit</p>
                        <p class="ga-msg-conv-preview">Commission selesai! Cek DM untuk file-nya.</p>
                    </div>
                    <div class="ga-msg-conv-meta">
                        <span class="ga-msg-conv-time">Sen</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Panel -->
        <div class="ga-msg-chat" id="chatPanel">
            <div class="ga-msg-chat-header">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=ichigo" alt="Avatar" id="chatAvatar">
                <div class="ga-msg-chat-header-info">
                    <strong id="chatName">Ichigowarano</strong>
                    <span class="ga-msg-chat-status"><i class="fas fa-circle" style="font-size:8px;"></i> Online</span>
                </div>
                <div class="ga-msg-chat-actions">
                    <i class="fas fa-info-circle" title="Detail Commission"></i>
                    <i class="fas fa-ellipsis-v"></i>
                </div>
            </div>

            <div class="ga-msg-chat-messages" id="chatMessages">
                <div class="ga-msg-date-divider">Senin, 5 Mei 2026</div>

                <!-- Commission card (them) -->
                <div class="ga-msg-bubble-row">
                    <img class="ga-msg-bubble-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=ichigo" alt="">
                    <div>
                        <div class="ga-msg-commission-card">
                            <div class="ga-msg-cc-label"><i class="fas fa-file-contract"></i> REQUEST COMMISSION</div>
                            <div class="ga-msg-cc-title">Ilustrasi Karakter Fullbody</div>
                            <div class="ga-msg-cc-detail">Karakter original Â· Fullbody Â· Warna penuh</div>
                            <div class="ga-msg-cc-price">Rp 350.000</div>
                            <button class="ga-msg-btn-cc-action">Lihat Detail Order</button>
                        </div>
                        <div class="ga-msg-bubble-time">09:01</div>
                    </div>
                </div>

                <div class="ga-msg-bubble-row">
                    <img class="ga-msg-bubble-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=ichigo" alt="">
                    <div>
                        <div class="ga-msg-bubble ga-msg-them">Halo! Terima kasih sudah memesan. Bisa ceritakan lebih lanjut karakter yang ingin digambar? ðŸ˜Š</div>
                        <div class="ga-msg-bubble-time">09:03</div>
                    </div>
                </div>

                <div class="ga-msg-bubble-row ga-msg-me">
                    <img class="ga-msg-bubble-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=me" alt="">
                    <div>
                        <div class="ga-msg-bubble ga-msg-me">Halo kak! Saya mau buat karakter cewek dengan rambut biru panjang, pakai baju casual modern. Referensinya ada di bawah ini ya!</div>
                        <div class="ga-msg-bubble-time" style="text-align:right;">09:15</div>
                    </div>
                </div>

                <div class="ga-msg-bubble-row ga-msg-me">
                    <img class="ga-msg-bubble-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=me" alt="">
                    <div>
                        <img class="ga-msg-bubble-img" src="https://via.placeholder.com/220x280/8e54e9/ffffff?text=Referensi" alt="Ref">
                        <div class="ga-msg-bubble-time" style="text-align:right;">09:16</div>
                    </div>
                </div>

                <div class="ga-msg-bubble-row">
                    <img class="ga-msg-bubble-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=ichigo" alt="">
                    <div>
                        <div class="ga-msg-bubble ga-msg-them">Wah, keren banget referensinya! Nanti saya buat sketsa dulu ya, sekitar 2-3 hari. Nanti saya kabari via chat ini kalau sudah siap untuk review. ðŸŽ¨</div>
                        <div class="ga-msg-bubble-time">09:30</div>
                    </div>
                </div>

                <div class="ga-msg-date-divider">Hari ini</div>

                <div class="ga-msg-bubble-row">
                    <img class="ga-msg-bubble-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=ichigo" alt="">
                    <div>
                        <div class="ga-msg-bubble ga-msg-them">Siap! Saya akan mulai sketsa besok ya ðŸ˜Š</div>
                        <div class="ga-msg-bubble-time">14:32</div>
                    </div>
                </div>
            </div>

            <div class="ga-msg-chat-input-bar">
                <i class="fas fa-paperclip" title="Lampirkan file"></i>
                <i class="fas fa-image" title="Kirim gambar"></i>
                <input class="ga-msg-chat-text-input" type="text" id="chatInput" placeholder="Tulis pesan..." onkeydown="if(event.key==='Enter') sendMessage()">
                <button class="ga-msg-btn-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script>
        const conversations = {
            ichigo: {
                name: 'Ichigowarano',
                seed: 'ichigo',
                status: 'Online'
            },
            jasper: {
                name: 'Jasper Xandros',
                seed: 'jasper',
                status: 'Aktif 1j lalu'
            },
            keen: {
                name: 'Keenbiscuit',
                seed: 'keen',
                status: 'Aktif kemarin'
            }
        };

        function openChat(key) {
            document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('ga-msg-active'));
            event.currentTarget.classList.add('ga-msg-active');
            const c = conversations[key];
            document.getElementById('chatAvatar').src = `https://api.dicebear.com/7.x/avataaars/svg?seed=${c.seed}`;
            document.getElementById('chatName').textContent = c.name;
            document.querySelector('.chat-status').innerHTML = `<i class="fas fa-circle" style="font-size:8px;color:${c.status==='Online'?'#4caf50':'#888'}"></i> ${c.status}`;
        }

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const text = input.value.trim();
            if (!text) return;

            const feed = document.getElementById('chatMessages');
            const row = document.createElement('div');
            row.className = 'ga-msg-bubble-row ga-msg-me';
            row.innerHTML = `
                <img class="ga-msg-bubble-av" src="https://api.dicebear.com/7.x/avataaars/svg?seed=me" alt="">
                <div>
                    <div class="ga-msg-bubble ga-msg-me">${text}</div>
                    <div class="ga-msg-bubble-time" style="text-align:right;">${new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})}</div>
                </div>`;
            feed.appendChild(row);
            feed.scrollTop = feed.scrollHeight;
            input.value = '';

            // Update preview
            document.getElementById('preview-ichigo').textContent = text;
        }

        function filterConvs(q) {
            document.querySelectorAll('.conv-item').forEach(item => {
                const name = item.querySelector('.conv-name').textContent.toLowerCase();
                item.style.display = name.includes(q.toLowerCase()) ? '' : 'none';
            });
        }
    </script>
</body>
</html>