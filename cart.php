<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login();
require_once __DIR__ . '/config/Db.php';

$cartItems = db_query(
    "SELECT ci.id AS cart_id, o.id AS order_id, o.total_price, o.tier_price, o.addon_total,
            COALESCE(ct.name, 'Commission') AS tier_name,
            COALESCE(artist.username, 'artis_lokal') AS artist_name
     FROM cart_items ci
     JOIN orders o ON o.id = ci.order_id
     LEFT JOIN commission_tiers ct ON ct.id = o.tier_id
     LEFT JOIN users artist ON artist.id = o.artist_id
     WHERE ci.user_id = ?
     ORDER BY ci.added_at DESC",
    [$_SESSION['user_id']]
);

$subtotal = array_reduce($cartItems, function ($sum, $item) {
    return $sum + (float) $item['total_price'];
}, 0.0);
$platformFee = round($subtotal * 0.05);
$promoCode = strtoupper(trim($_GET['promo'] ?? ''));
$discount = $promoCode === 'GALATE10' ? round($subtotal * 0.10) : 0;
$total = max(0, $subtotal + $platformFee - $discount);

function rupiah(float $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - GalateArt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <div class="ga-cart-page">
        <!-- Cart Items -->
        <div class="ga-cart-items-col">
            <h1><i class="fas fa-shopping-cart" style="color:var(--accent);margin-right:10px;"></i>Keranjang</h1>
            <p class="ga-cart-subtitle" id="cartSubtitle"><?= count($cartItems) ?> item menunggu checkout</p>

            <div id="cartList">
                <?php foreach ($cartItems as $item): ?>
                    <div class="ga-cart-item" id="<?= e($item['cart_id']) ?>">
                        <img class="ga-cart-item-img" src="Assets/draw2.png" alt="Commission Art">
                        <div class="ga-cart-item-body">
                            <div class="ga-cart-item-top">
                                <div>
                                    <p class="ga-cart-item-title"><?= e($item['tier_name']) ?></p>
                                    <p class="ga-cart-item-artist">@<?= e($item['artist_name']) ?></p>
                                    <span class="ga-cart-item-type"><i class="fas fa-paint-brush" style="margin-right:4px;"></i>Commission</span>
                                </div>
                                <span class="ga-cart-item-price"><?= rupiah((float) $item['total_price']) ?></span>
                            </div>
                            <div class="ga-cart-item-actions">
                                <button class="ga-cart-btn-save-later" type="button"><i class="far fa-bookmark"></i> Simpan nanti</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="ga-cart-empty" id="emptyCart" style="<?= $cartItems ? 'display:none;' : '' ?>">
                <i class="fas fa-shopping-cart"></i>
                <p>Keranjangmu kosong.<br><a href="landing.php">Jelajahi karya seni â†’</a></p>
            </div>
        </div>

        <!-- Summary -->
        <div class="ga-cart-summary-col" style="<?= $cartItems ? '' : 'display:none;' ?>">
            <div class="ga-cart-summary-box">
                <h2>Ringkasan Order</h2>

                <div class="ga-cart-summary-row"><span>Subtotal (<?= count($cartItems) ?> item)</span><span id="subtotalVal"><?= rupiah($subtotal) ?></span></div>
                <div class="ga-cart-summary-row"><span>Biaya platform (5%)</span><span id="feeVal"><?= rupiah($platformFee) ?></span></div>
                <div class="ga-cart-summary-row"><span>Diskon promo</span><span id="discountVal" style="color:#4caf50;">- <?= rupiah($discount) ?></span></div>
                <hr class="ga-cart-summary-divider">
                <div class="ga-cart-summary-total"><span>Total</span><span id="totalVal"><?= rupiah($total) ?></span></div>

                <form class="ga-cart-promo-row" method="get">
                    <input class="ga-cart-promo-input" type="text" name="promo" placeholder="Kode promo..." id="promoInput" value="<?= e($promoCode) ?>">
                    <button class="ga-cart-btn-promo" type="submit">Pakai</button>
                </form>
                <div id="promoMsg" style="font-size:12px;margin-top:6px;color:<?= $discount ? '#4caf50' : 'var(--text-gray)' ?>;">
                    <?= $discount ? 'Promo diterapkan! Diskon 10% aktif.' : '' ?>
                </div>

                <button class="ga-cart-btn-checkout" onclick="location.href='payment.php'">
                    <i class="fas fa-lock"></i> Lanjut ke Pembayaran
                </button>

                <div style="margin-top:16px;">
                    <div style="font-size:12px;color:var(--text-gray);margin-bottom:8px;">Metode pembayaran yang diterima:</div>
                    <div class="ga-cart-payment-icons">
                        <span class="ga-cart-payment-badge">GoPay</span>
                        <span class="ga-cart-payment-badge">DANA</span>
                        <span class="ga-cart-payment-badge">OVO</span>
                        <span class="ga-cart-payment-badge">QRIS</span>
                        <span class="ga-cart-payment-badge">BCA</span>
                        <span class="ga-cart-payment-badge">BRI</span>
                    </div>
                </div>

                <div style="margin-top:16px;font-size:12px;color:var(--text-gray);display:flex;gap:6px;align-items:center;">
                    <i class="fas fa-shield-alt" style="color:var(--accent);"></i>
                    Pembayaran aman & terlindungi GalateArt
                </div>
            </div>
        </div>
    </div>
    <script src="js/utils.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
</body>
</html>
