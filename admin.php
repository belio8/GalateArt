<?php
require_once __DIR__ . '/components/bootstrap.php';
require_login('admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - GalateArt</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body class="ga-adm-page">

<!-- SIDEBAR -->
<aside class="ga-adm-sidebar">
  <div class="ga-adm-sidebar-logo">Galate<span>Art</span><small>Admin Panel</small></div>
  <nav>
    <div class="ga-adm-nav-group-label">Overview</div>
    <button class="ga-adm-nav-item active" data-page="dashboard"><i class="fas fa-th-large"></i> Dashboard</button>

    <div class="ga-adm-nav-group-label">Moderasi</div>
    <button class="ga-adm-nav-item" data-page="reports"><i class="fas fa-flag"></i> Laporan <span class="ga-adm-badge" id="ga-adm-report-badge">0</span></button>
    <button class="ga-adm-nav-item" data-page="posts"><i class="fas fa-images"></i> Postingan</button>
    <button class="ga-adm-nav-item" data-page="accounts"><i class="fas fa-users"></i> Akun</button>
  </nav>
  <div class="ga-adm-sidebar-footer">
    <form action="api/auth.php" method="post" style="display:inline;">
        <input type="hidden" name="action" value="logout">
        <div class="logout-container">
            <button id="btnLogout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </form>
</aside>

<!-- MAIN -->
<div class="ga-adm-main">
  <div class="ga-adm-topbar">
    <h1 id="ga-adm-page-title">Dashboard</h1>
    <div class="ga-adm-topbar-right">
      <span style="font-size:12px;color:var(--ga-adm-muted)">Admin</span>
      <div class="ga-adm-avatar">A</div>
    </div>
  </div>

  <div class="ga-adm-content">

    <!-- ── DASHBOARD ── -->
    <section class="ga-adm-page-section active" id="page-dashboard">
      <div class="ga-adm-stat-grid">
        <div class="ga-adm-stat-card warn">
          <div class="ga-adm-label"><i class="fas fa-flag"></i> Laporan Pending</div>
          <div class="ga-adm-value" id="ga-adm-stat-pending">0</div>
          <div class="ga-adm-sub">Menunggu tinjauan admin</div>
        </div>
        <div class="ga-adm-stat-card green">
          <div class="ga-adm-label"><i class="fas fa-check"></i> Laporan Disetujui</div>
          <div class="ga-adm-value" id="ga-adm-stat-approved">0</div>
          <div class="ga-adm-sub">Tindakan telah diambil</div>
        </div>
        <div class="ga-adm-stat-card accent">
          <div class="ga-adm-label"><i class="fas fa-images"></i> Total Postingan</div>
          <div class="ga-adm-value" id="ga-adm-stat-posts">0</div>
          <div class="ga-adm-sub">Di seluruh platform</div>
        </div>
        <div class="ga-adm-stat-card red">
          <div class="ga-adm-label"><i class="fas fa-users"></i> Total Akun</div>
          <div class="ga-adm-value" id="ga-adm-stat-accounts">0</div>
          <div class="ga-adm-sub">Pengguna terdaftar</div>
        </div>
      </div>

      <!-- Recent reports preview -->
      <div class="ga-adm-section-header">
        <h2>Laporan Terbaru</h2>
        <button class="ga-adm-filter-btn" onclick="showPage('reports')">Lihat Semua</button>
      </div>
      <div class="ga-adm-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Target</th><th>Alasan</th><th>Tipe</th><th>Status</th><th>Waktu</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody id="ga-adm-dash-report-tbody"></tbody>
        </table>
      </div>
    </section>

    <!-- ── REPORTS ── -->
    <section class="ga-adm-page-section" id="page-reports">
      <div class="ga-adm-section-header">
        <h2>Semua Laporan</h2>
        <div class="ga-adm-filters">
          <button class="ga-adm-filter-btn active" data-filter="all" onclick="filterReports(this)">Semua</button>
          <button class="ga-adm-filter-btn" data-filter="pending" onclick="filterReports(this)">Pending</button>
          <button class="ga-adm-filter-btn" data-filter="approved" onclick="filterReports(this)">Disetujui</button>
          <button class="ga-adm-filter-btn" data-filter="rejected" onclick="filterReports(this)">Ditolak</button>
        </div>
      </div>
      <div class="ga-adm-table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Target</th><th>Alasan</th><th>Tipe</th><th>Status</th><th>Waktu</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody id="ga-adm-report-tbody"></tbody>
        </table>
      </div>
    </section>

    <!-- ── POSTS ── -->
    <section class="ga-adm-page-section" id="page-posts">
      <div class="ga-adm-section-header">
        <h2>Manajemen Postingan</h2>
        <div style="display:flex;align-items:center;gap:12px">
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--ga-adm-muted);cursor:pointer;">
            <input type="checkbox" id="ga-adm-show-removed" onchange="loadPosts()" style="accent-color:var(--ga-adm-accent);cursor:pointer;"> Tampilkan Removed
          </label>
          <input class="ga-adm-search-input" type="text" placeholder="Cari postingan..." oninput="filterTable(this.value,'ga-adm-post-tbody')">
        </div>
      </div>
      <div class="ga-adm-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Postingan</th><th>Artis</th><th>Tags</th><th>Likes</th><th>Status</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody id="ga-adm-post-tbody"></tbody>
        </table>
      </div>
    </section>

    <!-- ── ACCOUNTS ── -->
    <section class="ga-adm-page-section" id="page-accounts">
      <div class="ga-adm-section-header">
        <h2>Manajemen Akun</h2>
        <input class="ga-adm-search-input" type="text" placeholder="Cari akun..." oninput="filterTable(this.value,'ga-adm-account-tbody')">
      </div>
      <div class="ga-adm-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Pengguna</th><th>Tipe</th><th>Postingan</th><th>Followers</th><th>Status</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody id="ga-adm-account-tbody"></tbody>
        </table>
      </div>
    </section>

  </div><!-- /content -->
