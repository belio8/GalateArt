<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Only allow POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
}

// ── Auth check — must be logged in ──────────────────────────
if (empty($_SESSION['user_id'])) {
    json_response(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu untuk melakukan commission.'], 401);
}

$buyerId = $_SESSION['user_id'];

// ── Parse request body ──────────────────────────────────────
$artistUsername  = trim($_POST['artist_username'] ?? '');
$tierId         = trim($_POST['tier_id'] ?? '');
$characterCount = max(1, (int) ($_POST['character_count'] ?? 1));
$isNsfw         = (int) ($_POST['is_nsfw'] ?? 0);
$description    = trim($_POST['description'] ?? '');
$deadline       = trim($_POST['deadline'] ?? '');
$selectedOpts   = json_decode($_POST['selected_options'] ?? '[]', true);

// ── Upload files ────────────────────────────────────────────
$referenceFiles = [];
if (!empty($_FILES['reference_files']['name'][0])) {
    $uploadDir = __DIR__ . '/../uploads/references/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    foreach ($_FILES['reference_files']['name'] as $key => $name) {
        if ($_FILES['reference_files']['error'][$key] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['reference_files']['tmp_name'][$key];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $fileName = uuid() . '.' . $ext;
            if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                $referenceFiles[] = 'uploads/references/' . $fileName;
            }
        }
    }
}

// ── Validate required fields ────────────────────────────────
if (!$artistUsername || !$tierId) {
    json_response(['status' => 'error', 'message' => 'Artist dan tier harus dipilih.'], 422);
}

if (!$description) {
    json_response(['status' => 'error', 'message' => 'Deskripsi request wajib diisi.'], 422);
}

// ── Fetch artist ────────────────────────────────────────────
$artistUser = db_row($conn, "SELECT id FROM users WHERE username = ? AND role = 'artist' AND is_banned = 0", "s", [$artistUsername]);
if (!$artistUser) {
    json_response(['status' => 'error', 'message' => 'Artist tidak ditemukan.'], 404);
}

// Can't commission yourself
if ($artistUser['id'] === $buyerId) {
    json_response(['status' => 'error', 'message' => 'Tidak bisa commission diri sendiri.'], 422);
}

// ── Check artist commission status ──────────────────────────
$artistProfile = db_row($conn, "SELECT id, commission_status FROM artist_profiles WHERE user_id = ?", "s", [$artistUser['id']]);
if (!$artistProfile || $artistProfile['commission_status'] === 'closed') {
    json_response(['status' => 'error', 'message' => 'Artist sedang tidak menerima commission.'], 422);
}

// ── Fetch tier ──────────────────────────────────────────────
$tier = db_row($conn, "SELECT * FROM commission_tiers WHERE id = ? AND artist_id = ? AND status = 'active'", "ss", [$tierId, $artistProfile['id']]);
if (!$tier) {
    json_response(['status' => 'error', 'message' => 'Tier commission tidak valid.'], 422);
}

// ── Calculate price ─────────────────────────────────────────
$basePrice = (float) $tier['price'];
$tierPrice = $basePrice * $characterCount;
$addonTotal = 0.0;

// Process selected options and calculate additional cost
$optionsSummary = [];
foreach ($selectedOpts as $opt) {
    $catName = $opt['category'] ?? '';
    $items = $opt['items'] ?? [];
    $optItems = [];

    foreach ($items as $item) {
        $itemId = $item['id'] ?? '';
        // Fetch item from database to get authoritative price
        $dbItem = db_row($conn, "SELECT * FROM commission_option_items WHERE id = ?", "s", [$itemId]);
        if ($dbItem) {
            $itemPrice = 0;
            if ($dbItem['price_type'] === 'fixed') {
                $itemPrice = (float) $dbItem['price_value'];
            } elseif ($dbItem['price_type'] === 'percent') {
                $itemPrice = $tierPrice * ((float) $dbItem['price_value'] / 100);
            }
            $addonTotal += $itemPrice;
            $optItems[] = [
                'id'          => $dbItem['id'],
                'label'       => $dbItem['label'],
                'price_type'  => $dbItem['price_type'],
                'price_value' => (float) $dbItem['price_value'],
                'calculated'  => $itemPrice,
            ];
        }
    }

    $optionsSummary[] = [
        'category' => $catName,
        'items'    => $optItems,
    ];
}

$totalPrice = $tierPrice + $addonTotal;

// ── Validate deadline ───────────────────────────────────────
$deadlineDate = null;
if ($deadline) {
    $deadlineDate = date('Y-m-d', strtotime($deadline));
    if ($deadlineDate === '1970-01-01') {
        $deadlineDate = null;
    }
}

// ── Create order ────────────────────────────────────────────
$orderId = uuid();

$result = db_execute(
    $conn,
    "INSERT INTO orders (id, buyer_id, artist_id, tier_id, description, tier_price, addon_total, total_price, selected_options, character_count, is_nsfw, deadline, reference_files, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
    "sssssdddsiiss",
    [
        $orderId,
        $buyerId,
        $artistUser['id'],
        $tierId,
        $description,
        $tierPrice,
        $addonTotal,
        $totalPrice,
        json_encode($optionsSummary, JSON_UNESCAPED_UNICODE),
        $characterCount,
        $isNsfw,
        $deadlineDate,
        json_encode($referenceFiles, JSON_UNESCAPED_UNICODE),
    ]
);

if ($result < 0) {
    json_response(['status' => 'error', 'message' => 'Gagal membuat order.'], 500);
}

// ── Add to cart ─────────────────────────────────────────────
$cartId = uuid();
db_execute(
    $conn,
    "INSERT INTO cart_items (id, user_id, order_id) VALUES (?, ?, ?)",
    "sss",
    [$cartId, $buyerId, $orderId]
);

// ── Send notification to artist ─────────────────────────────
$notifId = uuid();
$buyerUsername = $_SESSION['username'] ?? 'Someone';
$notifText = "@{$buyerUsername} mengirim commission request ({$tier['name']})";

db_execute(
    $conn,
    "INSERT INTO notifications (id, user_id, text, type, ref_id) VALUES (?, ?, ?, 'commission', ?)",
    "ssss",
    [$notifId, $artistUser['id'], $notifText, $orderId]
);

// ── Return success ──────────────────────────────────────────
json_response([
    'status'      => 'ok',
    'message'     => 'Commission berhasil dikirim!',
    'order_id'    => $orderId,
    'cart_id'     => $cartId,
    'total_price' => $totalPrice,
]);
