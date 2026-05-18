// ── Kredensial admin (simpan di localStorage agar bisa diubah) ──
const DEFAULT_ADMIN = { username: 'admin', password: 'admin123' };

function getAdminCreds() {
  const saved = localStorage.getItem('ga_admin_creds');
  return saved ? JSON.parse(saved) : DEFAULT_ADMIN;
}

// Jika sudah login, langsung redirect
if (sessionStorage.getItem('ga_admin_auth') === 'true') {
  location.href = 'admin.html';
}

// Toggle tampilkan/sembunyikan password
document.getElementById('togglePw').addEventListener('click', function () {
  const inp = document.getElementById('adminPass');
  const ico = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    ico.className = 'fas fa-eye-slash';
  } else {
    inp.type = 'password';
    ico.className = 'fas fa-eye';
  }
});

function showError(msg) {
  const box = document.getElementById('errorMsg');
  document.getElementById('errorText').textContent = msg;
  box.classList.add('show');
  document.getElementById('adminUser').classList.add('error');
  document.getElementById('adminPass').classList.add('error');
}

function clearError() {
  document.getElementById('errorMsg').classList.remove('show');
  document.getElementById('adminUser').classList.remove('error');
  document.getElementById('adminPass').classList.remove('error');
}

function doLogin() {
  clearError();
  const user = document.getElementById('adminUser').value.trim();
  const pass = document.getElementById('adminPass').value;

  if (!user || !pass) {
    showError('Username dan kata sandi tidak boleh kosong.');
    return;
  }

  // Tampilkan loading
  const btn = document.getElementById('btnLogin');
  document.getElementById('btnText').textContent = 'Memeriksa...';
  document.getElementById('spinner').style.display = 'block';
  btn.disabled = true;

  // Simulasi delay autentikasi
  setTimeout(() => {
    const creds = getAdminCreds();
    if (user === creds.username && pass === creds.password) {
      sessionStorage.setItem('ga_admin_auth', 'true');
      sessionStorage.setItem('ga_admin_user', user);
      location.href = 'admin.html';
    } else {
      document.getElementById('btnText').textContent = 'Masuk ke Admin Panel';
      document.getElementById('spinner').style.display = 'none';
      btn.disabled = false;
      showError('Username atau kata sandi salah.');
      document.getElementById('adminPass').value = '';
      document.getElementById('adminPass').focus();
    }
  }, 700);
}

document.getElementById('btnLogin').addEventListener('click', doLogin);

// Enter untuk submit
['adminUser', 'adminPass'].forEach(id => {
  document.getElementById(id).addEventListener('keydown', e => {
    if (e.key === 'Enter') doLogin();
  });
});