</div><!-- /main -->

<!-- DRAWER -->
<div id="ga-adm-drawer-overlay">
  <div id="ga-adm-drawer">
    <button class="ga-adm-drawer-close" onclick="closeDrawer()"><i class="fas fa-times"></i></button>
    <div class="ga-adm-drawer-title" id="ga-adm-drawer-title-el">Detail</div>
    <div id="ga-adm-drawer-body"></div>
    <div class="ga-adm-drawer-actions" id="ga-adm-drawer-actions-el"></div>
  </div>
</div>

<!-- TOAST -->
<div id="ga-adm-toast"></div>
<script src="js/utils.js"></script>
<script src="js/auth.js"></script>
<script>
/* ========================================================================
   API HELPER
======================================================================== */
const API_BASE = 'api/admin.php';

async function api(action, params = {}, method = 'GET') {
  try {
    let url = API_BASE;
    let opts = { headers: { 'Content-Type': 'application/json' } };

    if (method === 'GET') {
      const qs = new URLSearchParams({ action, ...params }).toString();
      url += '?' + qs;
      opts.method = 'GET';
    } else {
      opts.method = 'POST';
      opts.body = JSON.stringify({ action, ...params });
    }

    const res = await fetch(url, opts);
    const json = await res.json();
    if (!res.ok || json.status === 'error') {
      throw new Error(json.message || 'Terjadi kesalahan.');
    }
    return json;
  } catch (err) {
    console.error(`API [${action}]:`, err);
    throw err;
  }
}

/* ========================================================================
   CONSTANTS
======================================================================== */
const REASON_LABELS = {
  sensitive: 'Unmarked sensitive content',
  hashtag:   'Misused hashtags / category',
  ai:        'AI / tracing / scam / fraud',
  harass:    'Harassment / doxxing / threats',
  hate:      'Incites hate, violence or self-harm',
  misrep:    'Misrepresentation',
  other:     'Something else',
};

/* ========================================================================
   IN-MEMORY DATA CACHE (filled from DB)
======================================================================== */
let reports  = [];
let posts    = [];
let accounts = [];

/* ========================================================================
   NAVIGATION
======================================================================== */
const PAGE_TITLES = { dashboard:'Dashboard', reports:'Laporan', posts:'Postingan', accounts:'Akun' };

