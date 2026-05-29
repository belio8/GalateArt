(function () {
  'use strict';

  /* ── Mock data artwork ──────────────────────────────────────────────────── */
  const ARTWORKS = [
    { id: 1,  img: 'Assets/draw2.png', tags: ['#original', '#illustration', '#character'],   artist: '@rini_draws',    likes: 342,  type: 'Illustration' },
    { id: 2,  img: 'Assets/draw2.png', tags: ['#fantasy', '#character', '#digitalart'],      artist: '@seniman_ark',   likes: 210,  type: 'Character' },
    { id: 3,  img: 'Assets/draw2.png', tags: ['#vtuber', '#chibi', '#fanart'],               artist: '@chibi_studio',  likes: 891,  type: 'Chibi' },
    { id: 4,  img: 'Assets/draw2.png', tags: ['#landscape', '#environment', '#concept'],     artist: '@env_art',       likes: 134,  type: 'Background' },
    { id: 5,  img: 'Assets/draw2.png', tags: ['#portrait', '#realistic', '#original'],       artist: '@realis_art',    likes: 567,  type: 'Portrait' },
    { id: 6,  img: 'Assets/draw2.png', tags: ['#chibi', '#cute', '#vtuber'],                 artist: '@kawaii_works',  likes: 729,  type: 'Chibi' },
    { id: 7,  img: 'Assets/draw2.png', tags: ['#fanart', '#anime', '#character'],            artist: '@anime_hub',     likes: 445,  type: 'Fanart' },
    { id: 8,  img: 'Assets/draw2.png', tags: ['#concept', '#scifi', '#environment'],         artist: '@sci_concept',   likes: 198,  type: 'Concept' },
    { id: 9,  img: 'Assets/draw2.png', tags: ['#illustration', '#cute', '#sticker'],         artist: '@sticker_pop',   likes: 312,  type: 'Illustration' },
    { id: 10, img: 'Assets/draw2.png', tags: ['#portrait', '#character', '#fantasy'],        artist: '@myth_artist',   likes: 653,  type: 'Portrait' },
    { id: 11, img: 'Assets/draw2.png', tags: ['#original', '#webtoon', '#comic'],            artist: '@toon_collab',   likes: 88,   type: 'Webtoon' },
    { id: 12, img: 'Assets/draw2.png', tags: ['#vtuber', '#rigging', '#live2d'],             artist: '@rig_studio',    likes: 417,  type: 'Live2D' },
    { id: 13, img: 'Assets/draw2.png', tags: ['#emoji', '#sticker', '#cute'],                artist: '@emote_lab',     likes: 995,  type: 'Emote' },
    { id: 14, img: 'Assets/draw2.png', tags: ['#landscape', '#fantasy', '#painting'],       artist: '@paint_dreams',  likes: 274,  type: 'Painting' },
    { id: 15, img: 'Assets/draw2.png', tags: ['#anime', '#fanart', '#illustration'],        artist: '@sakura_art',    likes: 502,  type: 'Illustration' },
    { id: 16, img: 'Assets/draw2.png', tags: ['#concept', '#character', '#originalart'],    artist: '@oc_workshop',   likes: 361,  type: 'Character' },
  ];

  const PAGE_SIZE   = 8;
  const SORT_FUNCS  = {
    newest:  (a, b) => b.id - a.id,
    popular: (a, b) => b.likes - a.likes,
    oldest:  (a, b) => a.id - b.id,
  };

  /* ── State ──────────────────────────────────────────────────────────────── */
  let state = {
    query:      '',   // teks pencarian bebas
    activeTag:  '',   // tag yang dipilih (chip / sidebar)
    activeType: '',   // filter tipe ('', 'Illustration', ...)
    sort:       'newest',
    page:       1,
    filtered:   [],
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

  /* ── Core filter & render ───────────────────────────────────────────────── */
  function filterArtworks() {
    const q   = state.query.toLowerCase().trim();
    const tag = state.activeTag.toLowerCase();

    state.filtered = ARTWORKS.filter(art => {
      const tagMatch  = !tag || art.tags.some(t => t.toLowerCase() === tag);
      const typeMatch = !state.activeType || art.type === state.activeType;
      const qMatch    = !q || art.tags.some(t => t.toLowerCase().includes(q))
                            || art.artist.toLowerCase().includes(q)
                            || art.type.toLowerCase().includes(q);
      return tagMatch && typeMatch && qMatch;
    });

    state.filtered.sort(SORT_FUNCS[state.sort] || SORT_FUNCS.newest);
    state.page = 1;
    renderGrid(true);
    updateHeading();
    updateActiveBanner();
  }

  function renderGrid(reset) {
    const slice = state.filtered.slice(0, state.page * PAGE_SIZE);

    if (reset) grid.innerHTML = '';

    if (state.filtered.length === 0) {
      emptyState.style.display = 'block';
      btnLoadMore.style.display = 'none';
      return;
    }
    emptyState.style.display = 'none';

    // Only render newly visible cards (avoid full re-render on load-more)
    const start = reset ? 0 : (state.page - 1) * PAGE_SIZE;
    slice.slice(start).forEach(art => {
      grid.insertAdjacentHTML('beforeend', buildCard(art));
    });

    // Attach click → open art modal (from art-modal.js)
    grid.querySelectorAll('.art-card:not([data-bound])').forEach(card => {
      card.dataset.bound = '1';
      card.addEventListener('click', () => {
        if (typeof openArtModal === 'function') {
          openArtModal({
            img:    card.dataset.img,
            artist: card.dataset.artist,
            tags:   card.dataset.tags,
          });
        }
      });
    });

    const hasMore = slice.length < state.filtered.length;
    btnLoadMore.style.display = hasMore ? 'inline-block' : 'none';
  }

  function buildCard(art) {
    const tagsHtml = art.tags.join(' ');
    return `
      <div class="art-card"
           data-img="${art.img}"
           data-artist="${art.artist}"
           data-tags="${tagsHtml}"
           style="cursor:pointer;">
        <img src="${art.img}" alt="Artwork by ${art.artist}" loading="lazy">
        <div class="art-info">
          <p class="hashtags">${art.tags.join(' ')}</p>
          <p class="artist-name">${art.artist}</p>
        </div>
      </div>`;
  }

  function updateHeading() {
    const total = state.filtered.length;
    const label = state.activeTag
      ? `untuk <span>${state.activeTag}</span>`
      : state.query
        ? `untuk <span>"${state.query}"</span>`
        : 'semua karya';
    resultsHeading.innerHTML = `${total} karya ditemukan ${label}`;
  }

  function updateActiveBanner() {
    if (state.activeTag) {
      activeBannerTxt.innerHTML = `Menampilkan karya dengan tag <strong>${state.activeTag}</strong>`;
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
    setTimeout(() => {
      filterArtworks();
    }, 600);
  }

  /* ── Event: search input & button ───────────────────────────────────────── */
  function doSearch() {
    state.query     = searchInput.value.trim();
    state.activeTag = '';           // texto search bersihkan tag aktif
    syncChips();
    filterArtworks();
  }

  btnSearch.addEventListener('click', doSearch);
  searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });

  /* ── Event: tag chips ───────────────────────────────────────────────────── */
  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      const tag = chip.dataset.tag;
      state.activeTag = state.activeTag === tag ? '' : tag;
      state.query     = '';
      searchInput.value = '';
      syncChips();
      filterArtworks();
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
      searchInput.value = '';
      syncChips();
      filterArtworks();
      // Scroll to results on mobile
      grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  /* ── Event: clear active tag ────────────────────────────────────────────── */
  btnClearTag.addEventListener('click', () => {
    state.activeTag = '';
    state.query     = '';
    searchInput.value = '';
    syncChips();
    filterArtworks();
  });

  /* ── Event: type filter buttons ─────────────────────────────────────────── */
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.dataset.type || '';
      state.activeType = state.activeType === type ? '' : type;
      filterBtns.forEach(b => b.classList.toggle(
        'ga-tag-filter-active',
        (b.dataset.type || '') === state.activeType
      ));
      filterArtworks();
    });
  });

  /* ── Event: sort select ─────────────────────────────────────────────────── */
  sortSelect.addEventListener('change', () => {
    state.sort = sortSelect.value;
    filterArtworks();
  });

  /* ── Event: load more ───────────────────────────────────────────────────── */
  btnLoadMore.addEventListener('click', () => {
    state.page++;
    renderGrid(false);
  });

  /* ── Boot ───────────────────────────────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();