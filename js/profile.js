
'use strict';

// ── TAB SWITCHER ───────────────────────────────────────────────
/**
 * Ganti tab aktif.
 * Dipanggil dari atribut onclick di HTML: switchTab(this, 'content-bio')
 */
function switchTab(clickedBtn, targetId) {
    $$('.tab-btn').forEach(b => b.classList.remove('active'));
    $$('.tab-content').forEach(c => c.classList.remove('active'));
    clickedBtn.classList.add('active');
    const target = $('#' + targetId);
    if (target) target.classList.add('active');
}

// Inisialisasi tidak lagi diperlukan karena di-handle oleh PHP template.

// ── COMMISSION TIERS MANAGEMENT ───────────────────────────────
let commissionTiers = [];

async function loadCommissionTiers() {
    const listEl = $('#commissionTiersList');
    if (!listEl) return;
    try {
        const res = await fetch('api/manage-commission-tiers.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'get'})
        });
        const data = await res.json();
        if (data.status === 'ok') {
            commissionTiers = data.tiers;
            renderCommissionTiers();
        } else {
            listEl.innerHTML = `<div style="text-align:center;color:red;">Error: ${data.message}</div>`;
        }
    } catch (e) {
        listEl.innerHTML = `<div style="text-align:center;color:red;">Gagal memuat tier.</div>`;
    }
}

function renderCommissionTiers() {
    const listEl = $('#commissionTiersList');
    if (!listEl) return;
    if (commissionTiers.length === 0) {
        listEl.innerHTML = '<div style="text-align:center; padding:20px; color:var(--text-gray);">Belum ada tier. Klik "Tambah Tier" untuk mulai.</div>';
        return;
    }

    let html = '';
    commissionTiers.forEach(tier => {
        html += `
        <div style="background:#2a2a35; border:1px solid #444; border-radius:12px; margin-bottom:15px; overflow:hidden;">
            <div style="background:#1a1a24; padding:15px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #444;">
                <div>
                    <h4 style="margin:0; color:var(--accent);">${escapeHtml(tier.name)}</h4>
                    <div style="font-size:14px; font-weight:bold; color:var(--text-gray); margin-top:4px;">
                        Harga Dasar: Rp ${Number(tier.price).toLocaleString('id-ID')}
                    </div>
                </div>
                <div style="display:flex; gap:10px;">
                    <button onclick="editTier('${tier.id}')" style="background:transparent; color:var(--accent2); border:1px solid var(--accent2); padding:5px 10px; border-radius:6px; cursor:pointer;"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteTier('${tier.id}')" style="background:transparent; color:#f87171; border:1px solid #f87171; padding:5px 10px; border-radius:6px; cursor:pointer;"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div style="padding:15px;">
                <p style="font-size:13px; color:#e0e0e0; margin-top:0; white-space:pre-wrap;">${escapeHtml(tier.description || 'Tidak ada deskripsi')}</p>
            </div>
        </div>
        `;
    });
    listEl.innerHTML = html;
}

function openTierModal() {
    $('#tierId').value = '';
    $('#tierName').value = '';
    $('#tierPrice').value = '';
    $('#tierDesc').value = '';
    $('#tierModalTitle').innerText = 'Tambah Tier Baru';
    $('#tierModal').classList.add('show');
}

function editTier(id) {
    const tier = commissionTiers.find(t => t.id === id);
    if (!tier) return;
    $('#tierId').value = tier.id;
    $('#tierName').value = tier.name;
    $('#tierPrice').value = tier.price;
    $('#tierDesc').value = tier.description;
    $('#tierModalTitle').innerText = 'Edit Tier';
    $('#tierModal').classList.add('show');
}

function closeTierModal() {
    $('#tierModal').classList.remove('show');
}

