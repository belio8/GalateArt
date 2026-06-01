'use strict';
(function initPostModal() {

    /* ── ELEMEN ───────────────────────────────────────────────── */
    const fabBtn      = $('#fabPostBtn');
    const postModal   = $('#postModal');
    const pmCloseBtn  = $('#pmCloseBtn');
    const pmCancelBtn = $('#pmCancelBtn');
    const pmSubmitBtn = $('#pmSubmitBtn');

    const pmUploadArea = $('#pmUploadArea');
    const pmImageInput = $('#pmImageInput');
    const pmPreviewImg = $('#pmPreviewImg');
    const pmPreviewInfo = $('#pmPreviewInfo');
    const pmUploadIcon = $('#pmUploadIcon');
    const pmUploadText = $('#pmUploadText');

    const pmTitle     = $('#pmTitle');
    const pmDesc      = $('#pmDesc');
    const pmTags      = $('#pmTags');
    const pmPrice     = $('#pmPrice');
    const pmFreeCheck = $('#pmFreeCheck');
    const pmNsfw      = $('#pmNsfwToggle');
    const pmToast     = $('#pmToast');
    const pmToastMsg  = $('#pmToastMsg');

    if (!fabBtn || !postModal) return; // halaman tanpa modal ini

    /* ── TAMPILKAN FAB (halaman artist ini sudah hanya untuk artist) ────────────────────── */
    function checkArtistRole() {
        fabBtn.style.display = 'flex';
    }

    checkArtistRole();

    // Hook ke applyLoginState jika ada (dari auth.js)
    const _origApply = window.applyLoginState;
    if (typeof _origApply === 'function') {
        window.applyLoginState = function () {
            _origApply();
            checkArtistRole();
        };
    }

    /* ── BUKA / TUTUP MODAL ───────────────────────────────────── */
    function openPostModal() {
        postModal.classList.add('open');
        document.body.style.overflow = 'hidden';
        pmTitle.focus();
    }

    function closePostModal() {
        postModal.classList.remove('open');
        document.body.style.overflow = '';
    }

    fabBtn.addEventListener('click', openPostModal);
    pmCloseBtn.addEventListener('click', closePostModal);
    pmCancelBtn.addEventListener('click', closePostModal);

    // Tutup saat klik background (bukan kotak modal)
    postModal.addEventListener('click', e => {
        if (e.target === postModal) closePostModal();
    });

    // Tutup dengan tombol Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && postModal.classList.contains('open')) {
            closePostModal();
        }
    });

    /* ── UPLOAD GAMBAR + PREVIEW ──────────────────────────────── */
    let _selectedFile = null;
    let _previewDataUrl = null;

    function handleFile(file) {
        if (!file || !file.type.startsWith('image/')) {
            showToast('File harus berupa gambar (PNG, JPG, WEBP).', true);
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            showToast('Ukuran gambar maksimal 10 MB.', true);
            return;
        }

        _selectedFile = file;

        const reader = new FileReader();
        reader.onload = function (ev) {
            _previewDataUrl = ev.target.result;

            pmPreviewImg.src = _previewDataUrl;
            pmPreviewImg.classList.add('visible');
            pmUploadIcon.style.display = 'none';
            pmUploadText.style.display = 'none';

            const sizeKB = (file.size / 1024).toFixed(0);
            pmPreviewInfo.textContent = `${file.name} — ${sizeKB} KB`;
            pmPreviewInfo.classList.add('visible');
        };
        reader.readAsDataURL(file);
    }

    pmImageInput.addEventListener('change', e => {
        if (e.target.files && e.target.files[0]) {
            handleFile(e.target.files[0]);
        }
    });

    // Drag & drop
    pmUploadArea.addEventListener('dragover', e => {
        e.preventDefault();
        pmUploadArea.classList.add('dragover');
    });

    pmUploadArea.addEventListener('dragleave', () => {
        pmUploadArea.classList.remove('dragover');
    });

    pmUploadArea.addEventListener('drop', e => {
        e.preventDefault();
        pmUploadArea.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        handleFile(file);
    });

    /* ── TOGGLE HARGA GRATIS ──────────────────────────────────── */
    pmFreeCheck.addEventListener('change', () => {
        if (pmFreeCheck.checked) {
            pmPrice.value = '';
            pmPrice.disabled = true;
            pmPrice.placeholder = 'Gratis';
        } else {
            pmPrice.disabled = false;
            pmPrice.placeholder = '0';
        }
    });

    /* ── VALIDASI FORM ────────────────────────────────────────── */
    function validateForm() {
        if (!_selectedFile) {
            showToast('Pilih gambar terlebih dahulu.', true);
            return false;
        }
        if (!pmTitle.value.trim()) {
            pmTitle.focus();
            showToast('Judul karya tidak boleh kosong.', true);
            return false;
        }
        if (!pmTags.value.trim()) {
            pmTags.focus();
            showToast('Tambahkan minimal satu hashtag.', true);
            return false;
        }
        return true;
    }

    /* ── FORMAT HASHTAG (pastikan diawali #) ──────────────────── */
    function normalizeHashtags(raw) {
        return raw
            .split(/\s+/)
            .filter(Boolean)
            .map(tag => (tag.startsWith('#') ? tag : '#' + tag))
            .join(' ');
    }

    /* ── FORMAT HARGA ─────────────────────────────────────────── */
    function formatPrice(val) {
        if (pmFreeCheck.checked || !val || Number(val) === 0) return 'Gratis';
        return 'Rp ' + Number(val).toLocaleString('id-ID');
    }

    /* ── BUAT KARTU POSTINGAN DAN INJECT KE ART-GRID ─────────── */
    function createPostCard(data) {
        const card = document.createElement('div');
        card.className = 'art-card' + (data.isNsfw ? ' is-nsfw' : '');
        card.style.cursor = 'pointer';

        const img = document.createElement('img');
        img.src = data.imageSrc;
        img.alt = data.title;
        card.appendChild(img);

        // Badge NSFW
        if (data.isNsfw) {
            const nsfwBadge = document.createElement('span');
            nsfwBadge.className = 'nsfw-badge';
            nsfwBadge.textContent = '18+';
            card.appendChild(nsfwBadge);
        }

        // Badge harga
        if (data.price !== 'Gratis') {
            const priceBadge = document.createElement('span');
            priceBadge.className = 'price-badge';
            priceBadge.textContent = data.price;
            card.appendChild(priceBadge);
        }

        // Info overlay
        const info     = document.createElement('div');
        info.className = 'art-info';
        const pTags    = document.createElement('p');
        pTags.className   = 'hashtags';
        pTags.textContent = data.tags;
        const pArtist  = document.createElement('p');
        pArtist.className   = 'artist-name';
        pArtist.textContent = '@' + (Store.get('artistUsername') || 'saya');
        info.appendChild(pTags);
        info.appendChild(pArtist);
        card.appendChild(info);


        // Inject ke awal grid
        const grid = $('.art-grid');
        if (grid) {
            grid.insertBefore(card, grid.firstChild);
        }

        // Pasang event untuk modal karya (dari art-modal.js)
        card.addEventListener('click', e => {
            e.stopPropagation();
            // Trigger art-modal jika tersedia
            if (typeof openModal === 'function') {
                openModal(card);
            }
        });
    }

    /* ── RESET FORM ───────────────────────────────────────────── */
    function resetForm() {
        _selectedFile = null;
        _previewDataUrl = null;

        pmImageInput.value = '';
        pmPreviewImg.src = '';
        pmPreviewImg.classList.remove('visible');
        pmPreviewInfo.classList.remove('visible');
        pmPreviewInfo.textContent = '';
        pmUploadIcon.style.display = '';
        pmUploadText.style.display = '';

        pmTitle.value = '';
        pmDesc.value = '';
        pmTags.value = '';
        pmPrice.value = '';
        pmPrice.disabled = false;
        pmFreeCheck.checked = false;
        pmNsfw.checked = false;
    }

    /* ── SUBMIT POSTINGAN KE SERVER ───────────────────────────── */
    pmSubmitBtn.addEventListener('click', async () => {
        if (!validateForm()) return;

        pmSubmitBtn.disabled = true;
        pmSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengunggah...';

        try {
            const formData = new FormData();
            formData.append('image', _selectedFile);
            formData.append('title', pmTitle.value.trim());
            formData.append('description', pmDesc.value.trim());
            formData.append('tags', pmTags.value.trim());
            formData.append('price', pmPrice.value || 0);
            formData.append('is_free', pmFreeCheck.checked ? 1 : 0);
            formData.append('is_nsfw', pmNsfw.checked ? 1 : 0);

            const response = await fetch('api/create-post.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();
            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Gagal mengunggah postingan.');
            }

            const postData = {
                imageSrc : result.data.image_url,
                title    : result.data.title,
                desc     : result.data.description,
                tags     : result.data.tags.map(tag => '#' + tag).join(' '),
                price    : result.data.is_free ? 'Gratis' : formatPrice(result.data.price),
                isNsfw   : result.data.is_nsfw,
            };

            createPostCard(postData);
            closePostModal();
            resetForm();

            showToast(result.message || 'Postingan berhasil diunggah!', false);
        } catch (error) {
            console.error('create-post error', error);
            showToast(error.message || 'Gagal mengunggah postingan.', true);
        } finally {
            pmSubmitBtn.disabled = false;
            pmSubmitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Post Sekarang';
        }
    });

    /* ── TOAST HELPER ─────────────────────────────────────────── */
    let _toastTimer = null;

    function showToast(msg, isError) {
        if (_toastTimer) clearTimeout(_toastTimer);
        pmToast.style.borderColor = isError ? '#ff4444' : '#8e54e9';
        pmToastMsg.textContent = msg;
        pmToast.classList.add('show');
        _toastTimer = setTimeout(() => pmToast.classList.remove('show'), 3000);
    }

    /* ── NSFW FILTER GLOBAL (bisa diaktifkan dari settings) ───── */
    // Jika user mengaktifkan filter NSFW, tambahkan class ke body
    // Ini bisa dihubungkan ke toggle settings di profil nanti
    const savedFilter = Store.get('nsfwFilter');
    if (savedFilter === 'on') {
        document.body.classList.add('nsfw-filter');
    }

    // Fungsi publik untuk toggle filter (dipanggil dari halaman profil)
    window.setNsfwFilter = function (enabled) {
        Store.set('nsfwFilter', enabled ? 'on' : 'off');
        document.body.classList.toggle('nsfw-filter', enabled);
    };

})();