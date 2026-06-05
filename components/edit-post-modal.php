<?php if (isset($_SESSION['user_id'])): ?>
<div id="editPostModal" role="dialog" aria-modal="true" aria-labelledby="editPostModalTitle">
    <div class="pm-box">

        <!-- Header -->
        <div class="pm-header">
            <h2 id="editPostModalTitle"><i class="fas fa-edit" style="margin-right:8px;"></i>Edit Postingan</h2>
            <button class="pm-close-btn" id="epmCloseBtn" aria-label="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <input type="hidden" id="epmPostId">

        <!-- Judul -->
        <div class="pm-field">
            <label for="epmTitle">Judul Karya <span class="pm-required">*</span></label>
            <input type="text" id="epmTitle" placeholder="Contoh: Character Design - Sakura Spirit" maxlength="80">
        </div>

        <!-- Deskripsi -->
        <div class="pm-field">
            <label for="epmDesc">Deskripsi</label>
            <textarea id="epmDesc" placeholder="Ceritakan tentang karya ini, proses pembuatan, tool yang digunakan, dsb." rows="3"></textarea>
        </div>

        <!-- Hashtag -->
        <div class="pm-field">
            <label for="epmTags">Hashtag <span class="pm-required">*</span></label>
            <input type="text" id="epmTags" placeholder="#illustration #originalart #digitalpainting">
            <small style="font-size:11px;color:#555566;margin-top:4px;display:block;">Pisahkan dengan spasi. Contoh: #anime #vtuber #fanart</small>
        </div>

        <!-- Harga -->
        <div class="pm-field">
            <label>Harga</label>
            <div class="pm-price-row">
                <input type="number" id="epmPrice" placeholder="0" min="0" step="1000">
                <label class="pm-free-toggle" for="epmFreeCheck">
                    <input type="checkbox" id="epmFreeCheck"> Gratis / Free Download
                </label>
            </div>
            <small style="font-size:11px;color:#555566;margin-top:4px;display:block;">Kosongkan atau centang "Gratis" jika karya ini tidak dijual.</small>
        </div>

        <!-- Filter NSFW -->
        <div class="pm-nsfw-row">
            <i class="fas fa-shield-alt pm-nsfw-icon"></i>
            <div class="pm-nsfw-text">
                <strong>Konten 18+ / NSFW</strong>
                <p>Aktifkan jika postingan ini mengandung konten dewasa.</p>
            </div>
            <label class="pm-switch" aria-label="Toggle NSFW">
                <input type="checkbox" id="epmNsfwToggle">
                <span class="pm-switch-slider"></span>
            </label>
        </div>

        <!-- Footer / Tombol -->
        <div class="pm-footer">
            <button class="pm-btn-cancel" id="epmCancelBtn">Batal</button>
            <button class="pm-btn-post" id="epmSubmitBtn">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>

    </div>
</div>
<script src="js/edit-post.js?v=<?= time() ?>"></script>
<?php endif; ?>