function showPage(name) {
  document.querySelectorAll('.ga-adm-page-section').forEach(s => s.classList.remove('active'));
  document.getElementById('page-'+name).classList.add('active');
  document.querySelectorAll('.ga-adm-nav-item[data-page]').forEach(b => b.classList.remove('active'));
  const btn = document.querySelector(`.ga-adm-nav-item[data-page="${name}"]`);
  if (btn) btn.classList.add('active');
  document.getElementById('ga-adm-page-title').textContent = PAGE_TITLES[name] || name;
  loadAll();
}

document.querySelectorAll('.ga-adm-nav-item[data-page]').forEach(btn => {
  btn.addEventListener('click', () => showPage(btn.dataset.page));
});

/* ========================================================================
   RENDER HELPERS
======================================================================== */
function timeAgo(iso) {
  const d = (Date.now() - new Date(iso)) / 1000;
  if (d < 60)    return `${~~d}d lalu`;
  if (d < 3600)  return `${~~(d/60)}m lalu`;
  if (d < 86400) return `${~~(d/3600)}j lalu`;
  return `${~~(d/86400)}h lalu`;
}

function badgeStatus(s) {
  const map = { pending:'Pending', approved:'Disetujui', rejected:'Ditolak', active:'Aktif', banned:'Banned', artist:'Artist', user:'User', admin:'Admin', flagged:'Ditandai', removed:'Dihapus' };
  return `<span class="ga-adm-badge-status ${s}">${map[s]||s}</span>`;
}

/* ========================================================================
   REPORTS
======================================================================== */
let reportFilter = 'all';

function filterReports(btn) {
  document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  reportFilter = btn.dataset.filter;
  loadReports();
}

function reportRow(r, bodyId) {
  const reasonLabel = REASON_LABELS[r.reason] || r.reason;
  const short = reasonLabel.length > 32 ? reasonLabel.substring(0,32)+'...' : reasonLabel;
  const idShort = typeof r.id === 'string' ? r.id.substring(0,8) : r.id;
  return `<tr>
    ${bodyId === 'ga-adm-report-tbody' ? `<td style="color:var(--ga-adm-muted);font-size:12px">${escapeHtml(String(idShort))}</td>` : ''}
    <td><strong>${escapeHtml(r.targetTitle)}</strong></td>
    <td title="${escapeHtml(reasonLabel)}" style="color:var(--ga-adm-muted);font-size:12px">${escapeHtml(short)}</td>
    <td>${r.type === 'post' ? '<i class="fas fa-image" style="color:var(--ga-adm-accent)"></i> Post' : '<i class="fas fa-user" style="color:var(--ga-adm-warn)"></i> Akun'}</td>
    <td>${badgeStatus(r.status)}</td>
    <td style="color:var(--ga-adm-muted);font-size:12px">${timeAgo(r.createdAt)}</td>
    <td>
      <div class="ga-adm-action-btns">
        <button class="ga-adm-btn-sm ga-adm-btn-view" onclick="viewReport('${escapeHtml(r.id)}')">Detail</button>
        ${r.status==='pending' ? `
          <button class="ga-adm-btn-sm ga-adm-btn-approve" onclick="updateReport('${escapeHtml(r.id)}','approved')">Setuju</button>
          <button class="ga-adm-btn-sm ga-adm-btn-reject"  onclick="updateReport('${escapeHtml(r.id)}','rejected')">Tolak</button>
        ` : ''}
      </div>
    </td>
  </tr>`;
}

async function loadReports() {
  try {
    const params = reportFilter !== 'all' ? { status: reportFilter } : {};
    const res = await api('reports', params);
    reports = res.data || [];
  } catch { reports = []; }
  renderReports();
  renderDashReports();
}

function renderReports() {
  const tbody = document.getElementById('ga-adm-report-tbody');
  tbody.innerHTML = reports.length
    ? reports.map(r => reportRow(r, 'ga-adm-report-tbody')).join('')
    : `<tr><td colspan="7"><div class="ga-adm-empty-table"><i class="fas fa-flag"></i>Tidak ada laporan</div></td></tr>`;
}

function renderDashReports() {
  const tbody = document.getElementById('ga-adm-dash-report-tbody');
  const dashReports = reports.slice(0,5);
  tbody.innerHTML = dashReports.length
    ? dashReports.map(r => reportRow(r, 'ga-adm-dash-report-tbody')).join('')
    : `<tr><td colspan="6"><div class="ga-adm-empty-table"><i class="fas fa-flag"></i>Belum ada laporan</div></td></tr>`;
}

