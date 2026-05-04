// ==========================================
// 1. ELEMEN NAVIGASI & MENU
// ==========================================
const menuToggle = document.getElementById('menuToggle');
const dropdownMenu = document.getElementById('dropdownMenu');

// Pastikan elemen ada sebelum menambahkan event (Mencegah error di halaman profil)
if (menuToggle && dropdownMenu) {
    menuToggle.addEventListener('click', function(event) {
        dropdownMenu.classList.toggle('show');
        event.stopPropagation();
    });
}

// Tutup menu saat klik area luar
document.addEventListener('click', function(event) {
    if (dropdownMenu && menuToggle) {
        if (!dropdownMenu.contains(event.target) && !menuToggle.contains(event.target)) {
            dropdownMenu.classList.remove('show');
        }
    }
});

// ==========================================
// 2. MODAL REGISTRASI (USER & ARTIST)
// ==========================================
const btnSignup = document.getElementById('btnSignup');
const registerModal = document.getElementById('registerModal');
const closeModal = document.getElementById('closeModal');

const btnArtistNav = document.getElementById('btnArtist');
const artistModal = document.getElementById('artistModal');
const closeArtistModal = document.getElementById('closeArtistModal');
const userProfileLink = document.getElementById('userProfileLink');
const btnLogin = document.getElementById('btnLogin');
const loginModal = document.getElementById('loginModal');
const closeLoginModal = document.getElementById('closeLoginModal');
const loginForm = document.getElementById('loginForm');
const switchToSignup = document.getElementById('switchToSignup');

// Buka/Tutup Modal User
if (btnSignup && registerModal) {
    btnSignup.addEventListener('click', () => registerModal.classList.add('show'));
}
if (closeModal && registerModal) {
    closeModal.addEventListener('click', () => registerModal.classList.remove('show'));
}

// Buka/Tutup Modal Artis
if (btnArtistNav && artistModal) {
    btnArtistNav.addEventListener('click', () => artistModal.classList.add('show'));
}
if (closeArtistModal && artistModal) {
    closeArtistModal.addEventListener('click', () => artistModal.classList.remove('show'));
}

// Tutup modal jika klik area gelap
window.addEventListener('click', function(event) {
    if (registerModal && event.target === registerModal) registerModal.classList.remove('show');
    if (artistModal && event.target === artistModal) artistModal.classList.remove('show');
});


// ==========================================
// 3. LOGIKA SIMULASI PENDAFTARAN
// ==========================================
const userForm = document.querySelector('#registerModal form');
const artistForm = document.querySelector('#artistModal form');

function setLoggedInState(isArtist = false) {
    if (btnSignup) btnSignup.style.display = 'none'; 
    if (btnArtistNav) btnArtistNav.style.display = 'none'; 
    // Sembunyikan tombol login saat sudah masuk
    if (btnLogin) btnLogin.style.display = 'none'; 
    
    // Logika penampilan ikon dan profil tetap sama
    const navIcons = document.querySelector('.nav-icons');
    if (navIcons) navIcons.style.display = 'flex';
    
    if (userProfileLink) {
        userProfileLink.style.display = 'flex';
    }
}

// Cek status login saat memuat halaman utama (landing page)
window.addEventListener('DOMContentLoaded', () => {
    const savedRole = localStorage.getItem('userRole');
    if (savedRole && userProfileLink) {
        setLoggedInState(savedRole === 'artist');
    }

    // Inisialisasi notifikasi setelah DOM ready
    const notifToggle = document.getElementById('notifToggle');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifBody = document.getElementById('notifBody');

    if (notifToggle && notifDropdown) {
        notifToggle.addEventListener('click', (e) => {
            notifDropdown.classList.toggle('show');
            if (dropdownMenu) dropdownMenu.classList.remove('show'); // Tutup kategori jika terbuka
            e.stopPropagation();
        });
        
        // Tutup dropdown notifikasi saat klik di luar
        document.addEventListener('click', (e) => {
            if (!notifDropdown.contains(e.target) && !notifToggle.contains(e.target)) {
                notifDropdown.classList.remove('show');
            }
        });
    }

    renderNotifications();
});

if (userForm) {
    userForm.addEventListener('submit', function(e) {
        e.preventDefault(); 
        alert('Pendaftaran Berhasil! Selamat datang di GalateArt.');
        if (registerModal) registerModal.classList.remove('show'); 
        setLoggedInState(false); 
        
        // Simpan peran sebagai regular di browser
        localStorage.setItem('userRole', 'regular'); 
    });
}

