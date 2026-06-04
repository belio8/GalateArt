<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'galateart');
define('DB_CHARSET', 'utf8mb4');

date_default_timezone_set('Asia/Jakarta');

// Buat koneksi
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    http_response_code(500);
    die(json_encode([
        'status'  => 'error',
        'message' => 'Koneksi database gagal: ' . mysqli_connect_error()
    ]));
}

// Set charset agar emoji & karakter khusus aman
mysqli_set_charset($conn, DB_CHARSET);

// ── Helper: query aman dengan prepared statement ──────────────
/**
 * Jalankan query dengan binding parameter.
 *
 * Contoh:
 *   $rows = db_query($conn, "SELECT * FROM users WHERE id = ?", "s", [$id]);
 *
 * @param mysqli  $conn   Koneksi aktif
 * @param string  $sql    Query SQL dengan placeholder ?
 * @param string  $types  Tipe parameter: s=string, i=int, d=decimal, b=blob
 * @param array   $params Nilai parameter
 * @return array          Array of associative arrays (rows)
 */
function db_query($conn_or_sql, $sql = '', string $types = '', array $params = []): array
{
    global $conn;

    if ($conn_or_sql instanceof mysqli) {
        $db = $conn_or_sql;
    } else {
        $db = $conn;
        $params = is_array($sql) ? $sql : [];
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $sql = $conn_or_sql;
    }

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('db_query prepare error: ' . $db->error . ' | SQL: ' . $sql);
        return [];
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    $stmt->close();
    return $rows;
}

/**
 * Jalankan INSERT / UPDATE / DELETE.
 * Mengembalikan insert_id untuk INSERT, atau affected_rows untuk lainnya.
 *
 * Contoh:
 *   $id = db_execute($conn, "INSERT INTO users (username) VALUES (?)", "s", ["abel"]);
 */
function db_execute(mysqli $conn, string $sql, string $types = '', array $params = []): int
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('db_execute prepare error: ' . $conn->error . ' | SQL: ' . $sql);
        return -1;
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->insert_id ?: $stmt->affected_rows;
    $stmt->close();
    return $result;
}

/**
 * Ambil satu baris saja.
 *
 * Contoh:
 *   $user = db_row($conn, "SELECT * FROM users WHERE email = ?", "s", [$email]);
 */
function db_row(mysqli $conn, string $sql, string $types = '', array $params = []): ?array
{
    $rows = db_query($conn, $sql, $types, $params);
    return $rows[0] ?? null;
}

/**
 * Generate UUID v4 sederhana (kompatibel PHP 7+).
 * Dipakai untuk kolom id CHAR(36).
 */
function uuid(): string
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Kirim respons JSON dan hentikan eksekusi.
 *
 * Contoh:
 *   json_response(['status' => 'ok', 'data' => $user]);
 */
function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
