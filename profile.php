<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="profile-page-container is-artist" id="profileWrapper">
        
        <div class="profile-main">
            <div class="account-badge" id="accountBadgeText">Artist Account</div>
            
            <div class="cover-photo" style="background-image: url('https://via.placeholder.com/800x250/222222/555555?text=Cover+Image');">
                <!-- <img src="Assets/draw2.png" width="800" height="250"> -->
            </div>
            
            <div class="profile-info-section">
                <div class="avatar-container">
                    <img src="Assets/draw2.png">
                </div>
                
                <div class="profile-text">
                    <h1 id="userName">Miew</h1>
                    <p id="userHandle">@Miew</p>
                </div>
                
                <div class="commission-status">
                    <label>Set commission status</label>
                    <select>
                        <option>Open</option>
                        <option>Closed</option>
                        <option>Waitlist</option>
                    </select>
                </div>
            </div>

            <div class="profile-stats">
                <span><strong>67</strong> Following</span>
                <span><strong>3</strong> Followers</span>
            </div>

            <div class="profile-tabs">
                <button class="tab-btn active" onclick="switchTab(this, 'content-bio')">Bio</button>
                <button class="tab-btn tab-posts" onclick="switchTab(this, 'content-posts')">Posts</button>
                <button class="tab-btn" onclick="switchTab(this, 'content-saved')">Saved</button>
                <button class="tab-btn" onclick="switchTab(this, 'content-liked')">Liked</button>
            </div>

            <div class="profile-content">
                
                <div class="tab-content active" id="content-bio">
                    <p>No character yet...</p>
                </div>

                <div class="tab-content" id="content-posts">
                    <div class="empty-state">
                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486831.png" alt="No Posts">
                        <p>No Post Yet . . .</p>
                    </div>
                </div>

                <div class="tab-content" id="content-saved">
                    <div class="profile-art-grid">
                        <div class="art-card">
                            <img src="https://via.placeholder.com/300x400/b3b3b3/ffffff?text=Kobo" alt="Art">
                            <div class="art-info">
                                <p class="hashtags">#kobokanaeru #hololive #holoid</p>
                                <p class="artist-name">@jasper_xandros</p>
                            </div>
                        </div>
                        <div class="art-card">
                            <img src="https://via.placeholder.com/300x400/ff7e33/ffffff?text=Exusiai" alt="Art">
                            <div class="art-info">
                                <p class="hashtags">#exusiai #arknight #exiaalter</p>
                                <p class="artist-name">@artist_3</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="content-liked">
                    <div class="profile-art-grid">
                        <div class="art-card">
                            <img src="https://via.placeholder.com/300x400/8e54e9/ffffff?text=Liked+Art" alt="Art">
                            <div class="art-info">
                                <p class="hashtags">#illustration #digitalart</p>
                                <p class="artist-name">@ichigowarano</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="profile-sidebar">
            <div class="suggestions">
                <h3>You might like</h3>
                
                <div class="suggestion-item">
                    <div class="suggestion-info">
                        <img src="https://via.placeholder.com/40" class="suggestion-avatar" alt="Ichigo">
                        <div class="suggestion-text">
                            <h4>Ichigowarano</h4>
                            <p>@ichigowarano</p>
                        </div>
                    </div>
                    <button class="btn-follow">Follow</button>
                </div>

                <div class="suggestion-item">
                    <div class="suggestion-info">
                        <img src="https://via.placeholder.com/40" class="suggestion-avatar" alt="Keen">
                        <div class="suggestion-text">
                            <h4>keenbiscuit</h4>
                            <p>@keenbiscuit</p>
                        </div>
                    </div>
                    <button class="btn-follow">Follow</button>
                </div>

                <div class="suggestion-item">
                    <div class="suggestion-info">
                        <img src="https://via.placeholder.com/40" class="suggestion-avatar" alt="Jasper">
                        <div class="suggestion-text">
                            <h4>Jasper Alexandros</h4>
                            <p>@jasper_xandros</p>
                        </div>
                    </div>
                    <button class="btn-follow">Follow</button>
                </div>
            </div>
            <div class="logout-container">
                    <button id="btnLogout" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Keluar dari Akun
                    </button>
            </div>
        </div>
        
    </main>
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/profile.js"></script>
    <script src="script.js"></script>
    <script src="report-modal.js"></script>
</body>
</html>