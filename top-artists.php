<?php
require_once __DIR__ . '/components/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Artist - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container">
        <section class="top-artists">
            <h1>Top Artist</h1>
            <p>Temukan artis terbaik di GalateArt dengan karya seni berkualitas tinggi.</p>
            
            <div class="artists-grid">
                <div class="artist-card">
                    <img src="Assets/draw2.png" alt="Artist 1" class="artist-avatar">
                    <div class="artist-details">
                        <h3>@artis_lokal</h3>
                        <p>Spesialis ilustrasi karakter dan desain VTuber. Telah menciptakan lebih dari 200 karya seni digital.</p>
                        <div class="artist-stats">
                            <span><strong>2.5K</strong> Followers</span>
                            <span><strong>150</strong> Posts</span>
                        </div>
                        <button class="btn-follow">Follow</button>
                    </div>
                </div>
                <div class="artist-card">
                    <img src="Assets/draw2.png" alt="Artist 2" class="artist-avatar">
                    <div class="artist-details">
                        <h3>@seniman_digital</h3>
                        <p>Artis fantasy dan concept art. Ahli dalam menciptakan dunia imajiner yang menakjubkan.</p>
                        <div class="artist-stats">
                            <span><strong>1.8K</strong> Followers</span>
                            <span><strong>120</strong> Posts</span>
                        </div>
                        <button class="btn-follow">Follow</button>
                    </div>
                </div>
                <div class="artist-card">
                    <img src="Assets/draw2.png" alt="Artist 3" class="artist-avatar">
                    <div class="artist-details">
                        <h3>@ichigowarano</h3>
                        <p>Pembuat karya seni anime dan manga style. Fokus pada ekspresi karakter yang mendalam.</p>
                        <div class="artist-stats">
                            <span><strong>3.2K</strong> Followers</span>
                            <span><strong>180</strong> Posts</span>
                        </div>
                        <button class="btn-follow">Follow</button>
                    </div>
                </div>
                <div class="artist-card">
                    <img src="Assets/draw2.png" alt="Artist 4" class="artist-avatar">
                    <div class="artist-details">
                        <h3>@keenbiscuit</h3>
                        <p>Artis landscape dan nature art. Mengabadikan keindahan alam dalam gaya digital yang unik.</p>
                        <div class="artist-stats">
                            <span><strong>1.5K</strong> Followers</span>
                            <span><strong>95</strong> Posts</span>
                        </div>
                        <button class="btn-follow">Follow</button>
                    </div>
                </div>
                <div class="artist-card">
                    <img src="Assets/draw2.png" alt="Artist 5" class="artist-avatar">
                    <div class="artist-details">
                        <h3>@jasper_xandros</h3>
                        <p>Spesialis portrait dan character design. Telah bekerja dengan berbagai klien internasional.</p>
                        <div class="artist-stats">
                            <span><strong>2.1K</strong> Followers</span>
                            <span><strong>140</strong> Posts</span>
                        </div>
                        <button class="btn-follow">Follow</button>
                    </div>
                </div>
                <!-- Tambahkan lebih banyak artist-card sesuai kebutuhan -->
            </div>
        </section>
    </main>
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/art-modal.js"></script>
    <script src="report-modal.js"></script>
</body>
</html>