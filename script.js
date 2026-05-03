// Mengambil elemen dari DOM
const btnSignup = document.getElementById('btnSignup');
const registerModal = document.getElementById('registerModal');
const closeModal = document.getElementById('closeModal');
const menuToggle = document.getElementById('menuToggle');
const dropdownMenu = document.getElementById('dropdownMenu');

// Event listener saat ikon diklik
menuToggle.addEventListener('click', function(event) {
    dropdownMenu.classList.toggle('show');
    event.stopPropagation(); // Mencegah event klik menjalar ke document
});

// Event listener untuk menutup menu jika user mengklik area luar menu
document.addEventListener('click', function(event) {
    if (!dropdownMenu.contains(event.target) && !menuToggle.contains(event.target)) {
        dropdownMenu.classList.remove('show');
    }
});

// --- Fungsi Modal Register ---

// Buka modal saat tombol "Daftar" diklik
btnSignup.addEventListener('click', function() {
    registerModal.classList.add('show');
});

// Tutup modal saat tombol "X" diklik
closeModal.addEventListener('click', function() {
    registerModal.classList.remove('show');
});

// Tutup modal jika user mengklik area gelap di luar form
window.addEventListener('click', function(event) {
    if (event.target === registerModal) {
        registerModal.classList.remove('show');
    }
});

// 1. Data Notifikasi (8 Item)
const notifications = [
    { id: 1, text: "<strong>@artis_lokal</strong> mengunggah karya baru!", time: "2 menit lalu" },
    { id: 2, text: "Pesanan aset digital kamu telah selesai.", time: "1 jam lalu" },
    { id: 3, text: "Seseorang menyukai karya Anda.", time: "3 jam lalu" },
    { id: 4, text: "Komentar baru pada postingan Anda.", time: "5 jam lalu" },
    { id: 5, text: "Update sistem GalateArt v1.2.", time: "Yesterday" },
    { id: 6, text: "Promo khusus aset 3D bulan ini!", time: "2 days ago" },
    { id: 7, text: "Verifikasi akun Anda berhasil.", time: "3 days ago" },
    { id: 8, text: "Selamat datang di GalateArt!", time: "1 week ago" }
];

// 2. Ambil elemen DOM
const notifToggle = document.getElementById('notifToggle');
const notifDropdown = document.getElementById('notifDropdown');
const notifBody = document.getElementById('notifBody');

function renderNotifications() {
    if (!notifBody) return;
    notifBody.innerHTML = ''; 

    if (notifications.length === 0) {
        notifBody.innerHTML = `
            <div class="empty-notif" style="padding:30px; text-align:center; color:#b3b3b3;">
                <i class="far fa-bell-slash" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                <p>Tidak ada notifikasi</p>
            </div>`;
    } else {
        notifications.forEach(item => {
            const div = document.createElement('div');
            div.className = 'notif-item';
            div.innerHTML = `<p>${item.text}</p><span>${item.time}</span>`;
            notifBody.appendChild(div);
        });
    }
}

// 4. Event Listener Klik Lonceng
if (notifToggle && notifDropdown) {
    notifToggle.addEventListener('click', (e) => {
        notifDropdown.classList.toggle('show');
        if (dropdownMenu) dropdownMenu.classList.remove('show'); // Tutup menu kategori jika terbuka
        e.stopPropagation();
    });
}

// 5. Update Event Klik Dokumen (Agar klik luar menutup segalanya)
document.addEventListener('click', function(event) {
    // Tutup Menu Kategori
    if (dropdownMenu && menuToggle && !dropdownMenu.contains(event.target) && !menuToggle.contains(event.target)) {
        dropdownMenu.classList.remove('show');
    }
    // Tutup Dropdown Notifikasi
    if (notifDropdown && notifToggle && !notifDropdown.contains(event.target) && !notifToggle.contains(event.target)) {
        notifDropdown.classList.remove('show');
    }
});

// Jalankan render saat awal
renderNotifications();

// ==========================================
// SCRIPT KHUSUS HALAMAN PROFIL
// ==========================================

// Fungsi Ganti Tab di Profil
// Fungsi Ganti Tab di Profil
function switchTab(clickedBtn, targetContentId) {
    // 1. Hapus class 'active' dari semua tombol tab
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(tab => tab.classList.remove('active'));

    // 2. Tambahkan class 'active' ke tombol yang baru saja diklik
    clickedBtn.classList.add('active');

    // 3. Sembunyikan semua isi konten tab
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));

    // 4. Tampilkan isi konten yang sesuai dengan tombol yang diklik
    const targetContent = document.getElementById(targetContentId);
    if (targetContent) {
        targetContent.classList.add('active');
    }
}