async function updateReport(id, status) {
  try {
    const res = await api('update_report', { id, status }, 'POST');
    toast(res.message || (status === 'approved' ? 'Laporan disetujui' : 'Laporan ditolak'));
    await loadAll();
  } catch (err) {
    toast('Gagal: ' + err.message);
  }
}

function viewReport(id) {
  const r = reports.find(x => x.id === id);
  if (!r) return;
  document.getElementById('ga-adm-drawer-title-el').textContent = 'Detail Laporan';
  document.getElementById('ga-adm-drawer-body').innerHTML = `
    <div class="ga-adm-detail-row"><label>ID</label><span>${escapeHtml(String(r.id).substring(0,8))}...</span></div>
    <div class="ga-adm-detail-row"><label>Tipe Target</label><span>${r.type === 'post' ? 'Postingan' : 'Akun'}</span></div>
    <div class="ga-adm-detail-row"><label>Target</label><span>${escapeHtml(r.targetTitle)}</span></div>
    <div class="ga-adm-detail-row"><label>Alasan</label><span>${REASON_LABELS[r.reason]||r.reason}</span></div>
    ${r.message ? `<div class="ga-adm-detail-row"><label>Pesan Tambahan</label><span style="white-space: pre-wrap; background: var(--ga-adm-surface2); padding: 10px; border-radius: 8px; margin-top: 5px;">${escapeHtml(r.message)}</span></div>` : ''}
    <div class="ga-adm-detail-row"><label>Status</label><span>${badgeStatus(r.status)}</span></div>
    <div class="ga-adm-detail-row"><label>Pelapor</label><span>${escapeHtml(r.reporter || '-')}</span></div>
    <div class="ga-adm-detail-row"><label>Waktu</label><span>${new Date(r.createdAt).toLocaleString('id-ID')}</span></div>
  `;
  const actions = document.getElementById('ga-adm-drawer-actions-el');
  actions.innerHTML = r.status === 'pending' ? `
    <button class="ga-adm-btn-sm ga-adm-btn-approve" onclick="updateReport('${escapeHtml(r.id)}','approved');closeDrawer()">Setujui Laporan</button>
    <button class="ga-adm-btn-sm ga-adm-btn-reject"  onclick="updateReport('${escapeHtml(r.id)}','rejected');closeDrawer()">Tolak Laporan</button>
  ` : `<button class="ga-adm-btn-sm ga-adm-btn-view" onclick="closeDrawer()">Tutup</button>`;
  openDrawer();
}

/* ========================================================================
   POSTS
======================================================================== */
async function loadPosts(filter = '') {
  try {
    const showRemoved = document.getElementById('ga-adm-show-removed')?.checked ? '1' : '0';
    const params = { show_removed: showRemoved };
    if (filter) params.q = filter;
    const res = await api('posts', params);
    posts = res.data || [];
  } catch { posts = []; }
  renderPosts();
}

function renderPosts() {
  const tbody = document.getElementById('ga-adm-post-tbody');
  tbody.innerHTML = posts.length ? posts.map(p => {
    const isRemoved = p.status === 'removed';
    return `<tr${isRemoved ? ' style="opacity:0.55"' : ''}>
    <td><strong>${escapeHtml(p.title)}</strong></td>
    <td style="color:var(--ga-adm-muted)">${escapeHtml(p.artist)}</td>
    <td style="font-size:12px;color:var(--ga-adm-muted)">${escapeHtml(p.tags)}</td>
    <td><i class="fas fa-heart" style="color:var(--ga-adm-danger);font-size:11px"></i> ${p.likes}</td>
    <td>${badgeStatus(p.status)}</td>
    <td>
      <div class="ga-adm-action-btns">
        <button class="ga-adm-btn-sm ga-adm-btn-view" onclick="viewPost('${escapeHtml(p.id)}')">Detail</button>
        ${isRemoved
          ? `<button class="ga-adm-btn-sm ga-adm-btn-approve" onclick="restorePost('${escapeHtml(p.id)}')"><i class="fas fa-undo"></i> Pulihkan</button>`
          : `<button class="ga-adm-btn-sm ga-adm-btn-delete" onclick="deletePost('${escapeHtml(p.id)}')"><i class="fas fa-trash"></i></button>`}
      </div>
    </td>
  </tr>`;
  }).join('') : `<tr><td colspan="6"><div class="ga-adm-empty-table"><i class="fas fa-images"></i>Tidak ada postingan</div></td></tr>`;
}

