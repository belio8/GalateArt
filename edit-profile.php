<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login();
require_once __DIR__ . '/config/Db.php';

$userSession = current_user();
$userId = $userSession['id'];

// Fetch full user data
$userRow = db_row($conn, "SELECT * FROM users WHERE id = ?", "s", [$userId]);
$user = $userRow ?: $userSession;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
    <style>
        .edit-profile-container {
            max-width: 600px;
            margin: 40px auto;
            background: #1a1a2e;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .edit-profile-container h2 {
            margin-bottom: 25px;
            color: #a78bfa;
            text-align: center;
            font-size: 28px;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #e0e0e0;
            font-weight: 500;
        }
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            background: #0f0f17;
            border: 1px solid #333;
            border-radius: 10px;
            color: #e0e0e0;
            transition: border-color 0.3s ease;
        }
        .form-group input[type="file"]:hover, .form-group input[type="file"]:focus {
            border-color: #a78bfa;
            outline: none;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            background: #0f0f17;
            border: 1px solid #333;
            border-radius: 10px;
            color: #e0e0e0;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .form-group input[type="text"]:hover, .form-group input[type="text"]:focus {
            border-color: #a78bfa;
            outline: none;
        }
        .form-group textarea {
            width: 100%;
            padding: 15px;
            background: #0f0f17;
            border: 1px solid #333;
            border-radius: 10px;
            color: #e0e0e0;
            resize: vertical;
            min-height: 150px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease;
        }
        .form-group textarea:hover, .form-group textarea:focus {
            border-color: #a78bfa;
            outline: none;
        }
        .banner-preview-wrapper {
            position: relative;
            width: 100%;
            height: 160px;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 25px;
            border: 1px solid #333;
        }
        .banner-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .banner-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.45);
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }
        .banner-preview-wrapper:hover .banner-overlay {
            opacity: 1;
        }
        .banner-overlay i {
            font-size: 28px;
            color: #fff;
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px auto;
            border: 3px solid #a78bfa;
            display: block;
            box-shadow: 0 0 20px rgba(167, 139, 250, 0.4);
        }
        .btn-submit {
            background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
            color: #fff;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.5);
        }
        .btn-submit:disabled {
            background: #555;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .alert-message {
            display: none;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }
        .alert-success {
            background: rgba(74, 222, 128, 0.1);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }
        .alert-error {
            background: rgba(248, 113, 113, 0.1);
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, 0.3);
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #b3b3b3;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #a78bfa;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="container" style="padding-top: 80px;">
        <div class="edit-profile-container">
            <h2>Edit Profile</h2>
            <div id="alertMessage" class="alert-message"></div>
            
            <form id="editProfileForm" enctype="multipart/form-data">
                <!-- Banner -->
                <div class="form-group">
                    <label>Banner Profil</label>
                    <div class="banner-preview-wrapper" onclick="document.getElementById('banner').click()">
                        <img src="<?php echo htmlspecialchars(!empty($user['banner_url']) ? $user['banner_url'] : 'Assets/galateart_banner.png'); ?>" alt="Banner" class="banner-preview" id="bannerPreview">
                        <div class="banner-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <input type="file" id="banner" name="banner" accept="image/png, image/jpeg, image/webp" style="display: none;">
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username baru..." value="<?php echo htmlspecialchars($user['username']); ?>">
                </div>

                <!-- Avatar -->
                <div class="form-group" style="text-align: center;">
                    <img src="<?php echo htmlspecialchars(!empty($user['avatar_url']) ? $user['avatar_url'] : 'Assets/galateart_icon.png'); ?>" alt="Avatar" class="avatar-preview" id="avatarPreview">
                    <label for="avatar" style="margin-top: 10px; display: inline-block; cursor: pointer; background: #2a2a3e; padding: 8px 16px; border-radius: 8px; color: #a78bfa; font-size: 14px; border: 1px solid #333; transition: all 0.3s ease;" onmouseover="this.style.borderColor='#a78bfa'" onmouseout="this.style.borderColor='#333'">
                        <i class="fas fa-camera"></i> Ganti Foto Profil
                    </label>
                    <input type="file" id="avatar" name="avatar" accept="image/png, image/jpeg, image/webp" style="display: none;">
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio / Tentang Anda</label>
                    <textarea id="bio" name="bio" placeholder="Tulis sesuatu tentang diri Anda..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn-submit" id="btnSubmit">Simpan Perubahan</button>
                <div style="text-align: center;">
                    <a href="<?php echo active_user_profile(); ?>" class="back-link">
                        <i class="fas fa-arrow-left"></i> Batal & Kembali
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        // Preview Avatar
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatarPreview');
        
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Preview Banner
        const bannerInput = document.getElementById('banner');
        const bannerPreview = document.getElementById('bannerPreview');
        
        bannerInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    bannerPreview.src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Submit Form
        const editProfileForm = document.getElementById('editProfileForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const alertMessage = document.getElementById('alertMessage');

        editProfileForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            alertMessage.style.display = 'none';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('api/update-profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                alertMessage.style.display = 'block';
                if (response.ok && result.status === 'success') {
                    alertMessage.className = 'alert-message alert-success';
                    alertMessage.innerHTML = '<i class="fas fa-check-circle"></i> ' + result.message;
                    
                    setTimeout(() => {
                        window.location.href = '<?php echo active_user_profile(); ?>';
                    }, 1500);
                } else {
                    alertMessage.className = 'alert-message alert-error';
                    alertMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (result.message || 'Terjadi kesalahan.');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = 'Simpan Perubahan';
                }
            } catch (error) {
                alertMessage.style.display = 'block';
                alertMessage.className = 'alert-message alert-error';
                alertMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> Gagal menghubungi server.';
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Simpan Perubahan';
            }
        });
    </script>
</body>
</html>
