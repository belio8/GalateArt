(function () {
  'use strict';

  const PAGE_SIZE   = 8;

  /* ── State ──────────────────────────────────────────────────────────────── */
  let state = {
    query:      '',   // teks pencarian bebas
    activeTag:  '',   // tag yang dipilih (chip / sidebar)
    activeType: '',   // filter tipe ('', 'Illustration', ...)
    sort:       'newest',
    page:       1,
    total:      0,
    posts:      [],   // data dari server
  };

  /* ── DOM refs ───────────────────────────────────────────────────────────── */
  const searchInput     = document.getElementById('tagSearchInput');
  const btnSearch       = document.getElementById('tagBtnSearch');
  const grid            = document.getElementById('tagGrid');
  const emptyState      = document.getElementById('tagEmpty');
  const resultsHeading  = document.getElementById('tagResultsHeading');
  const activeBanner    = document.getElementById('tagActiveBanner');
  const activeBannerTxt = document.getElementById('tagActiveBannerTxt');
  const btnClearTag     = document.getElementById('tagBtnClearTag');
  const btnLoadMore     = document.getElementById('tagBtnLoadMore');
  const sortSelect      = document.getElementById('tagSortSelect');
  const filterBtns      = document.querySelectorAll('.ga-tag-filter-btn');
  const chips           = document.querySelectorAll('.ga-tag-chip');
  const trendItems      = document.querySelectorAll('.ga-tag-trend-item');

  /* ── Fetch dari API ─────────────────────────────────────────────────────── */
  async function fetchArtworks(append) {
    const params = new URLSearchParams();
    params.set('sort', state.sort);
    params.set('page', state.page);
    params.set('limit', PAGE_SIZE);

    if (state.activeTag) {
      params.set('tag', state.activeTag);
    }
    if (state.activeType) {
      params.set('type', state.activeType);
    }
    if (state.query) {
      params.set('q', state.query);
    }

    try {
      const res = await fetch('api/posts.php?' + params.toString());
      const data = await res.json();

      if (data.status === 'ok') {
        if (append) {
          state.posts = state.posts.concat(data.posts);
        } else {
          state.posts = data.posts;
        }
        state.total = data.total;
      }
    } catch (err) {
      console.error('Gagal memuat karya:', err);
    }

    renderGrid(!append);
    updateHeading();
    updateActiveBanner();
  }

  /* ── Core render ────────────────────────────────────────────────────────── */
  function renderGrid(reset) {
    if (reset) grid.innerHTML = '';

    if (state.posts.length === 0) {
      emptyState.style.display = 'block';
      btnLoadMore.style.display = 'none';
      return;
    }
    emptyState.style.display = 'none';

    // On reset, render all current posts; on load-more, render only the new batch
    const toRender = reset ? state.posts : state.posts.slice((state.page - 1) * PAGE_SIZE);

    toRender.forEach(art => {
      grid.insertAdjacentHTML('beforeend', buildCard(art));
    });

    // Attach click → open art modal (from art-modal.js)
    grid.querySelectorAll('.art-card:not([data-bound])').forEach(card => {
      card.dataset.bound = '1';
      card.addEventListener('click', () => {
        if (typeof openArtModal === 'function') {
          openArtModal(card);
        }
      });
    });

    const hasMore = state.posts.length < state.total;
    btnLoadMore.style.display = hasMore ? 'inline-block' : 'none';
  }

  function buildCard(art) {
    const tagsHtml = art.tags.join(' ');
    return `
      <div class="art-card"
           data-post-id="${escapeHtml(art.id)}"
           data-img="${escapeHtml(art.img)}"
           data-artist="${escapeHtml(art.artist)}"
           data-avatar-url="${escapeHtml(art.artist_avatar)}"
           data-tags="${escapeHtml(tagsHtml)}"
           data-likes="${art.likes}"
           style="cursor:pointer;">
        <img src="${escapeHtml(art.img)}" alt="Artwork by ${escapeHtml(art.artist)}" loading="lazy">
        <div class="art-info">
          <p class="hashtags">${escapeHtml(tagsHtml)}</p>
          <p class="artist-name">
            <a href="visit-profile.php?user=${escapeHtml(art.artist.replace('@', ''))}" style="color: inherit; text-decoration: none;" onclick="event.stopPropagation();">
                ${escapeHtml(art.artist)}
            </a>
          </p>
        </div>
      </div>`;
  }

  function updateHeading() {
    const total = state.total;
    const label = state.activeTag
      ? `untuk <span>${escapeHtml(state.activeTag)}</span>`
      : state.query
        ? `untuk <span>"${escapeHtml(state.query)}"</span>`
        : 'semua karya';
    resultsHeading.innerHTML = `${total} karya ditemukan ${label}`;
  }

  function updateActiveBanner() {
    if (state.activeTag) {
      activeBannerTxt.innerHTML = `Menampilkan karya dengan tag <strong>${escapeHtml(state.activeTag)}</strong>`;
      activeBanner.classList.add('ga-tag-visible');
    } else {
      activeBanner.classList.remove('ga-tag-visible');
    }
  }

  /* ── Skeleton on first load ─────────────────────────────────────────────── */
  function showSkeletons(n) {
    grid.innerHTML = Array(n).fill('<div class="ga-tag-skeleton"></div>').join('');
  }

  function init() {
    showSkeletons(8);
    state.page = 1;
    fetchArtworks(false);
  }

  /* ── Event: search input & button ───────────────────────────────────────── */
  function doSearch() {
    state.query     = searchInput.value.trim();
    state.activeTag = '';           // texto search bersihkan tag aktif
    state.page      = 1;
    syncChips();
    fetchArtworks(false);
  }

  btnSearch.addEventListener('click', doSearch);
  searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });

  /* ── Event: tag chips ───────────────────────────────────────────────────── */
  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      const tag = chip.dataset.tag;
      state.activeTag = state.activeTag === tag ? '' : tag;
      state.query     = '';
      state.page      = 1;
      searchInput.value = '';
      syncChips();
      fetchArtworks(false);
    });
  });

  function syncChips() {
    chips.forEach(c => {
      c.classList.toggle('ga-tag-chip-active', c.dataset.tag === state.activeTag);
    });
  }

  /* ── Event: sidebar trending tags ──────────────────────────────────────── */
  trendItems.forEach(item => {
    item.addEventListener('click', () => {
      const tag = item.dataset.tag;
      state.activeTag   = tag;
      state.query       = '';
      state.page        = 1;
      searchInput.value = '';
      syncChips();
      fetchArtworks(false);
      // Scroll to results on mobile
      grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  /* ── Event: clear active tag ────────────────────────────────────────────── */
  btnClearTag.addEventListener('click', () => {
    state.activeTag = '';
    state.query     = '';
    state.page      = 1;
    searchInput.value = '';
    syncChips();
    fetchArtworks(false);
  });

  /* ── Event: type filter buttons ─────────────────────────────────────────── */
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.dataset.type || '';
      state.activeType = state.activeType === type ? '' : type;
      state.page = 1;
      filterBtns.forEach(b => b.classList.toggle(
        'ga-tag-filter-active',
        (b.dataset.type || '') === state.activeType
      ));
      fetchArtworks(false);
    });
  });

  /* ── Event: sort select ─────────────────────────────────────────────────── */
  sortSelect.addEventListener('change', () => {
    state.sort = sortSelect.value;
    state.page = 1;
    fetchArtworks(false);
  });

  /* ── Event: load more ───────────────────────────────────────────────────── */
  btnLoadMore.addEventListener('click', () => {
    state.page++;
    fetchArtworks(true);
  });

  /* ── Boot ───────────────────────────────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();