async function deletePost(id) {
  if (!confirm('Hapus postingan ini?')) return;
  try {
    await api('delete_post', { id }, 'POST');
    toast('🗑 Postingan dihapus');
    await loadAll();
  } catch (err) {
    toast('Gagal: ' + err.message);
  }
}

async function restorePost(id) {
  if (!confirm('Pulihkan postingan ini?')) return;
  try {
    const res = await api('restore_post', { id }, 'POST');
    toast(res.message || '✅ Postingan dipulihkan');
    await loadAll();
  } catch (err) {
    toast('Gagal: ' + err.message);
  }
}

function viewPost(id) {
  const p = posts.find(x => x.id === id);
  if (!p) return;
  const isRemoved = p.status === 'removed';
  document.getElementById('ga-adm-drawer-title-el').textContent = 'Detail Postingan';
  document.getElementById('ga-adm-drawer-body').innerHTML = `
    <div class="ga-adm-detail-row"><label>Judul</label><span>${escapeHtml(p.title)}</span></div>
    <div class="ga-adm-detail-row"><label>Artis</label><span>${escapeHtml(p.artist)}</span></div>
    <div class="ga-adm-detail-row"><label>Tags</label><span>${escapeHtml(p.tags)}</span></div>
    <div class="ga-adm-detail-row"><label>Likes</label><span>${p.likes}</span></div>
    <div class="ga-adm-detail-row"><label>Status</label><span>${badgeStatus(p.status)}</span></div>
  `;
  document.getElementById('ga-adm-drawer-actions-el').innerHTML = `
    ${isRemoved
      ? `<button class="ga-adm-btn-sm ga-adm-btn-approve" onclick="restorePost('${escapeHtml(p.id)}');closeDrawer()">Pulihkan Postingan</button>`
      : `<button class="ga-adm-btn-sm ga-adm-btn-delete" onclick="deletePost('${escapeHtml(p.id)}');closeDrawer()">Hapus Postingan</button>`}
    <button class="ga-adm-btn-sm ga-adm-btn-view" onclick="closeDrawer()">Tutup</button>
  `;
  openDrawer();
}

/* ========================================================================
   ACCOUNTS
======================================================================== */
async function loadAccounts(filter = '') {
  try {
    const params = filter ? { q: filter } : {};
    const res = await api('accounts', params);
    accounts = res.data || [];
  } catch { accounts = []; }
  renderAccounts();
}

function renderAccounts() {
  const tbody = document.getElementById('ga-adm-account-tbody');
  tbody.innerHTML = accounts.length ? accounts.map(a => `<tr>
    <td>
      <div class="ga-adm-user-cell">
        <div class="ga-adm-u-avatar">${escapeHtml(a.name[0].toUpperCase())}</div>
        <div><div class="ga-adm-u-name">${escapeHtml(a.name)}</div><div class="ga-adm-u-handle">${escapeHtml(a.handle)}</div></div>
      </div>
    </td>
    <td>${badgeStatus(a.type)}</td>
    <td>${a.posts}</td>
    <td>${a.followers.toLocaleString('id')}</td>
    <td>${badgeStatus(a.status)}</td>
    <td>
      <div class="ga-adm-action-btns">
        <button class="ga-adm-btn-sm ga-adm-btn-view" onclick="viewAccount('${escapeHtml(a.id)}')">Detail</button>
        ${a.status === 'banned'
          ? `<button class="ga-adm-btn-sm ga-adm-btn-unban" onclick="toggleBan('${escapeHtml(a.id)}')">Unban</button>`
          : `<button class="ga-adm-btn-sm ga-adm-btn-ban"   onclick="toggleBan('${escapeHtml(a.id)}')">Ban</button>`}
      </div>
    </td>
  </tr>`).join('') : `<tr><td colspan="6"><div class="ga-adm-empty-table"><i class="fas fa-users"></i>Tidak ada akun</div></td></tr>`;
}

