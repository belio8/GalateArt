<?php
require_once __DIR__ . '/components/bootstrap.php';
require_once __DIR__ . '/config/Db.php';

// Must be logged in
require_login();

$artistUsername = trim($_GET['artist'] ?? '');
if (!$artistUsername) {
    header('Location: ' . active_user_home());
    exit;
}

$artistRow = db_row($conn, "SELECT id, username, avatar_url, banner_url FROM users WHERE username = ? AND role = 'artist' AND is_banned = 0", "s", [$artistUsername]);
if (!$artistRow) {
    header('Location: ' . active_user_home());
    exit;
}

$artistProfile = db_row($conn, "SELECT * FROM artist_profiles WHERE user_id = ?", "s", [$artistRow['id']]);
if (!$artistProfile || $artistProfile['commission_status'] === 'closed') {
    $isClosed = true;
} else {
    $isClosed = false;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission @<?= e($artistRow['username']) ?> - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <main class="ga-com-page">
        <!-- Banner -->
        <div class="ga-com-banner" style="background-image: url('<?= e(!empty($artistRow['banner_url']) ? $artistRow['banner_url'] : 'Assets/galateart_banner.png') ?>'); background-size: cover; background-position: center; position:relative; overflow:hidden; min-height: 200px;">
            <div style="position:absolute; top:0; left:0; right:0; bottom:0; background: linear-gradient(135deg, rgba(26, 26, 36, 0.92), rgba(40, 20, 50, 0.85)); backdrop-filter:blur(8px); z-index:0;"></div>
            <!-- Floating decorative orbs -->
            <div class="com-float-orb" style="position:absolute; top:20px; right:80px; width:100px; height:100px; background: radial-gradient(circle, rgba(255,107,107,0.15), transparent); border-radius:50%; z-index:0; animation: comFloat 6s ease-in-out infinite;"></div>
            <div class="com-float-orb" style="position:absolute; bottom:10px; right:200px; width:60px; height:60px; background: radial-gradient(circle, rgba(139,92,246,0.15), transparent); border-radius:50%; z-index:0; animation: comFloat 8s ease-in-out infinite reverse;"></div>
            <div class="ga-com-banner-info" style="position:relative; z-index:1; display:flex; align-items:center; gap:25px; padding: 40px 35px;">
                <div class="com-avatar-ring">
                    <img src="<?= e(!empty($artistRow['avatar_url']) ? $artistRow['avatar_url'] : 'Assets/galateart_icon.png') ?>" alt="Avatar">
                </div>
                <div>
                    <h2 style="margin:0; font-size:30px; letter-spacing: -0.5px;">@<?= e($artistRow['username']) ?></h2>
                    <p style="margin:5px 0 12px 0; color:var(--text-gray); font-size: 14px;"><i class="fas fa-palette" style="margin-right:6px; opacity:0.6;"></i>Commission Page</p>
                    <?php if ($isClosed): ?>
                        <div class="ga-com-status-badge com-badge-closed"><span class="com-badge-dot"></span> Closed for Commission</div>
                    <?php else: ?>
                        <div class="ga-com-status-badge com-badge-open"><span class="com-badge-dot com-dot-pulse"></span> Open for Commission</div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Step indicators -->
            <div id="comStepIndicator" style="position:relative; z-index:1; display:flex; justify-content:center; gap:10px; padding-bottom:20px;">
                <div class="com-step-pill active" data-step="1"><span>1</span> Terms of Service</div>
                <div class="com-step-pill" data-step="2"><span>2</span> Detail & Submit</div>
            </div>
        </div>

        <?php if ($isClosed): ?>
            <div style="text-align:center; padding:50px; background:#1a1a24; border-radius:15px; margin-top:20px;">
                <i class="fas fa-door-closed" style="font-size:48px; color:var(--text-gray); margin-bottom:20px;"></i>
                <h2>Artist ini sedang tidak menerima commission.</h2>
                <button onclick="history.back()" style="background:var(--accent); color:white; border:none; padding:10px 20px; border-radius:20px; cursor:pointer; margin-top:10px;">Kembali</button>
            </div>
        <?php else: ?>
            <div class="ga-com-layout" id="commissionWizard">
                
                <!-- STEP 1: Terms of Service -->
                <div id="step1" class="wizard-step active" style="display:flex; justify-content:center;">
                    <div class="com-box" style="max-width: 800px; width: 100%; padding: 40px; position: relative; overflow: hidden;">
                        <!-- Decorative glow -->
                        <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: var(--accent); opacity: 0.1; filter: blur(40px); border-radius: 50%; pointer-events: none;"></div>
                        
                        <div style="text-align:center; margin-bottom: 30px;">
                            <div style="width:60px; height:60px; background:rgba(255,107,107,0.1); color:var(--accent); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin: 0 auto 15px auto;">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <h2 style="margin:0; font-size: 24px;">Terms of Service</h2>
                            <p style="color:var(--text-gray); margin-top:5px; font-size: 14px;">Harap baca dan setujui Terms of Service sebelum melanjutkan.</p>
                        </div>
                        
                        <div class="ga-com-tos-block" id="tosContent" style="background: rgba(26, 26, 36, 0.5); border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);">
                            <div class="spinner">Memuat TOS...</div>
                        </div>
                        
                        <div style="margin-top:30px; padding:20px; background: linear-gradient(145deg, #2a2a35, #22222d); border-radius:12px; border: 1px solid #333; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='#333'">
                            <label style="display:flex; align-items:center; gap:15px; cursor:pointer; font-weight:600;">
                                <input type="checkbox" id="agreeTos" style="width:24px; height:24px; accent-color:var(--accent); cursor:pointer;">
                                <span style="font-size: 15px;">Saya telah membaca dan menyetujui Terms of Service di atas.</span>
                            </label>
                        </div>
                        
                        <div style="display:flex; justify-content:center; margin-top:30px;">
                            <button class="com-btn-primary" id="btnNextStep1" disabled style="width: 100%; max-width: 300px; padding: 15px; font-size: 16px; border-radius: 30px;">Lanjutkan <i class="fas fa-arrow-right" style="margin-left:8px;"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Commission Form -->
                <div id="step2" class="wizard-step" style="display:none;">
                    <div style="display:flex; gap:40px; flex-wrap: wrap;">
                        
                        <!-- LEFT COLUMN: The Dynamic Form -->
                        <div class="com-form-col" style="flex:1; min-width: 300px;">
                            <button class="com-btn-back" onclick="goToStep(1)" style="margin-bottom:20px; transition: color 0.2s;"><i class="fas fa-arrow-left"></i> Kembali ke TOS</button>
                            
                            <div class="com-box" style="position: relative;">
                                <!-- Decorative glow -->
                                <div style="position: absolute; top: -50px; left: -50px; width: 150px; height: 150px; background: var(--purple); opacity: 0.05; filter: blur(40px); border-radius: 50%; pointer-events: none;"></div>
                                
                                <h3 class="com-title" style="border-bottom: 2px solid rgba(255,255,255,0.05); padding-bottom: 15px; margin-bottom: 25px;">Detail Commission</h3>
                                <div id="dynamicFormContainer">
                                    <div class="spinner">Memuat form...</div>
                                </div>

                                <div class="com-form-group" style="margin-top:35px; background: rgba(0,0,0,0.1); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.02);">
                                    <h4 class="com-section-title" style="margin-top: 0;">Character Reference <span class="required">*</span></h4>
                                    <p class="com-hint">Character reference sheets, PSD for rigging, mood boards, sample poses.</p>
                                    <div style="position: relative;">
                                        <input type="file" id="refFiles" multiple accept="image/*,.pdf,.psd" style="width:100%; background:#1a1a24; border:1px dashed #555; padding:15px; border-radius:8px; color:white; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='#555'">
                                    </div>
                                </div>

                                <div class="com-form-group" style="margin-top:30px;">
                                    <h4 class="com-section-title">Please Describe your request here :3 <span class="required">*</span></h4>
                                    <textarea id="descInput" placeholder="Deskripsikan dengan detail apa yang Anda inginkan..." style="width:100%; min-height:150px; background:#1a1a24; border:1px solid #444; color:white; padding:15px; border-radius:12px; font-family:inherit; resize:vertical; transition: border-color 0.3s, box-shadow 0.3s;" onfocus="this.style.borderColor='var(--accent)'; this.style.boxShadow='0 0 0 3px rgba(255,107,107,0.1)';" onblur="this.style.borderColor='#444'; this.style.boxShadow='none';"></textarea>
                                </div>

                                <div class="com-form-group" style="margin-top:30px;">
                                    <h4 class="com-section-title">Do you have a deadline for this project?</h4>
                                    <p class="com-hint">Refers to your preferred completion date. Artists are not required to deliver by this date.</p>
                                    <input type="date" id="deadlineInput" style="width:100%; max-width:250px; background:#1a1a24; border:1px solid #444; padding:12px 15px; border-radius:8px; color:white; font-family:inherit; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#444'">
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Price Breakdown Sidebar -->
                        <div class="com-sidebar-col" style="width:400px; flex-shrink:0;">
                            <div class="com-sidebar-sticky" style="position:sticky; top:100px; background:linear-gradient(180deg, #1e1e28 0%, #1a1a24 100%); border:1px solid #333; border-radius:20px; padding:25px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                                <h3 style="margin-top:0; font-size:20px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
                                    Order Summary
                                    <i class="fas fa-receipt" style="color:var(--text-gray); opacity:0.5;"></i>
                                </h3>
                                
                                <div id="summaryList" style="margin:20px 0; min-height: 50px;">
                                    <!-- Dynamic summary items -->
                                </div>

                                <div style="border-top:1px dashed rgba(255,255,255,0.08); margin:20px 0;"></div>
                                
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; background: rgba(255,107,107,0.05); padding: 15px; border-radius: 12px;">
                                    <strong style="font-size:16px;">Total Estimasi</strong>
                                    <strong id="totalPriceDisplay" style="font-size:24px; color:var(--accent); font-weight: 800;">Rp 0</strong>
                                </div>

                                <div style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.08), rgba(34, 197, 94, 0.03)); border: 1px solid rgba(34, 197, 94, 0.15); color:#86efac; padding:15px; border-radius:12px; margin-bottom:20px; font-size:12px;">
                                    <div style="font-weight:600; margin-bottom:8px; display:flex; align-items:center; gap:8px;"><i class="fas fa-shield-alt" style="color:#22c55e;"></i> Priority support</div>
                                    <div style="font-weight:600; display:flex; align-items:center; gap:8px;"><i class="fas fa-tasks" style="color:#22c55e;"></i> Track all requests + deliveries</div>
                                </div>

                                <div style="font-size:12px; color:var(--text-gray); margin-bottom:20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 12px;">
                                    <label style="display:flex; gap:10px; align-items:flex-start; cursor:pointer;">
                                        <input type="checkbox" id="understandCheck" style="margin-top:2px; accent-color: var(--accent); width: 16px; height: 16px;">
                                        <span style="line-height: 1.4;">I understand that submitting this request does not guarantee that the artist will accept my commission*</span>
                                    </label>
                                </div>

                                <button id="btnSubmitOrder" class="com-btn-submit" disabled style="width:100%; padding:16px; font-weight:bold; font-size:16px; background: linear-gradient(45deg, var(--accent), #ff8a8a); color: white; border: none; border-radius: 12px; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255,107,107,0.3);" onmouseover="if(!this.disabled) this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">Request Commission</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Success Modal -->
    <div class="modal-overlay" id="successOverlay">
        <div class="modal-content" style="text-align:center; position: relative; overflow: hidden; padding: 40px 30px;">
            <!-- Confetti decoration -->
            <div class="com-confetti-bg"></div>
            <div style="width:80px; height:80px; background:rgba(34,197,94,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin: 0 auto 20px auto; animation: comSuccessPop 0.5s ease-out;">
                <i class="fas fa-check-circle" style="font-size:42px; color:#22c55e;"></i>
            </div>
            <h2 style="margin-bottom:8px; font-size: 22px;">Commission Terkirim! 🎉</h2>
            <p style="color:var(--text-gray); margin-bottom:30px; font-size: 14px; line-height: 1.5;">Request kamu berhasil dikirim ke <strong style="color:white;">@<?= e($artistRow['username']) ?></strong>. Order sudah ditambahkan ke keranjang kamu.</p>
            <button onclick="window.location.href='cart.php'" style="background: linear-gradient(45deg, var(--accent), #ff8a8a); color:white; border:none; padding:14px 20px; border-radius:30px; font-weight:bold; width:100%; cursor:pointer; font-size: 15px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255,107,107,0.3);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'"><i class="fas fa-shopping-cart" style="margin-right:8px;"></i>Lihat Keranjang</button>
            <button onclick="window.location.href='visit-profile.php?user=<?= urlencode($artistRow['username']) ?>'" style="background:transparent; color:var(--text-gray); border:1px solid #333; padding:12px 20px; width:100%; cursor:pointer; margin-top:12px; border-radius: 30px; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--accent)'; this.style.color='white'" onmouseout="this.style.borderColor='#333'; this.style.color='var(--text-gray)'">Kembali ke Profil Artist</button>
        </div>
    </div>

    <script>
        const ARTIST_USERNAME = <?= json_encode($artistRow['username']) ?>;
    </script>
    <script src="js/utils.js?v=<?= time() ?>"></script>
    <script src="js/navbar.js?v=<?= time() ?>"></script>
    <script src="js/auth.js?v=<?= time() ?>"></script>
    <script src="js/commission.js?v=<?= time() ?>"></script>
</body>
</html>