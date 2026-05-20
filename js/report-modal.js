/**
 * GalateArt - Report System
 * Inject tombol report dan modal laporan ke semua halaman.
 * Tambahkan <script src="report-modal.js"></script> sebelum </body>.
 */

(function () {
  /* ───────── STYLES ───────── */
  const style = document.createElement('style');
  style.textContent = `
    /* ── Report Button ── */
    .report-trigger-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: none;
      border: none;
      color: #888;
      font-size: 12px;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      padding: 5px 8px;
      border-radius: 6px;
      transition: color .2s, background .2s;
    }
    .report-trigger-btn:hover { color: #e05c5c; background: rgba(224,92,92,.08); }
    .report-trigger-btn i { font-size: 13px; }

    /* ── Three-dot menu on art-card / post-card ── */
    .art-card, .post-card { position: relative; }
    .card-menu-btn {
      position: absolute;
      top: 8px; right: 8px;
      width: 28px; height: 28px;
      border-radius: 50%;
      background: rgba(0,0,0,.55);
      border: none;
      color: #fff;
      font-size: 14px;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      opacity: 0;
      transition: opacity .2s;
      z-index: 5;
    }
    .art-card:hover .card-menu-btn,
    .post-card:hover .card-menu-btn { opacity: 1; }

    .card-context-menu {
      position: absolute;
      top: 38px; right: 8px;
      background: #1e1e2a;
      border: 1px solid #333;
      border-radius: 10px;
      padding: 6px 0;
      min-width: 160px;
      z-index: 100;
      box-shadow: 0 8px 24px rgba(0,0,0,.5);
      display: none;
    }
    .card-context-menu.open { display: block; }
    .card-context-menu button {
      width: 100%;
      background: none;
      border: none;
      color: #ccc;
      font-family: 'Poppins', sans-serif;
      font-size: 13px;
      padding: 9px 16px;
      text-align: left;
      cursor: pointer;
      display: flex; align-items: center; gap: 8px;
      transition: background .15s, color .15s;
    }
    .card-context-menu button:hover { background: #2a2a3a; color: #fff; }
    .card-context-menu button.danger { color: #e05c5c; }
    .card-context-menu button.danger:hover { background: rgba(224,92,92,.1); }

    /* ── Report Modal Overlay ── */
    #reportModalOverlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.7);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(4px);
    }
    #reportModalOverlay.open { display: flex; }

    #reportModalBox {
      background: #16161f;
      border: 1px solid #2e2e3e;
      border-radius: 18px;
      width: 420px;
      max-width: 95vw;
      max-height: 90vh;
      overflow-y: auto;
      padding: 28px 26px 22px;
      position: relative;
      animation: slideUp .25s ease;
      scrollbar-width: thin;
      scrollbar-color: #3a3a50 transparent;
    }
    #reportModalBox::-webkit-scrollbar { width: 6px; }
    #reportModalBox::-webkit-scrollbar-track { background: transparent; }
    #reportModalBox::-webkit-scrollbar-thumb { background: #3a3a50; border-radius: 3px; }
    @keyframes slideUp {
      from { opacity:0; transform: translateY(20px); }
      to   { opacity:1; transform: translateY(0); }
    }

    #reportModalBox h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 20px;
      font-weight: 700;
      color: #fff;
      margin: 0 0 4px;
    }
    #reportModalBox .report-subtitle {
      font-family: 'Poppins', sans-serif;
      font-size: 13px;
      color: #888;
      margin: 0 0 18px;
    }
    .report-close-btn {
      position: absolute;
      top: 16px; right: 18px;
      background: none;
      border: none;
      color: #888;
      font-size: 20px;
      cursor: pointer;
      line-height: 1;
      transition: color .2s;
    }
    .report-close-btn:hover { color: #fff; }

    .report-options { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; }

    .report-option-label {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 12px 14px;
      border-radius: 10px;
      cursor: pointer;
      border: 1.5px solid transparent;
      transition: border-color .2s, background .2s;
    }
    .report-option-label:hover { background: #1e1e2c; border-color: #3a3a50; }
    .report-option-label input[type=radio] { display: none; }
    .report-option-label.selected { border-color: #7c6aff; background: rgba(124,106,255,.08); }

    .report-radio-circle {
      width: 20px; height: 20px;
      border-radius: 50%;
      border: 2px solid #555;
      flex-shrink: 0;
      margin-top: 2px;
      display: flex; align-items: center; justify-content: center;
      transition: border-color .2s;
    }
    .report-option-label.selected .report-radio-circle {
      border-color: #7c6aff;
      background: #7c6aff;
    }
    .report-option-label.selected .report-radio-circle::after {
      content: '';
      width: 8px; height: 8px;
      border-radius: 50%;
      background: #fff;
    }

    .report-option-text strong {
      display: block;
      font-family: 'Poppins', sans-serif;
      font-size: 13.5px;
      font-weight: 600;
      color: #e0e0e0;
    }
    .report-option-text small {
      font-family: 'Poppins', sans-serif;
      font-size: 11.5px;
      color: #777;
      line-height: 1.4;
    }

    .report-commission-note {
      background: rgba(255,160,50,.07);
      border: 1px solid rgba(255,160,50,.25);
      border-radius: 10px;
      padding: 11px 14px;
      font-family: 'Poppins', sans-serif;
      font-size: 12px;
      color: #f0a030;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 16px;
      cursor: pointer;
      transition: background .2s;
    }
    .report-commission-note:hover { background: rgba(255,160,50,.13); }
    .report-commission-note i { font-size: 14px; }

    .report-next-btn {
      width: 100%;
      padding: 12px;
      background: #7c6aff;
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s, opacity .2s;
    }
    .report-next-btn:disabled { opacity: .4; cursor: default; }
    .report-next-btn:not(:disabled):hover { background: #6a58e8; }

    /* ── Success state ── */
    .report-success {
      text-align: center;
      padding: 20px 0 10px;
      display: none;
    }
    .report-success i { font-size: 48px; color: #5cdd8b; margin-bottom: 14px; display: block; }
    .report-success h3 { font-family: 'Poppins', sans-serif; font-size: 18px; color: #fff; margin: 0 0 6px; }
    .report-success p { font-family: 'Poppins', sans-serif; font-size: 13px; color: #888; margin: 0; }
  `;
  document.head.appendChild(style);

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
  overlay.id = 'reportModalOverlay';
  overlay.innerHTML = `
    <div id="reportModalBox">
      <button class="report-close-btn" id="reportCloseBtn"><i class="fas fa-times"></i></button>

      <div id="reportFormView">
        <h2>Laporkan masalah</h2>
        <p class="report-subtitle">Ini mengkhawatirkan karena...</p>

        <div class="report-options" id="reportOptions">
          ${REPORT_OPTIONS.map(o => `
            <label class="report-option-label" data-id="${o.id}">
              <input type="radio" name="reportReason" value="${o.id}">
              <div class="report-radio-circle"></div>
              <div class="report-option-text">
                <strong>${o.title}</strong>
                ${o.desc ? `<small>${o.desc}</small>` : ''}
              </div>
            </label>
          `).join('')}
        </div>

        <div class="report-commission-note">
          <i class="fas fa-exclamation-triangle"></i>
          Hubungi kami langsung untuk masalah komisi &rsaquo;
        </div>

        <button class="report-next-btn" id="reportNextBtn" disabled>Selanjutnya</button>
      </div>

      <div class="report-success" id="reportSuccessView">
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
  function saveReport(target, reason) {
    const reports = JSON.parse(localStorage.getItem('galateart_reports') || '[]');
    reports.push({
      id: Date.now(),
      type: target.type,
      targetId: target.id,
      targetTitle: target.title,
      reason,
      status: 'pending', // pending | approved | rejected
      createdAt: new Date().toISOString(),
    });
    localStorage.setItem('galateart_reports', JSON.stringify(reports));
  }

  function openReportModal(target) {
    currentTarget = target;
    selectedReason = null;
    // reset
    document.querySelectorAll('.report-option-label').forEach(l => l.classList.remove('selected'));
    document.querySelectorAll('input[name=reportReason]').forEach(r => r.checked = false);
    document.getElementById('reportNextBtn').disabled = true;
    document.getElementById('reportFormView').style.display = '';
    document.getElementById('reportSuccessView').style.display = 'none';
    overlay.classList.add('open');
  }

  function closeReportModal() { overlay.classList.remove('open'); }

  /* ───────── EVENTS ───────── */
  document.getElementById('reportCloseBtn').addEventListener('click', closeReportModal);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeReportModal(); });

  document.getElementById('reportOptions').addEventListener('click', e => {
    const label = e.target.closest('.report-option-label');
    if (!label) return;
    document.querySelectorAll('.report-option-label').forEach(l => l.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input').checked = true;
    selectedReason = label.dataset.id;
    document.getElementById('reportNextBtn').disabled = false;
  });

  document.getElementById('reportNextBtn').addEventListener('click', () => {
    if (!selectedReason || !currentTarget) return;
    saveReport(currentTarget, selectedReason);
    document.getElementById('reportFormView').style.display = 'none';
    document.getElementById('reportSuccessView').style.display = 'block';
    setTimeout(closeReportModal, 2200);
  });

  /* ───────── INJECT BUTTONS INTO CARDS ───────── */
  function injectCardMenus() {
    document.querySelectorAll('.art-card, .post-card').forEach((card, i) => {
      if (card.querySelector('.card-menu-btn')) return; // skip if already injected
      const img = card.querySelector('img');
      const artistEl = card.querySelector('.artist-name');
      const hashEl   = card.querySelector('.hashtags');
      const targetTitle = hashEl ? hashEl.textContent : `Post #${i+1}`;
      const targetId    = `card-${i}-${Date.now()}`;

      const btn = document.createElement('button');
      btn.className = 'card-menu-btn';
      btn.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
      btn.title = 'More options';

      const menu = document.createElement('div');
      menu.className = 'card-context-menu';
      menu.innerHTML = `
        <button data-action="report" class="danger"><i class="fas fa-flag"></i> Laporkan Postingan</button>
        <button data-action="report-account"><i class="fas fa-user-slash"></i> Laporkan Akun</button>
      `;

      card.appendChild(btn);
      card.appendChild(menu);

      btn.addEventListener('click', e => {
        e.stopPropagation();
        document.querySelectorAll('.card-context-menu.open').forEach(m => m !== menu && m.classList.remove('open'));
        menu.classList.toggle('open');
      });

      menu.addEventListener('click', e => {
        const action = e.target.closest('[data-action]')?.dataset.action;
        menu.classList.remove('open');
        if (action === 'report') openReportModal({ type: 'post', id: targetId, title: targetTitle });
        if (action === 'report-account') openReportModal({ type: 'account', id: artistEl?.textContent || 'unknown', title: artistEl?.textContent || 'Account' });
      });
    });

    // Close menus on outside click
    document.addEventListener('click', () => {
      document.querySelectorAll('.card-context-menu.open').forEach(m => m.classList.remove('open'));
    });
  }

  /* ───────── INJECT REPORT BUTTON INSIDE POST MODAL PANEL (if exists) ───────── */
  function injectModalReportBtn() {
    const panel = document.querySelector('.modal-panel');
    if (!panel || panel.querySelector('.report-trigger-btn')) return;
    const bar = panel.querySelector('.like-action-bar');
    if (!bar) return;
    const reportBtn = document.createElement('button');
    reportBtn.className = 'report-trigger-btn';
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