<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - GalateArt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- NAVBAR -->
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <!-- BREADCRUMB -->
    <div style="padding: 18px 5%; display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-gray);">
        <a href="commission.php" style="color: var(--text-gray); text-decoration: none;">Commission</a>
        <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
        <span style="color: #fff; font-weight: 500;">Payment</span>
    </div>

    <!-- MAIN CONTENT -->
    <main class="ga-pay-page">

        <!-- LEFT: PAYMENT METHODS -->
        <div class="ga-pay-panel">
            <h2>Pilih Metode Pembayaran</h2>
            <p>Semua transaksi dienkripsi dan aman. Pilih metode yang paling nyaman untukmu.</p>

            <!-- E-Wallet -->
            <div class="ga-pay-method-group">
                <p class="ga-pay-section-title">E-Wallet</p>
                <div class="ga-pay-method-grid">

                    <label class="ga-pay-card" id="card-gopay" onclick="selectMethod('ga-pay-gopay', 'GoPay')">
                        <input type="radio" name="payment" value="ga-pay-gopay">
                        <div class="ga-pay-check-mark"></div>
                        <div class="ga-pay-logo ga-pay-gopay">G</div>
                        <div>
                            <div class="ga-pay-name">GoPay</div>
                            <div class="ga-pay-type">E-Wallet</div>
                        </div>
                    </label>

                    <label class="ga-pay-card" id="card-dana" onclick="selectMethod('ga-pay-dana', 'DANA')">
                        <input type="radio" name="payment" value="ga-pay-dana">
                        <div class="ga-pay-check-mark"></div>
                        <div class="ga-pay-logo ga-pay-dana">D</div>
                        <div>
                            <div class="ga-pay-name">DANA</div>
                            <div class="ga-pay-type">E-Wallet</div>
                        </div>
                    </label>

                    <label class="ga-pay-card" id="card-ovo" onclick="selectMethod('ga-pay-ovo', 'OVO')">
                        <input type="radio" name="payment" value="ga-pay-ovo">
                        <div class="ga-pay-check-mark"></div>
                        <div class="ga-pay-logo ga-pay-ovo">O</div>
                        <div>
                            <div class="ga-pay-name">OVO</div>
                            <div class="ga-pay-type">E-Wallet</div>
                        </div>
                    </label>

                </div>
            </div>

            <!-- QRIS -->
            <div class="ga-pay-method-group">
                <p class="ga-pay-section-title">QR Code</p>
                <div class="ga-pay-method-grid ga-pay-two-col">

                    <label class="ga-pay-card" id="card-qris" onclick="selectMethod('ga-pay-qris', 'QRIS')">
                        <input type="radio" name="payment" value="ga-pay-qris">
                        <div class="ga-pay-check-mark"></div>
                        <div class="ga-pay-logo ga-pay-qris"><i class="fas fa-qrcode" style="font-size: 20px;"></i></div>
                        <div>
                            <div class="ga-pay-name">QRIS</div>
                            <div class="ga-pay-type">Scan QR · Semua Bank</div>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Bank Transfer -->
            <div class="ga-pay-method-group">
                <p class="ga-pay-section-title">Transfer Bank</p>
                <div class="ga-pay-method-grid ga-pay-two-col">

                    <label class="ga-pay-card" id="card-bri" onclick="selectMethod('ga-pay-bri', 'BRI')">
                        <input type="radio" name="payment" value="ga-pay-bri">
                        <div class="ga-pay-check-mark"></div>
                        <div class="ga-pay-logo ga-pay-bri">BRI</div>
                        <div>
                            <div class="ga-pay-name">BRI</div>
                            <div class="ga-pay-type">Virtual Account</div>
                        </div>
                    </label>

                    <label class="ga-pay-card" id="card-bca" onclick="selectMethod('ga-pay-bca', 'BCA')">
                        <input type="radio" name="payment" value="ga-pay-bca">
                        <div class="ga-pay-check-mark"></div>
                        <div class="ga-pay-logo ga-pay-bca">BCA</div>
                        <div>
                            <div class="ga-pay-name">BCA</div>
                            <div class="ga-pay-type">Virtual Account</div>
                        </div>
                    </label>

                </div>
            </div>

            <!-- PROMO CODE -->
            <div class="ga-pay-method-group">
                <p class="ga-pay-section-title">Kode Promo</p>
                <div class="ga-pay-promo-row">
                    <input type="text" class="ga-pay-promo-input" id="promoInput" placeholder="Masukkan kode promo...">
                    <button class="ga-pay-btn-apply" onclick="applyPromo()">Terapkan</button>
                </div>
                <p class="ga-pay-promo-msg" id="promoMsg"></p>
            </div>
        </div>

        <!-- RIGHT: ORDER SUMMARY -->
        <div class="ga-pay-order-summary">
            <div class="ga-pay-summary-card">
                <h2>Ringkasan Pesanan</h2>

                <!-- Commission item -->
                <div class="ga-pay-commission-item">
                    <div class="ga-pay-commission-thumb-placeholder">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="ga-pay-commission-details">
                        <h4>Full Body Illustration</h4>
                        <p class="ga-pay-artist-tag">@artis_lokal</p>
                        <span class="ga-pay-tier-badge">â­ Premium Tier</span>
                    </div>
                </div>

                <!-- Delivery info -->
                <div class="ga-pay-delivery-info">
                    <i class="fas fa-clock"></i>
                    <span>Estimasi pengerjaan: <strong>7-14 hari kerja</strong> setelah pembayaran dikonfirmasi.</span>
                </div>

                <!-- Price breakdown -->
                <div class="ga-pay-price-rows">
                    <div class="ga-pay-price-row">
                        <span class="ga-pay-label">Harga Komisi</span>
                        <span class="ga-pay-value">Rp 350.000</span>
                    </div>
                    <div class="ga-pay-price-row">
                        <span class="ga-pay-label">Biaya Platform (5%)</span>
                        <span class="ga-pay-value">Rp 17.500</span>
                    </div>
                    <div class="ga-pay-price-row" id="discountRow" style="display: none;">
                        <span class="ga-pay-label">Diskon Promo</span>
                        <span class="ga-pay-value ga-pay-discount" id="discountValue">- Rp 0</span>
                    </div>
                    <div class="ga-pay-price-divider"></div>
                    <div class="ga-pay-price-row ga-pay-total">
                        <span class="ga-pay-label">Total Pembayaran</span>
                        <span class="ga-pay-value" id="totalValue">Rp 367.500</span>
                    </div>
                </div>

                <!-- Pay button -->
                <button class="ga-pay-btn-pay" id="btnPay" onclick="confirmPayment()" disabled>
                    <i class="fas fa-lock"></i>
                    <span id="payBtnText">Pilih Metode Dulu</span>
                </button>

                <p class="ga-pay-selected-label" id="selectedMethodLabel">Belum ada metode yang dipilih</p>

                <div class="ga-pay-security-note">
                    <i class="fas fa-shield-alt"></i>
                    <span>Transaksi diproteksi oleh enkripsi SSL 256-bit</span>
                </div>
            </div>
        </div>

    </main>

    <!-- SUCCESS MODAL -->
    <div class="ga-pay-success-overlay" id="successOverlay">
        <div class="ga-pay-success-box">
            <div class="ga-pay-success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2>Pembayaran Berhasil!</h2>
            <p>Pesanan komisimu telah dikonfirmasi. Artis akan segera memulai pengerjaannya.</p>
            <span class="ga-pay-order-id" id="orderIdDisplay">Order #GAL-000000</span>
            <button class="ga-pay-btn-done" onclick="window.location.href='landing.php'">
                Kembali ke Beranda
            </button>
        </div>
    </div>
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
    <script>
        // ── PAYMENT SELECTION ──
        let selectedMethod = null;
        let BASE_PRICE = 0;
        let PLATFORM_FEE = 0;
        let discountAmount = 0;

        function selectMethod(id, label) {
            const cardId = id.startsWith('card-') ? id : 'card-' + id.replace('ga-pay-', '');

            document.querySelectorAll('.ga-pay-card').forEach(c => c.classList.remove('ga-pay-selected'));
            const card = document.getElementById(cardId);
            if (card) card.classList.add('ga-pay-selected');

            selectedMethod = id;

            // Update button
            const btn = document.getElementById('btnPay');
            btn.disabled = false;
            document.getElementById('payBtnText').textContent = `Bayar dengan ${label}`;
            document.getElementById('selectedMethodLabel').innerHTML = `Metode terpilih: <span>${label}</span>`;
        }

        async function populateOrderSummary() {
            try {
                // Fetch dari server (aman, mengabaikan LocalStorage)
                const res = await fetch('api/get-cart-summary.php');
                const data = await res.json();
                
                if (data.status !== 'ok' || !data.data) return;
                
                const item = data.data;

                const titleEl = document.querySelector('.ga-pay-commission-details h4');
                const artistEl = document.querySelector('.ga-pay-commission-details .ga-pay-artist-tag');
                const badgeEl = document.querySelector('.ga-pay-tier-badge');
                const rows = document.querySelectorAll('.ga-pay-price-rows .ga-pay-price-row');
                const baseRow = rows[0]?.querySelector('.ga-pay-value');
                const feeRow = rows[1]?.querySelector('.ga-pay-value');
                const totalEl = document.getElementById('totalValue');

                BASE_PRICE = Number(item.total_price || 0);
                PLATFORM_FEE = Math.round(BASE_PRICE * 0.05);

                if (titleEl) titleEl.textContent = item.title || 'Commission / Post';
                if (artistEl) artistEl.textContent = `@${item.artist_username || 'artis'}`;
                if (badgeEl) badgeEl.textContent = item.badge_text || 'Item Terpilih';
                if (baseRow) baseRow.textContent = `Rp ${BASE_PRICE.toLocaleString('id-ID')}`;
                if (feeRow) feeRow.textContent = `Rp ${PLATFORM_FEE.toLocaleString('id-ID')}`;
                if (totalEl) totalEl.textContent = `Rp ${(BASE_PRICE + PLATFORM_FEE).toLocaleString('id-ID')}`;
            } catch (err) {
                console.error("Gagal mengambil ringkasan pesanan:", err);
            }
        }

        // ── PROMO CODE ──
        let promoApplied = false;
        let appliedPromoCode = '';

        async function applyPromo() {
            const code = document.getElementById('promoInput').value.trim().toUpperCase();
            const msg = document.getElementById('promoMsg');
            const discountRow = document.getElementById('discountRow');
            const discountVal = document.getElementById('discountValue');
            const total = document.getElementById('totalValue');

            if (!code) {
                msg.textContent = 'Masukkan kode promo terlebih dahulu.';
                msg.className = 'ga-pay-promo-msg ga-pay-error';
                return;
            }

            msg.textContent = 'Memvalidasi...';
            msg.className = 'ga-pay-promo-msg';

            try {
                const res = await fetch('api/validate-promo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code })
                });
                const data = await res.json();

                if (data.status === 'ok') {
                    const pct = data.discount_percent;
                    const subtotal = BASE_PRICE + PLATFORM_FEE;
                    discountAmount = Math.round(subtotal * (pct / 100));
                    const finalTotal = subtotal - discountAmount;

                    discountRow.style.display = 'flex';
                    discountVal.textContent = `- Rp ${discountAmount.toLocaleString('id-ID')}`;
                    total.textContent = `Rp ${finalTotal.toLocaleString('id-ID')}`;

                    msg.textContent = `✓ ${data.message} Diskon ${pct}% diterapkan.`;
                    msg.className = 'ga-pay-promo-msg ga-pay-success';
                    promoApplied = true;
                    appliedPromoCode = code;
                } else {
                    msg.textContent = data.message || 'Kode promo tidak valid.';
                    msg.className = 'ga-pay-promo-msg ga-pay-error';
                    discountAmount = 0;
                    discountRow.style.display = 'none';
                    total.textContent = `Rp ${(BASE_PRICE + PLATFORM_FEE).toLocaleString('id-ID')}`;
                    promoApplied = false;
                    appliedPromoCode = '';
                }
            } catch (err) {
                msg.textContent = 'Gagal menghubungi server.';
                msg.className = 'ga-pay-promo-msg ga-pay-error';
            }
        }

        populateOrderSummary();

        // Allow Enter key on promo input
        document.getElementById('promoInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') applyPromo();
        });

        // ── CONFIRM PAYMENT ──
        async function confirmPayment() {
            if (!selectedMethod) return;

            const btn = document.getElementById('btnPay');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const methodId = selectedMethod.replace('ga-pay-', '');

            try {
                const res = await fetch('api/mock-checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        method: methodId,
                        promo_code: appliedPromoCode
                    })
                });
                const data = await res.json();

                if (data.status === 'ok') {
                    const orderId = 'GAL-' + Math.floor(100000 + Math.random() * 900000);
                    document.getElementById('orderIdDisplay').textContent = data.order_code ? `Order #${data.order_code}` : `Order #${orderId}`;
                    document.getElementById('successOverlay').classList.add('ga-pay-open');
                } else {
                    alert(data.message || 'Gagal memproses pembayaran.');
                    btn.disabled = false;
                    btn.innerHTML = `<i class="fas fa-lock"></i> Bayar`;
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan jaringan.');
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-lock"></i> Bayar`;
            }
        }
    </script>
</body>
</html>