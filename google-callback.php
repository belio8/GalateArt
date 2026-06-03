<?php
/**
 * Google OAuth callback page.
 *
 * Google redirects here with #access_token in the URL fragment (implicit flow).
 * JavaScript reads the token and sends it to our backend API.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Google - GalateArt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121218;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .callback-box {
            background: #1a1a24;
            border: 1px solid #333;
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
        }
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #333;
            border-top-color: #8e54e9;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 25px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h2 { font-size: 20px; margin-bottom: 10px; }
        p { color: #b3b3b3; font-size: 14px; }
        .error-msg { color: #f87171; margin-top: 15px; display: none; }
        .error-msg a { color: #8e54e9; text-decoration: none; }
        .error-msg a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="callback-box">
        <div class="spinner" id="spinner"></div>
        <h2 id="statusTitle">Memproses login Google...</h2>
        <p id="statusMsg">Tunggu sebentar ya</p>
        <p class="error-msg" id="errorMsg"></p>
    </div>

    <script>
    (function() {
        // Read the access_token from URL fragment (#access_token=...)
        const hash = window.location.hash.substring(1);
        const params = new URLSearchParams(hash);
        const accessToken = params.get('access_token');

        if (!accessToken) {
            document.getElementById('spinner').style.display = 'none';
            document.getElementById('statusTitle').textContent = 'Login Gagal';
            document.getElementById('statusMsg').textContent = '';
            const errorEl = document.getElementById('errorMsg');
            errorEl.style.display = 'block';
            errorEl.innerHTML = 'Token tidak ditemukan. <a href="landing.php">Kembali ke beranda</a>';
            return;
        }

        // Send access_token to our backend
        fetch('api/google-auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ access_token: accessToken }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok' && data.redirect) {
                document.getElementById('statusTitle').textContent = 'Login Berhasil!';
                document.getElementById('statusMsg').textContent = 'Mengalihkan...';
                window.location.href = data.redirect;
            } else {
                throw new Error(data.message || 'Login gagal');
            }
        })
        .catch(err => {
            document.getElementById('spinner').style.display = 'none';
            document.getElementById('statusTitle').textContent = 'Login Gagal';
            document.getElementById('statusMsg').textContent = '';
            const errorEl = document.getElementById('errorMsg');
            errorEl.style.display = 'block';
            errorEl.innerHTML = err.message + '. <a href="landing.php">Kembali ke beranda</a>';
        });
    })();
    </script>
</body>
</html>
