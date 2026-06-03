<?php
require_once __DIR__ . '/../config/Db.php';

session_start();

// ── Auth check ──────────────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    json_response(['status' => 'error', 'message' => 'Belum login.'], 401);
}

$me = $_SESSION['user_id'];

// ── Parse body ──────────────────────────────────────────────
$is_json = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
$body    = $is_json ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
$action  = trim($body['action'] ?? $_GET['action'] ?? '');

// ── Router ──────────────────────────────────────────────────
switch ($action) {
    case 'conversations': handle_conversations($conn, $me); break;
    case 'history':       handle_history($conn, $me);       break;
    case 'send':          handle_send($conn, $me, $body);   break;
    case 'users':         handle_users($conn, $me);         break;
    default:
        json_response(['status' => 'error', 'message' => 'Action tidak dikenal.'], 400);
}

// ============================================================
//  CONVERSATIONS — daftar percakapan
// ============================================================
function handle_conversations(mysqli $conn, string $me): void
{
    // Ambil semua partner unik yang pernah bertukar pesan dengan user ini,
    // beserta pesan terakhir dan jumlah pesan belum dibaca.
    $sql = "
        SELECT
            u.id            AS partner_id,
            u.username      AS username,
            u.avatar_url    AS avatar_url,
            last_msg.content   AS last_message,
            last_msg.sent_at   AS last_time,
            last_msg.sender_id AS last_sender_id,
            COALESCE(unread.cnt, 0) AS unread_count
        FROM (
            -- Kumpulkan semua partner unik
            SELECT DISTINCT
                CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?
        ) AS partners
        JOIN users u ON u.id = partners.partner_id
        -- Ambil pesan terakhir per partner
        JOIN messages last_msg ON last_msg.id = (
            SELECT m2.id FROM messages m2
            WHERE (m2.sender_id = ? AND m2.receiver_id = partners.partner_id)
               OR (m2.sender_id = partners.partner_id AND m2.receiver_id = ?)
            ORDER BY m2.sent_at DESC
            LIMIT 1
        )
        -- Hitung pesan belum dibaca (hanya dari partner ke saya)
        LEFT JOIN (
            SELECT sender_id, COUNT(*) AS cnt
            FROM messages
            WHERE receiver_id = ? AND is_read = 0
            GROUP BY sender_id
        ) AS unread ON unread.sender_id = partners.partner_id
        ORDER BY last_msg.sent_at DESC
    ";

    $rows = db_query($conn, $sql, 'ssssss', [$me, $me, $me, $me, $me, $me]);

    // Format waktu untuk tampilan sidebar
    foreach ($rows as &$row) {
        $row['last_time_formatted'] = format_time($row['last_time']);
        // Potong preview pesan
        if (mb_strlen($row['last_message']) > 50) {
            $row['last_message'] = mb_substr($row['last_message'], 0, 50) . '…';
        }
        // Fallback avatar
        if (empty($row['avatar_url'])) {
            $row['avatar_url'] = 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($row['username']);
        }
    }
    unset($row);

    json_response(['status' => 'ok', 'conversations' => $rows]);
}

// ============================================================
//  HISTORY — riwayat chat dengan partner
// ============================================================
function handle_history(mysqli $conn, string $me): void
{
    $partner_id = trim($_GET['partner_id'] ?? '');
    if (!$partner_id) {
        json_response(['status' => 'error', 'message' => 'partner_id diperlukan.'], 400);
    }

    // Ambil info partner
    $partner = db_row($conn,
        "SELECT id, username, avatar_url FROM users WHERE id = ? LIMIT 1",
        "s", [$partner_id]
    );
    if (!$partner) {
        json_response(['status' => 'error', 'message' => 'User tidak ditemukan.'], 404);
    }
    if (empty($partner['avatar_url'])) {
        $partner['avatar_url'] = 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($partner['username']);
    }

    // Tandai pesan dari partner sebagai sudah dibaca
    db_execute($conn,
        "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0",
        "ss", [$partner_id, $me]
    );

    // Ambil semua pesan antara kedua user, urut dari lama ke baru
    $messages = db_query($conn,
        "SELECT id, sender_id, receiver_id, content, is_read, sent_at
         FROM messages
         WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
         ORDER BY sent_at ASC",
        "ssss", [$me, $partner_id, $partner_id, $me]
    );

    // Format waktu
    foreach ($messages as &$msg) {
        $ts = strtotime($msg['sent_at']);
        $msg['time_formatted'] = date('H:i', $ts);
        $msg['date_formatted'] = format_date($msg['sent_at']);
        $msg['is_me'] = ($msg['sender_id'] === $me);
    }
    unset($msg);

    json_response([
        'status'   => 'ok',
        'partner'  => $partner,
        'messages' => $messages,
    ]);
}

