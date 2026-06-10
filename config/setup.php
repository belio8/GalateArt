<?php

// ── Koneksi awal (tanpa pilih DB dulu) ──────────────────────
$conn = mysqli_connect('localhost', 'root', '');
if (!$conn) {
    die('❌ Koneksi MySQL gagal: ' . mysqli_connect_error());
}

echo '<style>
    body { font-family: Poppins, sans-serif; background:#0f0f17; color:#e0e0e0; padding:40px; }
    h2   { color:#a78bfa; }
    .ok  { color:#4ade80; }
    .err { color:#f87171; }
    .sep { border-top:1px solid #333; margin:20px 0; }
    .box { background:#1a1a2e; border-radius:12px; padding:24px; max-width:700px; margin:auto; }
    a    { color:#a78bfa; }
</style>
<div class="box">
<h2>🚀 GalateArt — Setup Database</h2>';

// ── 1. Buat Database ─────────────────────────────────────────
$conn->query("CREATE DATABASE IF NOT EXISTS galateart
              CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

if ($conn->select_db('galateart')) {
    echo '<p class="ok">✅ Database <strong>galateart</strong> siap.</p>';
} else {
    die('<p class="err">❌ Gagal memilih database: ' . $conn->error . '</p>');
}

mysqli_set_charset($conn, 'utf8mb4');

// ── 2. Definisi semua tabel ───────────────────────────────────
// Urutan PENTING: tabel induk harus dibuat sebelum yang ber-FK.

$tables = [];

// ── USERS ────────────────────────────────────────────────────
$tables['users'] = "CREATE TABLE IF NOT EXISTS users (
    id            CHAR(36)     PRIMARY KEY,
    username      VARCHAR(50)  UNIQUE NOT NULL,
    email         VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    google_id     VARCHAR(255) UNIQUE DEFAULT NULL,
    role          ENUM('regular','artist','admin') NOT NULL DEFAULT 'regular',
    avatar_url    VARCHAR(255) DEFAULT NULL,
    bio           TEXT         DEFAULT NULL,
    is_banned     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                               ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB";

// ── ARTIST_PROFILES ──────────────────────────────────────────
$tables['artist_profiles'] = "CREATE TABLE IF NOT EXISTS artist_profiles (
    id                 CHAR(36) PRIMARY KEY,
    user_id            CHAR(36) NOT NULL,
    portfolio_url      VARCHAR(255) DEFAULT NULL,
    portfolio_file_url VARCHAR(255) DEFAULT NULL,
    commission_status  ENUM('open','closed','waitlist') NOT NULL DEFAULT 'closed',
    tos                TEXT DEFAULT NULL,
    turnaround_days    INT  DEFAULT NULL,
    verified_at        TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── POSTS ────────────────────────────────────────────────────
$tables['posts'] = "CREATE TABLE IF NOT EXISTS posts (
    id          CHAR(36)      PRIMARY KEY,
    artist_id   CHAR(36)      NOT NULL,
    title       VARCHAR(255)  NOT NULL,
    description TEXT          DEFAULT NULL,
    image_url   VARCHAR(255)  DEFAULT NULL,
    price       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    is_free     TINYINT(1)    NOT NULL DEFAULT 0,
    is_nsfw     TINYINT(1)    NOT NULL DEFAULT 0,
    status      ENUM('active','flagged','removed') NOT NULL DEFAULT 'active',
    like_count  INT           NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── POST_TAGS ────────────────────────────────────────────────
$tables['post_tags'] = "CREATE TABLE IF NOT EXISTS post_tags (
    id      CHAR(36)     PRIMARY KEY,
    post_id CHAR(36)     NOT NULL,
    tag     VARCHAR(100) NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── FOLLOWS ──────────────────────────────────────────────────
$tables['follows'] = "CREATE TABLE IF NOT EXISTS follows (
    id           CHAR(36)  PRIMARY KEY,
    follower_id  CHAR(36)  NOT NULL,
    following_id CHAR(36)  NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_follow (follower_id, following_id),
    FOREIGN KEY (follower_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── LIKES ────────────────────────────────────────────────────
$tables['likes'] = "CREATE TABLE IF NOT EXISTS likes (
    id         CHAR(36)  PRIMARY KEY,
    user_id    CHAR(36)  NOT NULL,
    post_id    CHAR(36)  NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_like (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── SAVES ────────────────────────────────────────────────────
$tables['saves'] = "CREATE TABLE IF NOT EXISTS saves (
    id         CHAR(36)  PRIMARY KEY,
    user_id    CHAR(36)  NOT NULL,
    post_id    CHAR(36)  NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_save (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── COMMENTS ─────────────────────────────────────────────────
$tables['comments'] = "CREATE TABLE IF NOT EXISTS comments (
    id         CHAR(36)  PRIMARY KEY,
    user_id    CHAR(36)  NOT NULL,
    post_id    CHAR(36)  NOT NULL,
    content    TEXT      NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── COMMISSION_TIERS ─────────────────────────────────────────
$tables['commission_tiers'] = "CREATE TABLE IF NOT EXISTS commission_tiers (
    id             CHAR(36)      PRIMARY KEY,
    artist_id      CHAR(36)      NOT NULL,
    name           VARCHAR(100)  NOT NULL,
    label          VARCHAR(100)  DEFAULT NULL,
    description    TEXT          DEFAULT NULL,
    price          DECIMAL(12,2) NOT NULL,
    revision_count INT           NOT NULL DEFAULT 0,
    commercial_use TINYINT(1)    NOT NULL DEFAULT 0,
    status         ENUM('active','inactive') NOT NULL DEFAULT 'active',
    FOREIGN KEY (artist_id) REFERENCES artist_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── COMMISSION_ADDONS ────────────────────────────────────────
$tables['commission_addons'] = "CREATE TABLE IF NOT EXISTS commission_addons (
    id        CHAR(36)      PRIMARY KEY,
    artist_id CHAR(36)      NOT NULL,
    label     VARCHAR(100)  NOT NULL,
    price     DECIMAL(12,2) NOT NULL,
    type      ENUM('extra_char','bg_detail','speedpaint','priority') NOT NULL,
    FOREIGN KEY (artist_id) REFERENCES artist_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── COMMISSION_OPTIONS ──────────────────────────────────────
$tables['commission_options'] = "CREATE TABLE IF NOT EXISTS commission_options (
    id              CHAR(36)      PRIMARY KEY,
    artist_id       CHAR(36)      NOT NULL,
    category        VARCHAR(100)  NOT NULL,
    description     TEXT          DEFAULT NULL,
    selection_type  ENUM('single','multiple') NOT NULL DEFAULT 'single',
    is_required     TINYINT(1)    NOT NULL DEFAULT 0,
    sort_order      INT           NOT NULL DEFAULT 0,
    FOREIGN KEY (artist_id) REFERENCES artist_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// ── COMMISSION_OPTION_ITEMS ─────────────────────────────────
$tables['commission_option_items'] = "CREATE TABLE IF NOT EXISTS commission_option_items (
    id          CHAR(36)      PRIMARY KEY,
    option_id   CHAR(36)      NOT NULL,
    label       VARCHAR(255)  NOT NULL,
    price_type  ENUM('fixed','percent') NOT NULL DEFAULT 'fixed',
    price_value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    is_default  TINYINT(1)    NOT NULL DEFAULT 0,
    sort_order  INT           NOT NULL DEFAULT 0,
    FOREIGN KEY (option_id) REFERENCES commission_options(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// ── ORDERS ───────────────────────────────────────────────────
$tables['orders'] = "CREATE TABLE IF NOT EXISTS orders (
    id                 CHAR(36)      PRIMARY KEY,
    buyer_id           CHAR(36)      NOT NULL,
    artist_id          CHAR(36)      NOT NULL,
    tier_id            CHAR(36)      NOT NULL,
    description        TEXT          DEFAULT NULL,
    reference_file_url VARCHAR(255)  DEFAULT NULL,
    notes              TEXT          DEFAULT NULL,
    tier_price         DECIMAL(12,2) NOT NULL,
    addon_total        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_price        DECIMAL(12,2) NOT NULL,
    selected_options   TEXT          DEFAULT NULL,
    character_count    INT           NOT NULL DEFAULT 1,
    is_nsfw            TINYINT(1)    NOT NULL DEFAULT 0,
    deadline           DATE          DEFAULT NULL,
    reference_files    TEXT          DEFAULT NULL,
    status             ENUM('pending','confirmed','in_progress','completed','cancelled')
                       NOT NULL DEFAULT 'pending',
    created_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id)  REFERENCES users(id)             ON DELETE CASCADE,
    FOREIGN KEY (artist_id) REFERENCES users(id)             ON DELETE CASCADE,
    FOREIGN KEY (tier_id)   REFERENCES commission_tiers(id)  ON DELETE RESTRICT
) ENGINE=InnoDB";

// ── ORDER_ADDONS ─────────────────────────────────────────────
$tables['order_addons'] = "CREATE TABLE IF NOT EXISTS order_addons (
    id             CHAR(36)      PRIMARY KEY,
    order_id       CHAR(36)      NOT NULL,
    addon_id       CHAR(36)      NOT NULL,
    price_snapshot DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)             ON DELETE CASCADE,
    FOREIGN KEY (addon_id) REFERENCES commission_addons(id)  ON DELETE RESTRICT
) ENGINE=InnoDB";

// ── CART_ITEMS ───────────────────────────────────────────────
$tables['cart_items'] = "CREATE TABLE IF NOT EXISTS cart_items (
    id       CHAR(36)  PRIMARY KEY,
    user_id  CHAR(36)  NOT NULL,
    order_id CHAR(36)  NOT NULL,
    added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id)  ON DELETE CASCADE
) ENGINE=InnoDB";

// ── PAYMENTS ─────────────────────────────────────────────────
$tables['payments'] = "CREATE TABLE IF NOT EXISTS payments (
    id              CHAR(36)      PRIMARY KEY,
    order_id        CHAR(36)      NOT NULL,
    user_id         CHAR(36)      NOT NULL,
    order_code      VARCHAR(100)  UNIQUE NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,
    platform_fee    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_paid      DECIMAL(12,2) NOT NULL,
    method          ENUM('gopay','dana','ovo','qris','bri','bca') NOT NULL,
    promo_code      VARCHAR(50)   DEFAULT NULL,
    status          ENUM('pending','success','failed','expired') NOT NULL DEFAULT 'pending',
    paid_at         TIMESTAMP     NULL DEFAULT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB";

// ── PROMO_CODES ──────────────────────────────────────────────
$tables['promo_codes'] = "CREATE TABLE IF NOT EXISTS promo_codes (
    id               CHAR(36)     PRIMARY KEY,
    code             VARCHAR(50)  UNIQUE NOT NULL,
    discount_percent INT          NOT NULL,
    is_active        TINYINT(1)   NOT NULL DEFAULT 1,
    expires_at       TIMESTAMP    NULL DEFAULT NULL
) ENGINE=InnoDB";

// ── MESSAGES ─────────────────────────────────────────────────
$tables['messages'] = "CREATE TABLE IF NOT EXISTS messages (
    id          CHAR(36)   PRIMARY KEY,
    sender_id   CHAR(36)   NOT NULL,
    receiver_id CHAR(36)   NOT NULL,
    content     TEXT       NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    sent_at     TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── NOTIFICATIONS ────────────────────────────────────────────
$tables['notifications'] = "CREATE TABLE IF NOT EXISTS notifications (
    id         CHAR(36)     PRIMARY KEY,
    user_id    CHAR(36)     NOT NULL,
    text       VARCHAR(255) NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    type       ENUM('new_post','order','like','follow','report','commission') NOT NULL,
    ref_id     CHAR(36)     DEFAULT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB";

// ── REPORTS ──────────────────────────────────────────────────
$tables['reports'] = "CREATE TABLE IF NOT EXISTS reports (
    id             CHAR(36)  PRIMARY KEY,
    reporter_id    CHAR(36)  NOT NULL,
    target_user_id CHAR(36)  DEFAULT NULL,
    target_post_id CHAR(36)  DEFAULT NULL,
    target_type    ENUM('post','account') NOT NULL,
    reason         ENUM('sensitive','hashtag','ai','harass','hate','misrep','other') NOT NULL,
    message        TEXT      NULL,
    status         ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (target_post_id) REFERENCES posts(id) ON DELETE SET NULL
) ENGINE=InnoDB";

// ── 3. Eksekusi semua tabel ───────────────────────────────────
echo '<div class="sep"></div><h3>📦 Membuat Tabel</h3>';
foreach ($tables as $name => $sql) {
    if ($conn->query($sql) === true) {
        echo "<p class='ok'>✅ <strong>$name</strong></p>";
    } else {
        echo "<p class='err'>❌ <strong>$name</strong>: " . htmlspecialchars($conn->error) . "</p>";
    }
}

// ── 4. Helper UUID ───────────────────────────────────────────
function gen_uuid(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// ── 5. Seed data demo ─────────────────────────────────────────
echo '<div class="sep"></div><h3>🌱 Seed Data Demo</h3>';

// ─── Users demo ───────────────────────────────────────────────
$demo_users = [
    // [username, email, password, role]
    ['admin',          'admin@galateart.id',          'admin123',    'admin'],
    ['ichigowarano',   'ichigo@galateart.id',         'artist123',   'artist'],
    ['jasper_xandros', 'jasper@galateart.id',         'artist123',   'artist'],
    ['keenbiscuit',    'keen@galateart.id',           'artist123',   'artist'],
    ['artis_lokal',    'artislokal@galateart.id',     'artist123',   'artist'],
    ['user_regular',   'user@galateart.id',           'user123',     'regular'],
];

$user_ids = []; // simpan id per username untuk FK berikutnya

foreach ($demo_users as [$uname, $email, $pass, $role]) {
    // Cek sudah ada belum
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $uname);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $id   = gen_uuid();
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $ins  = $conn->prepare(
            "INSERT INTO users (id, username, email, password_hash, role) VALUES (?,?,?,?,?)"
        );
        $ins->bind_param("sssss", $id, $uname, $email, $hash, $role);
        $ins->execute();
        $user_ids[$uname] = $id;
        echo "<p class='ok'>✅ User <strong>$uname</strong> ($role) ditambahkan. Pass: <code>$pass</code></p>";
        $ins->close();
    } else {
        // Ambil id yang sudah ada
        $get = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $get->bind_param("s", $uname);
        $get->execute();
        $get->bind_result($existing_id);
        $get->fetch();
        $user_ids[$uname] = $existing_id;
        $get->close();
        echo "<p style='color:#facc15;'>⚠️ User <strong>$uname</strong> sudah ada, dilewati.</p>";
    }
    $check->close();
}

// ─── Artist Profiles ──────────────────────────────────────────
$artists = ['ichigowarano', 'jasper_xandros', 'keenbiscuit', 'artis_lokal'];
$ap_ids  = []; // artist_profile id per username

foreach ($artists as $uname) {
    if (empty($user_ids[$uname])) continue;
    $uid = $user_ids[$uname];

    $check = $conn->prepare("SELECT id FROM artist_profiles WHERE user_id = ?");
    $check->bind_param("s", $uid);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $ap_id  = gen_uuid();
        $status = 'open';
        $days   = rand(5, 14);
        $tos    = "Bayar 50% di awal, 50% setelah sketch disetujui. Revisi sesuai tier.";
        $ins    = $conn->prepare(
            "INSERT INTO artist_profiles (id, user_id, commission_status, turnaround_days, tos)
             VALUES (?,?,?,?,?)"
        );
        $ins->bind_param("sssds", $ap_id, $uid, $status, $days, $tos);
        // Perbaiki tipe — days adalah int
        $ins->close();

        $ins2 = $conn->prepare(
            "INSERT INTO artist_profiles (id, user_id, commission_status, turnaround_days, tos)
             VALUES (?,?,?,?,?)"
        );
        $ins2->bind_param("sssis", $ap_id, $uid, $status, $days, $tos);
        $ins2->execute();
        $ap_ids[$uname] = $ap_id;
        echo "<p class='ok'>✅ Artist profile <strong>$uname</strong> dibuat.</p>";
        $ins2->close();
    } else {
        $get = $conn->prepare("SELECT id FROM artist_profiles WHERE user_id = ?");
        $get->bind_param("s", $uid);
        $get->execute();
        $get->bind_result($existing_ap);
        $get->fetch();
        $ap_ids[$uname] = $existing_ap;
        $get->close();
        echo "<p style='color:#facc15;'>⚠️ Artist profile <strong>$uname</strong> sudah ada.</p>";
    }
    $check->close();
}

// ─── Commission Tiers ─────────────────────────────────────────
$tier_data = [
    ['ichigowarano', 'Sketch',    'Sketsa Lineart',   'Sketsa hitam putih tanpa warna.',       150000, 1, 0],
    ['ichigowarano', 'Flat Color','Flat Color',        'Lineart dengan warna flat dasar.',      250000, 2, 0],
    ['ichigowarano', 'Full Color','Ilustrasi Penuh',   'Shading penuh, background sederhana.',  450000, 3, 1],
    ['artis_lokal',  'Chibi',     'Chibi Character',   'Karakter chibi lucu full color.',       200000, 2, 0],
    ['artis_lokal',  'Fullbody',  'Full Body Premium', 'Fullbody karakter dengan BG detail.',   600000, 3, 1],
];

$tier_ids = [];
foreach ($tier_data as [$uname, $tname, $label, $desc, $price, $rev, $com]) {
    if (empty($ap_ids[$uname])) continue;
    $ap_id = $ap_ids[$uname];

    $check = $conn->prepare("SELECT id FROM commission_tiers WHERE artist_id = ? AND name = ?");
    $check->bind_param("ss", $ap_id, $tname);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $tid = gen_uuid();
        $ins = $conn->prepare(
            "INSERT INTO commission_tiers (id, artist_id, name, label, description, price, revision_count, commercial_use)
             VALUES (?,?,?,?,?,?,?,?)"
        );
        $ins->bind_param("sssssdii", $tid, $ap_id, $tname, $label, $desc, $price, $rev, $com);
        $ins->execute();
        $tier_ids["$uname-$tname"] = $tid;
        echo "<p class='ok'>✅ Tier <strong>$tname</strong> untuk $uname ditambahkan.</p>";
        $ins->close();
    } else {
        echo "<p style='color:#facc15;'>⚠️ Tier <strong>$tname</strong> $uname sudah ada.</p>";
    }
    $check->close();
}

// ─── Commission Addons ────────────────────────────────────────
$addon_data = [
    ['ichigowarano', 'Extra Character', 150000, 'extra_char'],
    ['ichigowarano', 'Background Detail', 100000, 'bg_detail'],
    ['ichigowarano', 'Speedpaint Video', 75000, 'speedpaint'],
    ['artis_lokal',  'Extra Character', 200000, 'extra_char'],
    ['artis_lokal',  'Priority Queue',  100000, 'priority'],
];

foreach ($addon_data as [$uname, $alabel, $aprice, $atype]) {
    if (empty($ap_ids[$uname])) continue;
    $ap_id = $ap_ids[$uname];

    $check = $conn->prepare("SELECT id FROM commission_addons WHERE artist_id = ? AND label = ?");
    $check->bind_param("ss", $ap_id, $alabel);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $aid = gen_uuid();
        $ins = $conn->prepare(
            "INSERT INTO commission_addons (id, artist_id, label, price, type) VALUES (?,?,?,?,?)"
        );
        $ins->bind_param("sssds", $aid, $ap_id, $alabel, $aprice, $atype);
        $ins->execute();
        echo "<p class='ok'>✅ Addon <strong>$alabel</strong> untuk $uname ditambahkan.</p>";
        $ins->close();
    } else {
        echo "<p style='color:#facc15;'>⚠️ Addon <strong>$alabel</strong> $uname sudah ada.</p>";
    }
    $check->close();
}

