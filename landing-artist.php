<?php
require_once __DIR__ . '/components/bootstrap.php';
require_once __DIR__ . '/config/Db.php';
require_login('artist');

$posts = db_query(
    "SELECT p.id, p.title, p.image_url, p.like_count, u.username,
     COALESCE(NULLIF(u.avatar_url, ''), CONCAT('https://api.dicebear.com/7.x/avataaars/svg?seed=', u.username)) AS avatar_url,
     COALESCE(GROUP_CONCAT(DISTINCT pt.tag ORDER BY pt.tag SEPARATOR ' '), '') AS tags
     FROM posts p
     JOIN users u ON u.id = p.artist_id
     LEFT JOIN post_tags pt ON pt.post_id = p.id
     WHERE p.status = ?
     GROUP BY p.id, p.title, p.image_url, u.username, u.avatar_url
     ORDER BY p.created_at DESC
     LIMIT 12",
    ['active']
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GalateArt - Koleksi Seni Lokal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<button id="fabPostBtn" title="Buat Postingan Baru" aria-label="Buat Postingan Baru">
    <i class="fas fa-plus"></i>
</button>


<div id="postModal" role="dialog" aria-modal="true" aria-labelledby="postModalTitle">
    <div class="pm-box">

        <!-- Header -->
        <div class="pm-header">
            <h2 id="postModalTitle"><i class="fas fa-image" style="margin-right:8px;"></i>Buat Postingan</h2>
            <button class="pm-close-btn" id="pmCloseBtn" aria-label="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Upload Gambar -->
        <div class="pm-field">
            <label>Gambar Karya <span class="pm-required">*</span></label>
            <div class="pm-upload-area" id="pmUploadArea">
                <input type="file" id="pmImageInput" accept="image/*" aria-label="Pilih gambar">
                <i class="fas fa-cloud-upload-alt pm-upload-icon" id="pmUploadIcon"></i>
                <p class="pm-upload-label" id="pmUploadText">
                    <span>Pilih gambar</span> atau seret & lepas di sini<br>
                    <small style="color:#555566;">PNG, JPG, WEBP - maks. 10 MB</small>
                </p>
                <img class="pm-preview-img" id="pmPreviewImg" alt="Preview">
                <p class="pm-preview-info" id="pmPreviewInfo"></p>
            </div>
        </div>

        <!-- Judul -->
        <div class="pm-field">
            <label for="pmTitle">Judul Karya <span class="pm-required">*</span></label>
            <input type="text" id="pmTitle" placeholder="Contoh: Character Design - Sakura Spirit" maxlength="80">
        </div>

        <!-- Deskripsi -->
        <div class="pm-field">
            <label for="pmDesc">Deskripsi</label>
            <textarea id="pmDesc" placeholder="Ceritakan tentang karya ini, proses pembuatan, tool yang digunakan, dsb." rows="3"></textarea>
        </div>

        <!-- Hashtag -->
        <div class="pm-field">
            <label for="pmTags">Hashtag <span class="pm-required">*</span></label>
            <input type="text" id="pmTags" placeholder="#illustration #originalart #digitalpainting">
            <small style="font-size:11px;color:#555566;margin-top:4px;display:block;">Pisahkan dengan spasi. Contoh: #anime #vtuber #fanart</small>
        </div>

        <!-- Harga -->
        <div class="pm-field">
            <label>Harga</label>
            <div class="pm-price-row">
                <input type="number" id="pmPrice" placeholder="0" min="0" step="1000">
                <label class="pm-free-toggle" for="pmFreeCheck">
                    <input type="checkbox" id="pmFreeCheck"> Gratis / Free Download
                </label>
            </div>
            <small style="font-size:11px;color:#555566;margin-top:4px;display:block;">Kosongkan atau centang "Gratis" jika karya ini tidak dijual.</small>
        </div>

        <!-- Filter NSFW -->
        <div class="pm-nsfw-row">
            <i class="fas fa-shield-alt pm-nsfw-icon"></i>
            <div class="pm-nsfw-text">
                <strong>Konten 18+ / NSFW</strong>
                <p>Aktifkan jika postingan ini mengandung konten dewasa. Karya akan disensor otomatis bagi pengguna yang tidak mengaktifkan filter NSFW.</p>
            </div>
            <label class="pm-switch" aria-label="Toggle NSFW">
                <input type="checkbox" id="pmNsfwToggle">
                <span class="pm-switch-slider"></span>
            </label>
        </div>

        <!-- Footer / Tombol -->
        <div class="pm-footer">
            <button class="pm-btn-cancel" id="pmCancelBtn">Batal</button>
            <button class="pm-btn-post" id="pmSubmitBtn">
                <i class="fas fa-paper-plane"></i> Post Sekarang
            </button>
        </div>

    </div>
</div>


<div id="pmToast" role="alert" aria-live="polite">
    <i class="fas fa-check-circle"></i>
    <span id="pmToastMsg">Postingan berhasil diunggah!</span>
</div>



    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container">
                <section class="hero-grid">
            <div class="hero-card main-card" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('https://via.placeholder.com/600x400');">
                <div class="hero-text">
                    <img src="Assets/draw2.png">
                    <h2>Dibuat oleh artis lokal</h2>
                    <p>Semua yang Anda butuhkan untuk vtuber, game, dan aset digital ada di sini!</p>
                </div>
            </div>
            <div class="hero-card ai-card" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('https://via.placeholder.com/600x400');">
                <div class="hero-text">
                    <img src="Assets/draw2.png">
                    <h2>Tanpa Generative AI</h2>
                    <p>Kami menghargai orisinalitas. Karya tanpa persetujuan dan kredit tidak diterima.</p>
                </div>
            </div>
            <div class="hero-card talent-card" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('https://via.placeholder.com/600x400');">
                <div class="hero-text">
                    <img src="Assets/draw2.png">
                    <h2>Temukan Bakat</h2>
                    <p>Temukan artis dengan gaya seni dan budget yang cocok untuk Anda.</p>
                </div>
            </div>
        </section>

        <section class="art-grid">
            <?php foreach ($posts as $post): ?>
                <?php
                    $image = $post['image_url'] ?: 'Assets/draw2.png';
                    $tags = trim((string) ($post['tags'] ?? ''));
                    $hashtags = $tags ? '#' . str_replace(' ', ' #', $tags) : '#original #illustration';
                ?>
                <div class="art-card" style="cursor: pointer;"
                     data-post-id="<?= e($post['id']) ?>"
                     data-img="<?= e($image) ?>"
                     data-artist="@<?= e($post['username']) ?>"
                     data-avatar-url="<?= e($post['avatar_url']) ?>"
                     data-tags="<?= e($hashtags) ?>"
                     data-likes="<?= (int)$post['like_count'] ?>">
                    <img src="<?= e($image) ?>" alt="<?= e($post['title']) ?>">
                    <div class="art-info">
                        <p class="hashtags"><?= e($hashtags) ?></p>
                        <p class="artist-name">@<?= e($post['username']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <?php include __DIR__ . '/components/art-modal.php'; ?>

    </main>
    <!-- Core utilities (wajib dimuat pertama) -->
    <script src="js/utils.js"></script>
    <script src="js/post.js?v=<?= time() ?>"></script>
    <!-- Komponen navbar: hamburger, notifikasi, cart, pesan, search -->
    <script src="js/navbar.js?v=<?= time() ?>"></script>
    <!-- Autentikasi: login state, modal daftar/masuk/artis -->
    <script src="js/auth.js?v=<?= time() ?>"></script>
    <!-- Modal popup karya seni + follow button -->
    <script src="js/art-modal.js?v=<?= time() ?>"></script>
    <script src="js/report-modal.js?v=<?= time() ?>"></script>
</body>
</html>