async function saveTier() {
    const name = $('#tierName').value.trim();
    const price = $('#tierPrice').value;
    if (!name || price === '') return alert('Nama dan Harga wajib diisi!');
    
    try {
        const res = await fetch('api/manage-commission-tiers.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'save',
                tier_id: $('#tierId').value,
                name: name,
                price: price,
                description: $('#tierDesc').value
            })
        });
        const data = await res.json();
        if (data.status === 'ok') {
            closeTierModal();
            loadCommissionTiers();
        } else alert(data.message);
    } catch(e) { alert('Error jaringan'); }
}

async function deleteTier(id) {
    if(!confirm('Anda yakin ingin menghapus tier ini? Semua order lama yang menggunakan tier ini mungkin terpengaruh.')) return;
    try {
        const res = await fetch('api/manage-commission-tiers.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'delete', tier_id: id})
        });
        const data = await res.json();
        if(data.status === 'ok') loadCommissionTiers();
        else alert(data.message);
    } catch(e) {}
}

// ── COMMISSION OPTIONS MANAGEMENT ─────────────────────────────
let commissionOptions = [];

async function loadCommissionOptions() {
    const listEl = $('#commissionOptionsList');
    if (!listEl) return;
    try {
        const res = await fetch('api/manage-commission-options.php?action=list');
        const data = await res.json();
        if (data.status === 'ok') {
            commissionOptions = data.options;
            renderCommissionOptions();
        } else {
            listEl.innerHTML = `<div style="text-align:center;color:red;">Error: ${data.message}</div>`;
        }
    } catch (e) {
        listEl.innerHTML = `<div style="text-align:center;color:red;">Gagal memuat opsi.</div>`;
    }
}