// ─── Commission Options (Dynamic) ────────────────────────────
// Seed opsi commission dinamis untuk artist demo
$commission_options_data = [
    // [artist_username, category, description, selection_type, is_required, sort_order, items[]]
    // items: [label, price_type, price_value, is_default, sort_order]
    ['artis_lokal', 'Canvas Size', 'Pilih ukuran canvas yang diinginkan', 'single', 1, 1, [
        ['Portrait', 'fixed', 0, 1, 1],
        ['Square', 'fixed', 0, 0, 2],
        ['Banner', 'fixed', 358660, 0, 3],
        ['Landscape 1920x1080', 'fixed', 358660, 0, 4],
        ['Landscape 2K', 'fixed', 448325, 0, 5],
        ['Landscape 4K', 'fixed', 537990, 0, 6],
    ]],
    ['artis_lokal', 'License Type', 'Pilih lisensi yang dibutuhkan', 'multiple', 1, 2, [
        ['Personal', 'fixed', 0, 1, 1],
        ['Monetized Content', 'percent', 50, 0, 2],
        ['Commercial Merchandising', 'percent', 100, 0, 3],
    ]],
    ['artis_lokal', 'Background', 'Pilih tipe background', 'single', 1, 3, [
        ['Transparent / Single Color Background (FREE)', 'fixed', 0, 1, 1],
        ['Simple Background', 'fixed', 358660, 0, 2],
        ['Complex Background', 'fixed', 717320, 0, 3],
        ['Very Detailed + Lot of Objects', 'fixed', 1434640, 0, 4],
    ]],
    ['artis_lokal', 'NSFW', 'Apakah ini konten NSFW? Jika tidak ada bagian privat yang terlihat, maka bukan NSFW.', 'single', 1, 4, [
        ['No', 'fixed', 0, 1, 1],
        ['Yes', 'fixed', 358660, 0, 2],
    ]],
    ['artis_lokal', 'Streaming Permission', 'Bolehkah karya ini dipublikasikan / di-stream dengan credit?', 'single', 1, 5, [
        ['Yes (Free)', 'fixed', 0, 1, 1],
        ['Not while WIP but final work is ok (Free)', 'fixed', 0, 0, 2],
        ['Few days / week after I post (Free)', 'fixed', 0, 0, 3],
        ['NDA required (Do not post Forever)', 'fixed', 896650, 0, 4],
        ['Delete all data about me and my commission / anonymous', 'fixed', 1793300, 0, 5],
    ]],
    ['artis_lokal', 'Add-on', 'Layanan tambahan opsional', 'multiple', 0, 6, [
        ['No, I dont need any of this.', 'fixed', 0, 1, 1],
        ['PSD Files (Some will be merged) (Free)', 'fixed', 0, 0, 2],
        ['PSD Files (DO NOT MERGE ANYTHING)', 'fixed', 358660, 0, 3],
        ['Layered, Ready for animated (Only Character)', 'fixed', 717320, 0, 4],
        ['Layered, Ready for animated (Only Background)', 'fixed', 717320, 0, 5],
        ['Layered, Ready for animated (Character & Background)', 'fixed', 1434640, 0, 6],
    ]],
    // ichigowarano options
    ['ichigowarano', 'Canvas Size', 'Pilih ukuran canvas', 'single', 1, 1, [
        ['Portrait', 'fixed', 0, 1, 1],
        ['Square', 'fixed', 0, 0, 2],
        ['Landscape 1920x1080', 'fixed', 200000, 0, 3],
    ]],
    ['ichigowarano', 'License Type', 'Pilih lisensi penggunaan', 'multiple', 1, 2, [
        ['Personal', 'fixed', 0, 1, 1],
        ['Monetized Content', 'percent', 50, 0, 2],
        ['Commercial Merchandising', 'percent', 100, 0, 3],
    ]],
    ['ichigowarano', 'Background', 'Pilih tipe background', 'single', 1, 3, [
        ['Transparent / Single Color (FREE)', 'fixed', 0, 1, 1],
        ['Simple Background', 'fixed', 200000, 0, 2],
        ['Complex Background', 'fixed', 400000, 0, 3],
    ]],
    ['ichigowarano', 'NSFW', 'Konten NSFW?', 'single', 1, 4, [
        ['No', 'fixed', 0, 1, 1],
        ['Yes', 'fixed', 200000, 0, 2],
    ]],
];

