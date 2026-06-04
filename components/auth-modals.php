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
        <div class="auth-divider">
            <span>atau</span>
        </div>
        <button type="button" class="btn-google" id="googleSignupBtn" onclick="triggerGoogleLogin()">
            <svg viewBox="0 0 48 48" width="20" height="20">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Daftar dengan Google
        </button>
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
        <div class="auth-divider">
            <span>atau</span>
        </div>
        <button type="button" class="btn-google" id="googleArtistSignupBtn" onclick="triggerArtistGoogleLogin()">
            <svg viewBox="0 0 48 48" width="20" height="20">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Daftar dengan Google
        </button>
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
        <div class="auth-divider">
            <span>atau</span>
        </div>
        <button type="button" class="btn-google" id="googleLoginBtn" onclick="triggerGoogleLogin()">
            <svg viewBox="0 0 48 48" width="20" height="20">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Masuk dengan Google
        </button>
        <p class="login-link">Belum punya akun? <a href="#" id="switchToSignup">Daftar di sini</a></p>
    </div>
</div>
<?php endif; ?>
