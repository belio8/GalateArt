'use strict';

(function initEditPostLogic() {
    const editModal = document.getElementById('editPostModal');
    const epmCloseBtn = document.getElementById('epmCloseBtn');
    const epmCancelBtn = document.getElementById('epmCancelBtn');
    const epmSubmitBtn = document.getElementById('epmSubmitBtn');

    const epmPostId = document.getElementById('epmPostId');
    const epmTitle = document.getElementById('epmTitle');
    const epmDesc = document.getElementById('epmDesc');
    const epmTags = document.getElementById('epmTags');
    const epmPrice = document.getElementById('epmPrice');
    const epmFreeCheck = document.getElementById('epmFreeCheck');
    const epmNsfwToggle = document.getElementById('epmNsfwToggle');

    function closeEditModal() {
        const modal = document.getElementById('editPostModal');
        if (modal) modal.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (epmCloseBtn) epmCloseBtn.addEventListener('click', closeEditModal);
    if (epmCancelBtn) epmCancelBtn.addEventListener('click', closeEditModal);

    if (epmFreeCheck) {
        epmFreeCheck.addEventListener('change', () => {
            if (epmFreeCheck.checked) {
                epmPrice.value = '';
                epmPrice.disabled = true;
            } else {
                epmPrice.disabled = false;
            }
        });
    }

    // Global function to open modal
    window.openEditPostModal = async function(postId) {
        const modal = document.getElementById('editPostModal');
        if (!modal) {
            alert("Modal edit tidak ditemukan di halaman ini.");
            return;
        }
        try {
            // Fetch post data
            const res = await fetch(`api/get-post.php?id=${encodeURIComponent(postId)}`);
            const json = await res.json();

            if (json.status !== 'success') {
                alert(json.message || 'Gagal mengambil data postingan.');
                return;
            }

            const data = json.data;
            epmPostId.value = data.id;
            epmTitle.value = data.title;
            epmDesc.value = data.description;
            epmTags.value = data.tags;
            epmPrice.value = data.price > 0 ? data.price : '';
            epmFreeCheck.checked = data.is_free;
            epmNsfwToggle.checked = data.is_nsfw;

            if (data.is_free) {
                epmPrice.disabled = true;
            } else {
                epmPrice.disabled = false;
            }

            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
            epmTitle.focus();

        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat memuat data postingan.');
        }
    };

    // Save Edit
    if (epmSubmitBtn) {
        epmSubmitBtn.addEventListener('click', async () => {
            const title = epmTitle.value.trim();
            const tags = epmTags.value.trim();
        
        if (!title) return alert('Judul karya harus diisi.');
        if (!tags) return alert('Hashtag harus diisi.');

        epmSubmitBtn.disabled = true;
        epmSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        const payload = {
            post_id: epmPostId.value,
            title: title,
            description: epmDesc.value.trim(),
            tags: tags,
            price: epmPrice.value ? parseFloat(epmPrice.value) : 0,
            is_free: epmFreeCheck.checked,
            is_nsfw: epmNsfwToggle.checked
        };

        try {
            const res = await fetch('api/edit-post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();

            if (json.status === 'success') {
                closeEditModal();
                alert('Postingan berhasil diperbarui!');
                window.location.reload(); // Reload to see changes
            } else {
                alert(json.message || 'Gagal menyimpan perubahan.');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat menyimpan perubahan.');
        } finally {
            epmSubmitBtn.disabled = false;
            epmSubmitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
        }
    });
    }

    // Global function to delete post
    window.deletePost = async function(postId) {
        if (!confirm('Apakah Anda yakin ingin menghapus postingan ini secara permanen?')) {
            return;
        }

        try {
            const res = await fetch('api/delete-post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId })
            });
            const json = await res.json();

            if (json.status === 'success') {
                alert('Postingan berhasil dihapus.');
                // Try to close modal if open
                if (window._closeArtModalIfOpen) window._closeArtModalIfOpen();
                window.location.reload();
            } else {
                alert(json.message || 'Gagal menghapus postingan.');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat menghapus postingan.');
        }
    };

})();