foreach ($commission_options_data as [$uname, $cat, $desc, $selType, $isReq, $sortOrd, $items]) {
    if (empty($ap_ids[$uname])) continue;
    $ap_id = $ap_ids[$uname];

    // Check if this option already exists
    $check = $conn->prepare("SELECT id FROM commission_options WHERE artist_id = ? AND category = ?");
    $check->bind_param("ss", $ap_id, $cat);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $optId = gen_uuid();
        $ins = $conn->prepare(
            "INSERT INTO commission_options (id, artist_id, category, description, selection_type, is_required, sort_order)
             VALUES (?,?,?,?,?,?,?)"
        );
        $ins->bind_param("sssssii", $optId, $ap_id, $cat, $desc, $selType, $isReq, $sortOrd);
        $ins->execute();
        $ins->close();

        // Insert items
        foreach ($items as [$label, $priceType, $priceVal, $isDef, $itemSort]) {
            $itemId = gen_uuid();
            $insItem = $conn->prepare(
                "INSERT INTO commission_option_items (id, option_id, label, price_type, price_value, is_default, sort_order)
                 VALUES (?,?,?,?,?,?,?)"
            );
            $insItem->bind_param("ssssdii", $itemId, $optId, $label, $priceType, $priceVal, $isDef, $itemSort);
            $insItem->execute();
            $insItem->close();
        }
        echo "<p class='ok'>✅ Commission option <strong>$cat</strong> untuk $uname ditambahkan.</p>";
    } else {
        echo "<p style='color:#facc15;'>⚠️ Commission option <strong>$cat</strong> $uname sudah ada.</p>";
    }
    $check->close();
}

