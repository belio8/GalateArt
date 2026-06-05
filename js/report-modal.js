

(function () {
  /* ───────── MODAL HTML ───────── */
  const REPORT_OPTIONS = [
    { id: 'sensitive', title: 'Konten sensitif tidak ditandai', desc: 'Konten eksplisit secara seksual, NSFW, kekerasan, gore, dan konten sensitif lainnya yang tidak diberi label.' },
    { id: 'hashtag',   title: 'Penyalahgunaan hashtag / kategori', desc: 'Menyalahgunakan hashtag dan kategori.' },
    { id: 'ai',        title: 'AI / tracing / penipuan / scam', desc: 'Akun palsu, penipuan, evidential tracing, heavy referencing, penggunaan AI tidak etis.' },
    { id: 'harass',    title: 'Pelecehan / doxxing / ancaman', desc: 'Membagikan atau mengancam membagikan informasi pribadi, pelecehan terhadap individu, hasutan pelecehan.' },
    { id: 'hate',      title: 'Menghasut kebencian, kekerasan, atau menyakiti diri', desc: 'Ancaman kekerasan, hasutan kekerasan, ujaran kebencian. Mendorong, mempromosikan, atau berbagi cara menyakiti diri sendiri. Perilaku ilegal, dll.' },
    { id: 'misrep',    title: 'Penyamaran / representasi palsu', desc: 'Peniruan identitas, identitas menyesatkan, atau iklan palsu.' },
    { id: 'other',     title: 'Lainnya', desc: '' },
  ];

  const overlay = document.createElement('div');
  overlay.id = 'ga-rpt-modal-overlay';
  overlay.innerHTML = `
    <div id="ga-rpt-modal-box">
      <button class="ga-rpt-close-btn" id="ga-rpt-close-btn-el"><i class="fas fa-times"></i></button>

      <div id="ga-rpt-form-view">
        <h2>Laporkan masalah</h2>
        <p class="ga-rpt-subtitle">Ini mengkhawatirkan karena...</p>

        <div class="ga-rpt-options" id="ga-rpt-options">
          ${REPORT_OPTIONS.map(o => `
            <label class="ga-rpt-option-label" data-id="${o.id}">
              <input type="radio" name="reportReason" value="${o.id}">
              <div class="ga-rpt-radio-circle"></div>
              <div class="ga-rpt-option-text">
                <strong>${o.title}</strong>
                ${o.desc ? `<small>${o.desc}</small>` : ''}
              </div>
            </label>
          `).join('')}
        </div>

        <div class="ga-rpt-commission-note">
          <i class="fas fa-exclamation-triangle"></i>
          Hubungi kami langsung untuk masalah komisi &rsaquo;
        </div>

        <button class="ga-rpt-next-btn" id="ga-rpt-next-btn-el" disabled>Selanjutnya</button>
      </div>

      <div class="ga-rpt-success" id="ga-rpt-success-view">
        <i class="fas fa-check-circle"></i>
        <h3>Laporan Terkirim</h3>
        <p>Terima kasih. Tim admin GalateArt akan meninjau laporan Anda.</p>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);

  /* ───────── STATE ───────── */
  let currentTarget = null; // { type: 'post'|'account', id, title }
  let selectedReason = null;

  /* ───────── HELPERS ───────── */
  async function saveReport(target, reason) {
    try {
      const res = await fetch('api/reports.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          target_type: target.type,
          target_id: target.id,
          target_title: target.title,
          reason: reason
        })
      });
      const data = await res.json();
      if (data.status !== 'ok') {
        console.error('Report failed:', data.message);
      }
    } catch (err) {
      console.error('Network error during report:', err);
    }
  }

  function openReportModal(target) {
    currentTarget = target;
    selectedReason = null;
    // reset
    document.querySelectorAll('.ga-rpt-option-label').forEach(l => l.classList.remove('selected'));
    document.querySelectorAll('input[name=reportReason]').forEach(r => r.checked = false);
    document.getElementById('ga-rpt-next-btn-el').disabled = true;
    document.getElementById('ga-rpt-form-view').style.display = '';
    document.getElementById('ga-rpt-success-view').style.display = 'none';
    overlay.classList.add('open');
  }

  function closeReportModal() { overlay.classList.remove('open'); }

  /* ───────── EVENTS ───────── */
  document.getElementById('ga-rpt-close-btn-el').addEventListener('click', closeReportModal);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeReportModal(); });

  document.getElementById('ga-rpt-options').addEventListener('click', e => {
    const label = e.target.closest('.ga-rpt-option-label');
    if (!label) return;
    document.querySelectorAll('.ga-rpt-option-label').forEach(l => l.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input').checked = true;
    selectedReason = label.dataset.id;
    document.getElementById('ga-rpt-next-btn-el').disabled = false;
  });

  document.getElementById('ga-rpt-next-btn-el').addEventListener('click', () => {
    if (!selectedReason || !currentTarget) return;
    saveReport(currentTarget, selectedReason);
    document.getElementById('ga-rpt-form-view').style.display = 'none';
    document.getElementById('ga-rpt-success-view').style.display = 'block';
    setTimeout(closeReportModal, 2200);
  });

  /* ───────── INJECT BUTTONS INTO CARDS ───────── */
  function injectCardMenus() {
    document.querySelectorAll('.art-card, .post-card').forEach((card, i) => {
      if (card.querySelector('.ga-rpt-card-menu-btn')) return; // skip if already injected
      const artistName = card.dataset.artist || card.querySelector('.card-avatar-tooltip')?.textContent || '';
      const hashEl   = card.querySelector('.hashtags');
      const targetTitle = card.dataset.title || (hashEl ? hashEl.textContent : `Post #${i+1}`);
      const targetId    = card.dataset.postId || `card-${i}-${Date.now()}`;

      const btn = document.createElement('button');
      btn.className = 'ga-rpt-card-menu-btn';
      btn.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
      btn.title = 'More options';

      const menu = document.createElement('div');
      menu.className = 'ga-rpt-card-ctx-menu';
      menu.innerHTML = `
        <button data-action="report" class="danger"><i class="fas fa-flag"></i> Laporkan Postingan</button>
        <button data-action="report-account"><i class="fas fa-user-slash"></i> Laporkan Akun</button>
      `;

      card.appendChild(btn);
      card.appendChild(menu);

      btn.addEventListener('click', e => {
        e.stopPropagation();
        document.querySelectorAll('.ga-rpt-card-ctx-menu.open').forEach(m => m !== menu && m.classList.remove('open'));
        menu.classList.toggle('open');
      });

      menu.addEventListener('click', e => {
        const action = e.target.closest('[data-action]')?.dataset.action;
        menu.classList.remove('open');
        if (action === 'report') openReportModal({ type: 'post', id: targetId, title: targetTitle });
        if (action === 'report-account') openReportModal({ type: 'account', id: artistName || 'unknown', title: artistName || 'Account' });
      });
    });

    // Close menus on outside click
    document.addEventListener('click', () => {
      document.querySelectorAll('.ga-rpt-card-ctx-menu.open').forEach(m => m.classList.remove('open'));
    });
  }

  /* ───────── INJECT REPORT BUTTON INSIDE POST MODAL PANEL (if exists) ───────── */
  function injectModalReportBtn() {
    const panel = document.querySelector('.modal-panel');
    if (!panel || panel.querySelector('.ga-rpt-trigger-btn')) return;
    const bar = panel.querySelector('.like-action-bar');
    if (!bar) return;
    const reportBtn = document.createElement('button');
    reportBtn.className = 'ga-rpt-trigger-btn';
    reportBtn.innerHTML = '<i class="fas fa-flag"></i> Laporkan';
    reportBtn.style.marginTop = '6px';
    reportBtn.addEventListener('click', () => {
      const name = document.getElementById('phName')?.textContent || 'post';
      openReportModal({ type: 'post', id: 'modal-post', title: name });
    });
    bar.after(reportBtn);
  }

  /* Run on DOM ready */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => { injectCardMenus(); injectModalReportBtn(); });
  } else {
    injectCardMenus();
    injectModalReportBtn();
  }

  /* Re-inject when modal opens (for dynamically shown cards) */
  const obs = new MutationObserver(() => { injectCardMenus(); injectModalReportBtn(); });
  obs.observe(document.body, { childList: true, subtree: true });

  /* Expose globally for manual use */
  window.openReportModal = openReportModal;
})();