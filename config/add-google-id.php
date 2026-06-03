<?php
/**
 * Migration: Menambahkan kolom google_id ke tabel users.
 * 
 * Jalankan file ini sekali saja melalui browser:
 *   http://localhost/GalateArt/config/add-google-id.php
 */

$conn = mysqli_connect('localhost', 'root', '');
if (!$conn) {
    die('❌ Koneksi MySQL gagal: ' . mysqli_connect_error());
}

if (!$conn->select_db('galateart')) {
    die('❌ Database galateart tidak ditemukan. Jalankan setup.php terlebih dahulu.');
}

echo '<style>
    body { font-family: Poppins, sans-serif; background:#0f0f17; color:#e0e0e0; padding:40px; }
    .ok  { color:#4ade80; }
    .err { color:#f87171; }
    .warn { color:#facc15; }
    .box { background:#1a1a2e; border-radius:12px; padding:24px; max-width:700px; margin:auto; }
</style>
<div class="box">
<h2>🔧 Migration: Google Sign-In</h2>';

// Check if the column already exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'google_id'");

if ($result && $result->num_rows > 0) {
    echo '<p class="warn">⚠️ Kolom <strong>google_id</strong> sudah ada di tabel users. Tidak perlu migrasi.</p>';
} else {
    $sql = "ALTER TABLE users ADD COLUMN google_id VARCHAR(255) UNIQUE DEFAULT NULL AFTER password_hash";
    if ($conn->query($sql)) {
        echo '<p class="ok">✅ Kolom <strong>google_id</strong> berhasil ditambahkan ke tabel users.</p>';
    } else {
        echo '<p class="err">❌ Gagal menambahkan kolom: ' . htmlspecialchars($conn->error) . '</p>';
    }
}

echo '<p>Kamu sekarang bisa menggunakan Google Sign-In di GalateArt!</p>';
echo '</div>';

$conn->close();