async function toggleBan(id) {
  const a = accounts.find(x => x.id === id);
  if (!a) return;
  const willBan = a.status !== 'banned';
  if (!confirm(`${willBan ? 'Ban' : 'Unban'} akun ${a.handle}?`)) return;
  try {
    const res = await api('toggle_ban', { id }, 'POST');
    toast(res.message);
    await loadAll();
  } catch (err) {
    toast('Gagal: ' + err.message);
  }
}

function viewAccount(id) {
  const a = accounts.find(x => x.id === id);
  if (!a) return;
  document.getElementById('ga-adm-drawer-title-el').textContent = 'Detail Akun';
  document.getElementById('ga-adm-drawer-body').innerHTML = `
    <div class="ga-adm-detail-row"><label>Nama</label><span>${escapeHtml(a.name)}</span></div>
    <div class="ga-adm-detail-row"><label>Username</label><span>${escapeHtml(a.handle)}</span></div>
    <div class="ga-adm-detail-row"><label>Tipe</label><span>${badgeStatus(a.type)}</span></div>
    <div class="ga-adm-detail-row"><label>Postingan</label><span>${a.posts}</span></div>
    <div class="ga-adm-detail-row"><label>Followers</label><span>${a.followers.toLocaleString('id')}</span></div>
    <div class="ga-adm-detail-row"><label>Status</label><span>${badgeStatus(a.status)}</span></div>
  `;
  document.getElementById('ga-adm-drawer-actions-el').innerHTML = `
    ${a.status === 'banned'
      ? `<button class="ga-adm-btn-sm ga-adm-btn-unban" onclick="toggleBan('${escapeHtml(a.id)}');closeDrawer()">Unban Akun</button>`
      : `<button class="ga-adm-btn-sm ga-adm-btn-ban"   onclick="toggleBan('${escapeHtml(a.id)}');closeDrawer()">Ban Akun</button>`}
    <button class="ga-adm-btn-sm ga-adm-btn-view" onclick="closeDrawer()">Tutup</button>
  `;
  openDrawer();
}

/* ========================================================================
   STATS
======================================================================== */
async function loadStats() {
  try {
    const res = await api('stats');
    const d = res.data;
    document.getElementById('ga-adm-stat-pending').textContent  = d.pending;
    document.getElementById('ga-adm-stat-approved').textContent = d.approved;
    document.getElementById('ga-adm-stat-posts').textContent    = d.posts;
    document.getElementById('ga-adm-stat-accounts').textContent = d.accounts;
    document.getElementById('ga-adm-report-badge').textContent  = d.pending;
  } catch (err) {
    console.error('Stats load error:', err);
  }
}

/* ========================================================================
   FILTER TABLE (search) — debounced DB search
======================================================================== */
let _searchTimer = null;
function filterTable(val, tbodyId) {
  clearTimeout(_searchTimer);
  _searchTimer = setTimeout(() => {
    if (tbodyId === 'ga-adm-post-tbody')    loadPosts(val);
    if (tbodyId === 'ga-adm-account-tbody') loadAccounts(val);
  }, 300);
}

/* ========================================================================
   DRAWER
======================================================================== */
function openDrawer() { document.getElementById('ga-adm-drawer-overlay').classList.add('open'); }
function closeDrawer() { document.getElementById('ga-adm-drawer-overlay').classList.remove('open'); }
document.getElementById('ga-adm-drawer-overlay').addEventListener('click', e => {
  if (e.target === document.getElementById('ga-adm-drawer-overlay')) closeDrawer();
});

/* ========================================================================
   TOAST
======================================================================== */
function toast(msg) {
  const el = document.getElementById('ga-adm-toast');
  el.textContent = msg; el.style.display = 'block';
  clearTimeout(toast._t);
  toast._t = setTimeout(() => { el.style.display='none'; }, 2500);
}

/* ========================================================================
   LOAD ALL — fetch everything from DB
======================================================================== */
async function loadAll() {
  await Promise.all([
    loadStats(),
    loadReports(),
    loadPosts(),
    loadAccounts(),
  ]);
}

// Initial load
loadAll();
</script>
</body>
</html>
