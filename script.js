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