// ─── Promo Codes ──────────────────────────────────────────────
$promos = [
    ['GALATE10',  10, '2026-12-31 23:59:59'],
    ['NEWUSER20', 20, '2026-12-31 23:59:59'],
    ['ARTFEST',   15, '2026-08-17 23:59:59'],
];

foreach ($promos as [$code, $pct, $exp]) {
    $check = $conn->prepare("SELECT id FROM promo_codes WHERE code = ?");
    $check->bind_param("s", $code);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $pid = gen_uuid();
        $ins = $conn->prepare(
            "INSERT INTO promo_codes (id, code, discount_percent, expires_at) VALUES (?,?,?,?)"
        );
        $ins->bind_param("ssis", $pid, $code, $pct, $exp);
        $ins->execute();
        echo "<p class='ok'>✅ Promo <strong>$code</strong> ({$pct}%) ditambahkan.</p>";
        $ins->close();
    } else {
        echo "<p style='color:#facc15;'>⚠️ Promo <strong>$code</strong> sudah ada.</p>";
    }
    $check->close();
}

// ── Selesai ───────────────────────────────────────────────────
$conn->close();

echo '
<div class="sep"></div>
<h3 class="ok">🎉 Setup selesai!</h3>
<p><strong>Akun demo:</strong></p>
<table style="border-collapse:collapse;font-size:13px;width:100%;">
  <tr style="color:#a78bfa;">
    <th style="text-align:left;padding:4px 12px;">Username</th>
    <th style="text-align:left;padding:4px 12px;">Password</th>
    <th style="text-align:left;padding:4px 12px;">Role</th>
  </tr>
  <tr><td style="padding:4px 12px;">admin</td>         <td style="padding:4px 12px;">admin123</td>  <td style="padding:4px 12px;">admin</td></tr>
  <tr><td style="padding:4px 12px;">ichigowarano</td>  <td style="padding:4px 12px;">artist123</td> <td style="padding:4px 12px;">artist</td></tr>
  <tr><td style="padding:4px 12px;">artis_lokal</td>   <td style="padding:4px 12px;">artist123</td> <td style="padding:4px 12px;">artist</td></tr>
  <tr><td style="padding:4px 12px;">user_regular</td>  <td style="padding:4px 12px;">user123</td>   <td style="padding:4px 12px;">regular</td></tr>
</table>
<div class="sep"></div>
<p>⚠️ <strong>Hapus atau proteksi file ini setelah setup selesai!</strong></p>
<p>Langkah berikutnya: buat <code>api/auth.php</code> untuk login & register.</p>
</div>';