if (artistForm) {
    artistForm.addEventListener('submit', function(e) {
        e.preventDefault(); 
        alert('Pendaftaran Artist Berhasil! Portofolio Anda sedang ditinjau.');
        if (artistModal) artistModal.classList.remove('show'); 
        setLoggedInState(true); 
        
        // Simpan peran sebagai artist di browser
        localStorage.setItem('userRole', 'artist'); 
    });
}


// ==========================================
// 4. SISTEM NOTIFIKASI
// ==========================================
const notifications = [
    { id: 1, text: "<strong>@artis_lokal</strong> mengunggah karya baru!", time: "2 menit lalu" },
    { id: 2, text: "Pesanan aset digital kamu telah selesai.", time: "1 jam lalu" },
    { id: 3, text: "Seseorang menyukai karya Anda.", time: "3 jam lalu" }
];

function renderNotifications() {
    const notifBody = document.getElementById('notifBody');
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


// ==========================================
// 5. SCRIPT KHUSUS HALAMAN PROFIL
// ==========================================

// A. Fungsi Ganti Tab di Profil
function switchTab(clickedBtn, targetContentId) {
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(tab => tab.classList.remove('active'));

    clickedBtn.classList.add('active');

    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.remove('active'));

    const targetContent = document.getElementById(targetContentId);
    if (targetContent) {
        targetContent.classList.add('active');
    }
}

// B. Auto-Setup Role Profil saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const profileWrapper = document.getElementById('profileWrapper');
    const accountBadgeText = document.getElementById('accountBadgeText');
    
    // Pastikan kita berada di halaman profil
    if (profileWrapper) {
        // Ambil data peran dari pendaftaran sebelumnya. Jika tidak ada, anggap 'regular'
        const userRole = localStorage.getItem('userRole') || 'regular';

        if (userRole === 'artist') {
            // Tampilan Artis
            profileWrapper.classList.remove('is-regular');
            profileWrapper.classList.add('is-artist');
            if (accountBadgeText) accountBadgeText.innerText = 'Artist Account';
        } else {
            // Tampilan Regular User
            profileWrapper.classList.remove('is-artist');
            profileWrapper.classList.add('is-regular');
            if (accountBadgeText) accountBadgeText.innerText = 'Regular Account';
            
            // Otomatis pindah ke tab Bio, karena tab Posts disembunyikan untuk regular
            const bioTabBtn = document.querySelector('.profile-tabs button:nth-child(1)');
            if (bioTabBtn) switchTab(bioTabBtn, 'content-bio');
        }
    }
});

// ==========================================
// 6. LOGIKA LOGOUT
// ==========================================
const btnLogout = document.getElementById('btnLogout');

if (btnLogout) {
    btnLogout.addEventListener('click', function() {
        // Konfirmasi sebelum logout (Opsional)
        const yakin = confirm("Apakah Anda yakin ingin keluar?");
        
        if (yakin) {
            // 1. Hapus data dari localStorage
            localStorage.removeItem('userRole');
            
            // 2. Arahkan kembali ke halaman landing
            // Gunakan window.location.href untuk berpindah halaman
            window.location.href = 'landing.html';
        }
    });
}

// Buka/Tutup Modal Masuk
if (btnLogin && loginModal) {
    btnLogin.addEventListener('click', () => loginModal.classList.add('show'));
}
if (closeLoginModal) {
    closeLoginModal.addEventListener('click', () => loginModal.classList.remove('show'));
}

// Berpindah dari Modal Masuk ke Daftar
if (switchToSignup) {
    switchToSignup.addEventListener('click', (e) => {
        e.preventDefault();
        loginModal.classList.remove('show');
        registerModal.classList.add('show');
    });
}

// Tutup modal jika klik area luar (perbarui event listener yang sudah ada)
window.addEventListener('click', function(event) {
    if (event.target === registerModal) registerModal.classList.remove('show');
    if (event.target === artistModal) artistModal.classList.remove('show');
    if (event.target === loginModal) loginModal.classList.remove('show'); // Tambahan
});

// Simulasi Submit Login
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Berhasil Masuk!');
        loginModal.classList.remove('show');
        setLoggedInState(false); 
        localStorage.setItem('userRole', 'regular'); 
    });
}