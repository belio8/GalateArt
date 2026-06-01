<?php
require_once __DIR__ . '/components/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - GalateArt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <div class="ga-srch-page">
        <!-- Search Bar -->
        <div class="ga-srch-bar-large">
            <input type="text" id="mainSearchInput" placeholder="Cari karya seni, artis, atau tag..." value="vtuber">
            <button class="ga-srch-btn-go" onclick="doSearch(document.getElementById('mainSearchInput').value)">
                <i class="fas fa-search"></i> Cari
            </button>
        </div>

        <!-- Header -->
        <div class="ga-srch-header">
            <h1>Hasil untuk "<span id="queryLabel">vtuber</span>"</h1>
            <p class="ga-srch-meta" id="resultsMeta">Menampilkan 24 hasil Â· Artwork Â· Diurutkan: Relevansi</p>
        </div>

        <!-- Tabs -->
        <div class="ga-srch-tabs">
            <button class="ga-srch-tab ga-srch-active" onclick="switchTab(this,'tab-artwork')"><i class="fas fa-image"></i> Artwork</button>
            <button class="ga-srch-tab" onclick="switchTab(this,'tab-artist')"><i class="fas fa-user"></i> Artis</button>
            <button class="ga-srch-tab" onclick="switchTab(this,'tab-tag')"><i class="fas fa-hashtag"></i> Tag</button>
        </div>

        <!-- Filters -->
        <div class="ga-srch-filters">
            <span class="ga-srch-filter-label">Filter:</span>
            <button class="ga-srch-filter-chip ga-srch-active" onclick="toggleChip(this)">Semua</button>
            <button class="ga-srch-filter-chip" onclick="toggleChip(this)">Commission Open</button>
            <button class="ga-srch-filter-chip" onclick="toggleChip(this)">Free</button>
            <button class="ga-srch-filter-chip" onclick="toggleChip(this)">Character</button>
            <button class="ga-srch-filter-chip" onclick="toggleChip(this)">Fullbody</button>
            <select class="ga-srch-filter-select" onchange="updateSort(this.value)">
                <option value="relevance">Relevansi</option>
                <option value="newest">Terbaru</option>
                <option value="popular">Terpopuler</option>
                <option value="price_low">Harga Terendah</option>
            </select>
        </div>

        <div class="ga-srch-body">
            <div class="ga-srch-results-col">

                <!-- TAB: Artwork -->
                <div id="tab-artwork">
                    <div class="ga-srch-art-grid" id="artworkGrid">
                        <!-- Dynamically generated below -->
                    </div>
                    <div class="ga-srch-pagination">
                        <button class="ga-srch-page-btn ga-srch-active">1</button>
                        <button class="ga-srch-page-btn">2</button>
                        <button class="ga-srch-page-btn">3</button>
                        <button class="ga-srch-page-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <!-- TAB: Artist -->
                <div id="tab-artist" style="display:none;">
                    <div class="ga-srch-artist-result-list">
                        <div class="ga-srch-artist-result-card">
                            <img class="ga-srch-ar-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=ichigo" alt="">
                            <div class="ga-srch-ar-body">
                                <p class="ga-srch-ar-name">Ichigowarano</p>
                                <p class="ga-srch-ar-handle">@ichigowarano</p>
                                <p class="ga-srch-ar-bio">Spesialis VTuber design & anime illustration. Commission OPEN.</p>
                                <p class="ga-srch-ar-stats"><strong>3.2K</strong> followers Â· <strong>180</strong> posts</p>
                            </div>
                            <button class="ga-srch-btn-follow">Follow</button>
                        </div>
                        <div class="ga-srch-artist-result-card">
                            <img class="ga-srch-ar-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=jasper" alt="">
                            <div class="ga-srch-ar-body">
                                <p class="ga-srch-ar-name">Jasper Xandros</p>
                                <p class="ga-srch-ar-handle">@jasper_xandros</p>
                                <p class="ga-srch-ar-bio">Character & VTuber model design. Berbasis di Jakarta.</p>
                                <p class="ga-srch-ar-stats"><strong>2.1K</strong> followers Â· <strong>140</strong> posts</p>
                            </div>
                            <button class="ga-srch-btn-follow">Follow</button>
                        </div>
                        <div class="ga-srch-artist-result-card">
                            <img class="ga-srch-ar-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=artislokal" alt="">
                            <div class="ga-srch-ar-body">
                                <p class="ga-srch-ar-name">Artis Lokal</p>
                                <p class="ga-srch-ar-handle">@artis_lokal</p>
                                <p class="ga-srch-ar-bio">Ilustrasi VTuber, game asset, dan desain konseptual.</p>
                                <p class="ga-srch-ar-stats"><strong>2.5K</strong> followers Â· <strong>150</strong> posts</p>
                            </div>
                            <button class="ga-srch-btn-follow">Follow</button>
                        </div>
                    </div>
                </div>

                <!-- TAB: Tag -->
                <div id="tab-tag" style="display:none;">
                    <div class="ga-srch-tag-results">
                        <div class="ga-srch-tag-card"><div class="ga-srch-tag-name">#vtuber</div><div class="ga-srch-tag-count">1.2K postingan</div></div>
                        <div class="ga-srch-tag-card"><div class="ga-srch-tag-name">#vtuberfanart</div><div class="ga-srch-tag-count">842 postingan</div></div>
                        <div class="ga-srch-tag-card"><div class="ga-srch-tag-name">#vtuberdesign</div><div class="ga-srch-tag-count">610 postingan</div></div>
                        <div class="ga-srch-tag-card"><div class="ga-srch-tag-name">#vtuberart</div><div class="ga-srch-tag-count">509 postingan</div></div>
                        <div class="ga-srch-tag-card"><div class="ga-srch-tag-name">#vtuberlive2d</div><div class="ga-srch-tag-count">312 postingan</div></div>
                        <div class="ga-srch-tag-card"><div class="ga-srch-tag-name">#vtubercostume</div><div class="ga-srch-tag-count">198 postingan</div></div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="ga-srch-sidebar">
                <div class="ga-srch-sidebar-widget">
                    <h3><i class="fas fa-fire" style="color:var(--accent);margin-right:6px;"></i>Tag Populer</h3>
                    <div class="ga-srch-trending-tag-item"><span>#vtuber</span><span>1.2K posts</span></div>
                    <div class="ga-srch-trending-tag-item"><span>#digitalart</span><span>980 posts</span></div>
                    <div class="ga-srch-trending-tag-item"><span>#characterdesign</span><span>754 posts</span></div>
                    <div class="ga-srch-trending-tag-item"><span>#anime</span><span>620 posts</span></div>
                    <div class="ga-srch-trending-tag-item"><span>#illustration</span><span>512 posts</span></div>
                </div>

                <div class="ga-srch-sidebar-widget">
                    <h3><i class="fas fa-user-plus" style="color:var(--purple);margin-right:6px;"></i>Artis yang Mungkin Kamu Suka</h3>
                    <div class="ga-srch-suggested-artist">
                        <img class="ga-srch-sa-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=keen" alt="">
                        <div class="ga-srch-sa-info"><strong>Keenbiscuit</strong><span>@keenbiscuit</span></div>
                        <button class="ga-srch-btn-follow" style="font-size:11px;padding:5px 10px;">+Follow</button>
                    </div>
                    <div class="ga-srch-suggested-artist">
                        <img class="ga-srch-sa-avatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=seniman" alt="">
                        <div class="ga-srch-sa-info"><strong>Seniman Digital</strong><span>@seniman_digital</span></div>
                        <button class="ga-srch-btn-follow" style="font-size:11px;padding:5px 10px;">+Follow</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/art-modal.js"></script>
    <script>
        const artworkData = [
            { color:'8e54e9', label:'VTuber Design', artist:'@ichigowarano', tags:'#vtuber #originalcharacter', likes:320 },
            { color:'ff7e33', label:'Chibi VTuber', artist:'@jasper_xandros', tags:'#vtuber #chibi #cute', likes:215 },
            { color:'68a819', label:'Vtuber Fullbody', artist:'@artis_lokal', tags:'#vtuber #fullbody', likes:189 },
            { color:'e91e8c', label:'Stream Overlay', artist:'@seniman_digital', tags:'#vtuber #overlay #design', likes:144 },
            { color:'1e88e5', label:'Live2D Model', artist:'@ichigowarano', tags:'#vtuber #live2d', likes:278 },
            { color:'ffc107', label:'Avatar Bust', artist:'@keenbiscuit', tags:'#vtuber #bust #anime', likes:92 },
            { color:'9c27b0', label:'Scene Illustration', artist:'@jasper_xandros', tags:'#vtuber #scene', likes:163 },
            { color:'00bcd4', label:'Emote Pack', artist:'@artis_lokal', tags:'#vtuber #emote #twitch', likes:410 },
        ];

        function buildArtGrid() {
            const grid = document.getElementById('artworkGrid');
            grid.innerHTML = artworkData.map(a => `
                <div class="art-card" style="cursor:pointer;">
                    <img src="https://via.placeholder.com/300x380/${a.color}/ffffff?text=${encodeURIComponent(a.label)}" alt="${a.label}">
                    <div class="art-info">
                        <p class="hashtags">${a.tags}</p>
                        <p class="artist-name">${a.artist}</p>
                        <p style="font-size:12px;color:#b3b3b3;margin:4px 0 0;"><i class="fas fa-heart" style="color:#ff4d6a;margin-right:4px;"></i>${a.likes}</p>
                    </div>
                </div>`).join('');
        }

        function switchTab(btn, tabId) {
            document.querySelectorAll('.search-tab').forEach(b => b.classList.remove('ga-srch-active'));
            btn.classList.add('ga-srch-active');
            ['tab-artwork','tab-artist','tab-tag'].forEach(id => {
                document.getElementById(id).style.display = id === tabId ? '' : 'none';
            });
            const counts = { 'tab-artwork': '24 hasil', 'tab-artist': '3 artis', 'tab-tag': '6 tag' };
            document.getElementById('resultsMeta').textContent = `Menampilkan ${counts[tabId]} Â· "${document.getElementById('queryLabel').textContent}"`;
        }

        function toggleChip(btn) {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('ga-srch-active'));
            btn.classList.add('ga-srch-active');
        }

        function updateSort(val) {
            // Dummy: just re-render shuffled
            buildArtGrid();
        }

        function doSearch(q) {
            if (!q.trim()) return;
            document.getElementById('queryLabel').textContent = q;
            document.getElementById('mainSearchInput').value = q;
            document.getElementById('resultsMeta').textContent = `Menampilkan 24 hasil Â· Artwork Â· Diurutkan: Relevansi`;
            buildArtGrid();
        }

        // Init
        buildArtGrid();
    </script>
</body>
</html>