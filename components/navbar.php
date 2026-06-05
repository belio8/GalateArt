<?php
require_once __DIR__ . '/bootstrap.php';

$navUser = current_user();
$home = active_user_home();
$profile = active_user_profile();
?>
<header class="navbar">
    <div class="nav-left">
        <div class="menu-container">
            <i class="fas fa-bars menu-icon" id="menuToggle"></i>
            <div class="dropdown-menu" id="dropdownMenu">
                <ul>
                    <li><a href="tagline.php"><i class="fas fa-quote-left"></i> Tagline</a></li>
                    <li><a href="top-artists.php"><i class="fas fa-star"></i> Top Artist</a></li>
                    <li><a href="trending.php"><i class="fas fa-fire"></i> Trending</a></li>
                </ul>
            </div>
        </div>

        <div class="logo"><a href="<?= e($home) ?>" style="color:inherit;text-decoration:none; display:flex; align-items:center;"><img src="Assets/galateart_logo.png" alt="GalateArt" style="height: 50px; width: auto;"></a></div>
    </div>

    <div class="nav-center">
        <form class="search-bar" action="search-results.php" method="GET">
            <input type="text" name="q" placeholder="Cari karya seni..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES) : '' ?>">
            <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="nav-right">
        <?php if (!$navUser): ?>
            <button class="btn-artist" id="btnArtist" type="button">Saya seorang artis</button>
            <button class="btn-login" id="btnLogin" type="button">Masuk</button>
            <button class="btn-signup" id="btnSignup" type="button">Daftar</button>
        <?php endif; ?>

        <?php if ($navUser): ?>
            <script>
                window.GA_CURRENT_USER = <?= json_encode($navUser['username'] ?? '') ?>;
                window.GA_CURRENT_ROLE = <?= json_encode($navUser['role'] ?? '') ?>;
            </script>
            <div class="nav-icons">
                <a href="messages.php" aria-label="Pesan"><i class="far fa-comment"></i></a>

                <div class="notification-container">
                    <i class="far fa-bell" id="notifToggle"></i>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">Notifikasi</div>
                        <div class="notif-body" id="notifBody"></div>
                    </div>
                </div>

                <a href="cart.php" aria-label="Keranjang"><i class="fas fa-shopping-cart"></i></a>

                <a href="<?= e($profile) ?>" id="userProfileLink" class="profile-icon-link" title="<?= e($navUser['username']) ?>">
                    <img src="<?= htmlspecialchars(!empty($navUser['avatar_url']) ? $navUser['avatar_url'] : 'Assets/galateart_icon.png') ?>" alt="Profil" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;" referrerpolicy="no-referrer">
                </a>

                <form action="api/auth.php" method="post" style="display:inline;">
                    <input type="hidden" name="action" value="logout">
                    <div class="logout-container">
                        <button id="btnLogout" class="btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</header>
<?php include __DIR__ . '/auth-modals.php'; ?>
<?php include __DIR__ . '/edit-post-modal.php'; ?>
<script>
(function(){
    try {
        if (localStorage.getItem('nsfwFilter') !== 'off') {
            document.body.classList.add('nsfw-filter');
        }
    } catch(e){}
})();
</script>
