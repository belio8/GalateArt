<?php
require_once __DIR__ . '/config/Db.php';

session_start();

$orderId = uuid();
$buyerId = '8151f151-5125-4299-8083-05ef8b6c0e0b'; // user_regular
$artistId = '827244df-9f08-47fd-b742-d9bcadad4255'; // artis_lokal
$tierId = 'f370ddb1-eb27-4ce2-9fa2-5c8af36a87ad'; // Chibi
$description = 'Test Description';
$tierPrice = 200000;
$addonTotal = 0;
$totalPrice = 200000;
$optionsSummary = [];
$characterCount = 1;
$isNsfw = 0;
$deadlineDate = null;
$referenceFiles = [];

$query = "INSERT INTO orders (id, buyer_id, artist_id, tier_id, description, tier_price, addon_total, total_price, selected_options, character_count, is_nsfw, deadline, reference_files, status)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
$types = "sssssdddsiiss";
$params = [
    $orderId,
    $buyerId,
    $artistId,
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
];

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    echo "Prepare failed: " . mysqli_error($conn) . "\n";
    exit;
}
mysqli_stmt_bind_param($stmt, $types, ...$params);
if (!mysqli_stmt_execute($stmt)) {
    echo "Execute failed: " . mysqli_error($conn) . "\n";
    exit;
}
echo "Success\n";