function renderCommissionOptions() {
    const listEl = $('#commissionOptionsList');
    if (!listEl) return;
    if (commissionOptions.length === 0) {
        listEl.innerHTML = '<div style="text-align:center; padding:20px; color:var(--text-gray);">Belum ada opsi.</div>';
        return;
    }

    let html = '';
    commissionOptions.forEach(opt => {
        html += `
        <div style="background:#2a2a35; border:1px solid #444; border-radius:12px; margin-bottom:15px; overflow:hidden;">
            <div style="background:#1a1a24; padding:15px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #444;">
                <div>
                    <h4 style="margin:0; color:var(--accent);">${escapeHtml(opt.category)}</h4>
                    <div style="font-size:12px; color:var(--text-gray); margin-top:4px;">
                        ${opt.selection_type === 'single' ? 'Pilih 1 (Radio)' : 'Pilih Banyak (Checkbox)'} | 
                        ${parseInt(opt.is_required) === 1 ? '<span style="color:#f87171;">Wajib Diisi</span>' : 'Opsional'}
                    </div>
                </div>
                <div style="display:flex; gap:10px;">
                    <button onclick="deleteOption('${opt.id}')" style="background:transparent; color:#f87171; border:1px solid #f87171; padding:5px 10px; border-radius:6px; cursor:pointer;"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div style="padding:15px;">
                <p style="font-size:13px; color:#e0e0e0; margin-top:0;">${escapeHtml(opt.description || 'Tidak ada deskripsi')}</p>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    ${opt.items.map(item => `
                        <div style="display:flex; justify-content:space-between; align-items:center; background:#1e1e26; padding:10px; border-radius:8px;">
                            <span style="font-size:13px;">${escapeHtml(item.label)} 
                                ${parseInt(item.is_default) === 1 ? '<span style="background:var(--purple); color:white; padding:2px 6px; border-radius:4px; font-size:10px; margin-left:8px;">Default</span>' : ''}
                            </span>
                            <div style="display:flex; align-items:center; gap:15px;">
                                <span style="font-size:13px; font-weight:600; color:var(--accent2);">${item.price_type === 'fixed' ? '+Rp ' + Number(item.price_value).toLocaleString('id-ID') : '+' + item.price_value + '%'}</span>
                                <button onclick="deleteItem('${item.id}')" style="background:transparent; color:var(--text-gray); border:none; cursor:pointer;"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button onclick="openAddItemModal('${opt.id}')" style="background:transparent; color:var(--purple); border:1px dashed var(--purple); padding:8px; border-radius:8px; cursor:pointer; width:100%; margin-top:10px; font-weight:600;"><i class="fas fa-plus"></i> Tambah Pilihan</button>
            </div>
        </div>
        `;
    });
    listEl.innerHTML = html;
}

// TOS
async function saveTos() {
    const tos = $('#tosInput').value;
    try {
        const res = await fetch('api/manage-commission-options.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'update_tos', tos})
        });
        const data = await res.json();
        if (data.status === 'ok') {
            alert('TOS berhasil disimpan!');
        } else {
            alert(data.message);
        }
    } catch (e) {
        alert('Gagal menyimpan TOS.');
    }
}

// Option Modal
function openAddOptionModal() {
    $('#optId').value = '';
    $('#optCat').value = '';
    $('#optDesc').value = '';
    $('#optionModal').classList.add('show');
}
function closeOptionModal() {
    $('#optionModal').classList.remove('show');
}
async function saveOption() {
    const cat = $('#optCat').value.trim();
    if (!cat) return alert('Nama kategori wajib!');
    try {
        const res = await fetch('api/manage-commission-options.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'add_option',
                category: cat,
                description: $('#optDesc').value,
                selection_type: $('#optType').value,
                is_required: $('#optReq').value,
                sort_order: commissionOptions.length + 1
            })
        });
        const data = await res.json();
        if (data.status === 'ok') {
            closeOptionModal();
            loadCommissionOptions();
        } else alert(data.message);
    } catch(e) { alert('Error jaringan'); }
}
async function deleteOption(id) {
    if(!confirm('Hapus kategori ini beserta isinya?')) return;
    try {
        const res = await fetch('api/manage-commission-options.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'delete_option', option_id: id})
        });
        const data = await res.json();
        if(data.status === 'ok') loadCommissionOptions();
        else alert(data.message);
    } catch(e) {}
}

// Item Modal
function openAddItemModal(optId) {
    $('#parentOptId').value = optId;
    $('#itemLabel').value = '';
    $('#itemPriceVal').value = 0;
    $('#itemModal').classList.add('show');
}
function closeItemModal() {
    $('#itemModal').classList.remove('show');
}
async function saveItem() {
    const label = $('#itemLabel').value.trim();
    if (!label) return alert('Label wajib!');
    try {
        const res = await fetch('api/manage-commission-options.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'add_item',
                option_id: $('#parentOptId').value,
                label: label,
                price_type: $('#itemPriceType').value,
                price_value: $('#itemPriceVal').value
            })
        });
        const data = await res.json();
        if (data.status === 'ok') {
            closeItemModal();
            loadCommissionOptions();
        } else alert(data.message);
    } catch(e) { alert('Error jaringan'); }
}
async function deleteItem(id) {
    if(!confirm('Hapus item ini?')) return;
    try {
        const res = await fetch('api/manage-commission-options.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'delete_item', item_id: id})
        });
        const data = await res.json();
        if(data.status === 'ok') loadCommissionOptions();
        else alert(data.message);
    } catch(e) {}
}

// Jika ada tab commission terpilih, load opsi
document.addEventListener('DOMContentLoaded', () => {
    const listEl = $('#commissionOptionsList');
    if (listEl) {
        // Load immediately
        loadCommissionTiers();
        loadCommissionOptions();

        // Observer (just in case)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.target.classList.contains('active')) {
                    if (commissionOptions.length === 0) loadCommissionOptions();
                    if (commissionTiers.length === 0) loadCommissionTiers();
                }
            });
        });
        const comTab = $('#content-commission');
        if (comTab) observer.observe(comTab, { attributes: true, attributeFilter: ['class'] });
    }
});