// ============================================================
//  SEND — kirim pesan baru
// ============================================================
function handle_send(mysqli $conn, string $me, array $body): void
{
    $receiver_id = trim($body['receiver_id'] ?? '');
    $content     = trim($body['content'] ?? '');

    if (!$receiver_id || !$content) {
        json_response(['status' => 'error', 'message' => 'receiver_id dan content wajib diisi.'], 422);
    }

    // Cek receiver ada
    $receiver = db_row($conn,
        "SELECT id FROM users WHERE id = ? LIMIT 1",
        "s", [$receiver_id]
    );
    if (!$receiver) {
        json_response(['status' => 'error', 'message' => 'Penerima tidak ditemukan.'], 404);
    }

    // Jangan kirim ke diri sendiri
    if ($receiver_id === $me) {
        json_response(['status' => 'error', 'message' => 'Tidak bisa mengirim pesan ke diri sendiri.'], 400);
    }

    $id = uuid();
    $result = db_execute($conn,
        "INSERT INTO messages (id, sender_id, receiver_id, content, is_read, sent_at)
         VALUES (?, ?, ?, ?, 0, NOW())",
        "ssss", [$id, $me, $receiver_id, $content]
    );

    if ($result < 0) {
        json_response(['status' => 'error', 'message' => 'Gagal mengirim pesan.'], 500);
    }

    // Ambil pesan yang baru dikirim
    $msg = db_row($conn, "SELECT * FROM messages WHERE id = ?", "s", [$id]);

    json_response([
        'status'  => 'ok',
        'message' => 'Pesan terkirim.',
        'data'    => [
            'id'         => $id,
            'content'    => $content,
            'sent_at'    => $msg['sent_at'] ?? date('Y-m-d H:i:s'),
            'time_formatted' => date('H:i'),
        ],
    ]);
}

// ============================================================
//  USERS — cari user untuk percakapan baru
// ============================================================
function handle_users(mysqli $conn, string $me): void
{
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 1) {
        json_response(['status' => 'ok', 'users' => []]);
    }

    $like = '%' . $q . '%';
    $users = db_query($conn,
        "SELECT id, username, avatar_url, role
         FROM users
         WHERE id != ? AND username LIKE ? AND is_banned = 0
         ORDER BY username ASC
         LIMIT 10",
        "ss", [$me, $like]
    );

    foreach ($users as &$u) {
        if (empty($u['avatar_url'])) {
            $u['avatar_url'] = 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($u['username']);
        }
    }
    unset($u);

    json_response(['status' => 'ok', 'users' => $users]);
}

// ── Helpers ─────────────────────────────────────────────────

/**
 * Format waktu untuk sidebar (hari ini → jam, kemarin → "Kemarin", dll)
 */
function format_time(string $datetime): string
{
    $ts    = strtotime($datetime);
    $now   = time();
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');

    if ($ts >= $today) {
        return date('H:i', $ts);
    }
    if ($ts >= $yesterday) {
        return 'Kemarin';
    }
    // Dalam seminggu terakhir
    if ($ts >= $now - 7 * 86400) {
        $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        return $days[(int)date('w', $ts)];
    }
    return date('d/m/Y', $ts);
}

/**
 * Format tanggal untuk date divider di chat
 */
function format_date(string $datetime): string
{
    $ts    = strtotime($datetime);
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');

    if ($ts >= $today) {
        return 'Hari ini';
    }
    if ($ts >= $yesterday) {
        return 'Kemarin';
    }

    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $days   = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    $dayName   = $days[(int)date('w', $ts)];
    $dayNum    = date('j', $ts);
    $monthName = $months[(int)date('n', $ts)];
    $year      = date('Y', $ts);

    return "$dayName, $dayNum $monthName $year";
}
