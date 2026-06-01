<?php require_once __DIR__ . '/bootstrap.php'; ?>
<?php if (!is_logged_in()): ?>
<div class="modal-overlay<?= ($_GET['auth'] ?? '') === 'signup' ? ' show' : '' ?>" id="registerModal">
    <div class="modal-content">
        <span class="close-btn" id="closeModal">&times;</span>
        <h2>Daftar ke GalateArt</h2>
        <form action="api/auth.php" method="POST" class="register-form">
            <input type="hidden" name="action" value="register">
            <div class="input-group">
                <label for="username">Nama Pengguna</label>
                <input type="text" id="username" name="username" placeholder="Pilih nama pengguna..." required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Masukkan alamat email..." required>
            </div>
            <div class="input-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" placeholder="Buat kata sandi yang kuat..." required>
            </div>
            <button type="submit" class="btn-submit">Buat Akun</button>
        </form>
        <p class="login-link">Sudah punya akun? <a href="#" id="openLoginFromSignup">Masuk di sini</a></p>
    </div>
</div>

<div class="modal-overlay" id="artistModal">
    <div class="modal-content">
        <span class="close-btn" id="closeArtistModal">&times;</span>
        <h2>Daftar sebagai Artis</h2>
        <form action="api/auth.php" method="POST" class="register-form">
            <input type="hidden" name="action" value="register_artist">
            <div class="input-group">
                <label for="artistName">Username</label>
                <input type="text" id="artistName" name="username" placeholder="Masukkan username Anda..." required>
            </div>
            <div class="input-group">
                <label for="artistEmail">Email</label>
                <input type="email" id="artistEmail" name="email" placeholder="Masukkan alamat email..." required>
            </div>
            <div class="input-group">
                <label for="portfolioLink">Link Portofolio (ArtStation, Behance, dll)</label>
                <input type="url" id="portfolioLink" name="portfolio_url" placeholder="https://..." required>
            </div>
            <div class="input-group">
                <label for="artistPassword">Kata Sandi</label>
                <input type="password" id="artistPassword" name="password" placeholder="Buat kata sandi yang kuat..." required>
            </div>
            <button type="submit" class="btn-submit">Daftar Menjadi Artis</button>
        </form>
    </div>
</div>

<div class="modal-overlay<?= ($_GET['auth'] ?? '') === 'login' ? ' show' : '' ?>" id="loginModal">
    <div class="modal-content">
        <span class="close-btn" id="closeLoginModal">&times;</span>
        <h2>Masuk ke GalateArt</h2>
        <form action="api/auth.php" method="POST" class="register-form" id="loginForm">
            <input type="hidden" name="action" value="login">
            <div class="input-group">
                <label for="loginUsername">Username</label>
                <input type="text" id="loginUsername" name="username" placeholder="Masukkan username Anda..." required>
            </div>
            <div class="input-group">
                <label for="loginPassword">Kata Sandi</label>
                <input type="password" id="loginPassword" name="password" placeholder="Masukkan kata sandi..." required>
            </div>
            <button type="submit" class="btn-submit">Masuk</button>
        </form>
        <p class="login-link">Belum punya akun? <a href="#" id="switchToSignup">Daftar di sini</a></p>
    </div>
</div>
<?php endif; ?>
