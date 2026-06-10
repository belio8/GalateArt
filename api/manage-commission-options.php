<?php
require_once __DIR__ . '/../config/Db.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Auth check — must be artist ─────────────────────────────
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'artist') {
    json_response(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
}

$userId = $_SESSION['user_id'];

// ── Get artist profile ──────────────────────────────────────
$artistProfile = db_row($conn, "SELECT id FROM artist_profiles WHERE user_id = ?", "s", [$userId]);
if (!$artistProfile) {
    json_response(['status' => 'error', 'message' => 'Profil artist tidak ditemukan.'], 404);
}

$artistId = $artistProfile['id'];
$method = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$action = trim($body['action'] ?? $_GET['action'] ?? '');

// ── GET — List all commission options ───────────────────────
if ($method === 'GET' && ($action === 'list' || !$action)) {
    $options = db_query(
        $conn,
        "SELECT id, category, description, selection_type, is_required, sort_order
         FROM commission_options WHERE artist_id = ? ORDER BY sort_order ASC",
        "s", [$artistId]
    );

    foreach ($options as &$opt) {
        $opt['items'] = db_query(
            $conn,
            "SELECT id, label, price_type, price_value, is_default, sort_order
             FROM commission_option_items WHERE option_id = ? ORDER BY sort_order ASC",
            "s", [$opt['id']]
        );
    }
    unset($opt);

    json_response(['status' => 'ok', 'options' => $options]);
}

// ── POST — Create / Update / Delete ─────────────────────────
if ($method !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
}

// ── Action: add_option ──────────────────────────────────────
if ($action === 'add_option') {
    $category      = trim($body['category'] ?? '');
    $description   = trim($body['description'] ?? '');
    $selectionType = trim($body['selection_type'] ?? 'single');
    $isRequired    = (int) ($body['is_required'] ?? 0);
    $sortOrder     = (int) ($body['sort_order'] ?? 0);

    if (!$category) {
        json_response(['status' => 'error', 'message' => 'Nama kategori wajib diisi.'], 422);
    }

    $id = uuid();
    db_execute($conn,
        "INSERT INTO commission_options (id, artist_id, category, description, selection_type, is_required, sort_order)
         VALUES (?,?,?,?,?,?,?)",
        "sssssii",
        [$id, $artistId, $category, $description, $selectionType, $isRequired, $sortOrder]
    );

    json_response(['status' => 'ok', 'message' => 'Option ditambahkan.', 'option_id' => $id]);
}

// ── Action: update_option ───────────────────────────────────
if ($action === 'update_option') {
    $optionId      = trim($body['option_id'] ?? '');
    $category      = trim($body['category'] ?? '');
    $description   = trim($body['description'] ?? '');
    $selectionType = trim($body['selection_type'] ?? 'single');
    $isRequired    = (int) ($body['is_required'] ?? 0);
    $sortOrder     = (int) ($body['sort_order'] ?? 0);

    if (!$optionId || !$category) {
        json_response(['status' => 'error', 'message' => 'Option ID dan kategori wajib diisi.'], 422);
    }

    db_execute($conn,
        "UPDATE commission_options SET category=?, description=?, selection_type=?, is_required=?, sort_order=?
         WHERE id=? AND artist_id=?",
        "sssiisd",
        [$category, $description, $selectionType, $isRequired, $sortOrder, $optionId, $artistId]
    );

    json_response(['status' => 'ok', 'message' => 'Option diupdate.']);
}

// ── Action: delete_option ───────────────────────────────────
if ($action === 'delete_option') {
    $optionId = trim($body['option_id'] ?? '');
    if (!$optionId) {
        json_response(['status' => 'error', 'message' => 'Option ID wajib.'], 422);
    }

    db_execute($conn, "DELETE FROM commission_options WHERE id=? AND artist_id=?", "ss", [$optionId, $artistId]);
    json_response(['status' => 'ok', 'message' => 'Option dihapus.']);
}

// ── Action: add_item ────────────────────────────────────────
if ($action === 'add_item') {
    $optionId   = trim($body['option_id'] ?? '');
    $label      = trim($body['label'] ?? '');
    $priceType  = trim($body['price_type'] ?? 'fixed');
    $priceValue = (float) ($body['price_value'] ?? 0);
    $isDefault  = (int) ($body['is_default'] ?? 0);
    $sortOrder  = (int) ($body['sort_order'] ?? 0);

    if (!$optionId || !$label) {
        json_response(['status' => 'error', 'message' => 'Option ID dan label wajib diisi.'], 422);
    }

    // Verify option belongs to this artist
    $opt = db_row($conn, "SELECT id FROM commission_options WHERE id=? AND artist_id=?", "ss", [$optionId, $artistId]);
    if (!$opt) {
        json_response(['status' => 'error', 'message' => 'Option tidak ditemukan.'], 404);
    }

    $id = uuid();
    db_execute($conn,
        "INSERT INTO commission_option_items (id, option_id, label, price_type, price_value, is_default, sort_order)
         VALUES (?,?,?,?,?,?,?)",
        "ssssdii",
        [$id, $optionId, $label, $priceType, $priceValue, $isDefault, $sortOrder]
    );

    json_response(['status' => 'ok', 'message' => 'Item ditambahkan.', 'item_id' => $id]);
}

// ── Action: delete_item ─────────────────────────────────────
if ($action === 'delete_item') {
    $itemId = trim($body['item_id'] ?? '');
    if (!$itemId) {
        json_response(['status' => 'error', 'message' => 'Item ID wajib.'], 422);
    }

    // Verify item belongs to artist's option
    $item = db_row($conn,
        "SELECT oi.id FROM commission_option_items oi
         JOIN commission_options co ON co.id = oi.option_id
         WHERE oi.id = ? AND co.artist_id = ?",
        "ss", [$itemId, $artistId]
    );
    if (!$item) {
        json_response(['status' => 'error', 'message' => 'Item tidak ditemukan.'], 404);
    }

    db_execute($conn, "DELETE FROM commission_option_items WHERE id=?", "s", [$itemId]);
    json_response(['status' => 'ok', 'message' => 'Item dihapus.']);
}

// ── Action: update_tos ──────────────────────────────────────
if ($action === 'update_tos') {
    $tos = trim($body['tos'] ?? '');
    db_execute($conn, "UPDATE artist_profiles SET tos = ? WHERE id = ?", "ss", [$tos, $artistId]);
    json_response(['status' => 'ok', 'message' => 'TOS diupdate.']);
}

// ── Unknown action ──────────────────────────────────────────
json_response(['status' => 'error', 'message' => 'Action tidak dikenal: ' . $action], 400);
