<?php
require_once __DIR__ . '/components/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Artis - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container">
        <section class="artist-profile">
            <div class="profile-header">
                <img src="Assets/draw2.png" alt="Artist Avatar" class="artist-avatar">
                <div class="artist-info">
                    <h1>@artis_lokal</h1>
                    <p class="artist-bio">Artis lokal yang menciptakan karya seni digital untuk VTuber, game, dan aset kreatif. Spesialis dalam ilustrasi karakter dan desain konseptual.</p>
                    <div class="artist-stats">
                        <span><strong>150</strong> Postingan</span>
                        <span><strong>2.5K</strong> Followers</span>
                        <span><strong>500</strong> Following</span>
                    </div>
                    <button class="btn-follow">Follow</button>
                </div>
            </div>

            <div class="artist-posts">
                <div class="post-grid">
                    <div class="post-card">
                        <img src="Assets/draw2.png" alt="Post 1">
                        <div class="post-info">
                            <p class="hashtags">#original #illustration #character</p>
                            <p class="likes"><i class="fas fa-heart"></i> 120 likes</p>
                        </div>
                    </div>
                    <div class="post-card">
                        <img src="Assets/draw2.png" alt="Post 2">
                        <div class="post-info">
                            <p class="hashtags">#digitalart #vtuber #design</p>
                            <p class="likes"><i class="fas fa-heart"></i> 85 likes</p>
                        </div>
                    </div>
                    <div class="post-card">
                        <img src="Assets/draw2.png" alt="Post 3">
                        <div class="post-info">
                            <p class="hashtags">#fantasy #conceptart</p>
                            <p class="likes"><i class="fas fa-heart"></i> 200 likes</p>
                        </div>
                    </div>
                    <!-- Tambahkan lebih banyak post-card sesuai kebutuhan -->
                </div>
            </div>
        </section>
    </main>
    <script src="js/utils.js"></script>
    <script src="report-modal.js"></script>
    <script src="js/art-modal.js"></script>
</body>
</html>