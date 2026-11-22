'use strict';

// [CFG-01] PENGATURAN: ubah durasi cache dan aturan Aura di bagian ini.
const CACHE_CONFIG = {
  key: 'flipplay.catalog.kitsu.v2',
  freshForMs: 60 * 60 * 1000,
  maxAgeMs: 7 * 24 * 60 * 60 * 1000,
};
const AURA_GENRES = {
  energy: ['Action', 'Adventure', 'Sports'],
  melancholy: ['Drama', 'Romance'],
};
const BINGE_MAX_EPISODES = 26;
const auraDescriptions = {
  all: 'Semua anime dalam katalog yang dimuat.',
  energy: `High Energy: genre ${AURA_GENRES.energy.join(', ')}.`,
  melancholy: `Melancholy: genre ${AURA_GENRES.melancholy.join(', ')}.`,
  binge: `Binge Mode: anime tamat dengan 1–${BINGE_MAX_EPISODES} episode. Cocok untuk maraton singkat.`,
};

// [DOM-01] PENGHUBUNG HTML: selector harus cocok dengan id di vibe.html.
const ui = {
  search: document.querySelector('#search'),
  searchHelp: document.querySelector('#search-help'),
  auraButtons: document.querySelectorAll('[data-aura]'),
  auraHelp: document.querySelector('#aura-help'),
  grid: document.querySelector('#media-grid'),
  count: document.querySelector('#result-count'),
  loading: document.querySelector('#loading'),
  empty: document.querySelector('#empty'),
  errorPanel: document.querySelector('#error'),
  errorMessage: document.querySelector('#error-message'),
  sourceLabel: document.querySelector('#source-label'),
  sourceStatus: document.querySelector('#source-status'),
  refreshButton: document.querySelector('#refresh'),
  retryButton: document.querySelector('#retry'),
  resetButton: document.querySelector('#reset'),
};

// [STATE-01] INGATAN HALAMAN: data asli tidak diubah ketika difilter.
const state = {
  media: [],
  activeAura: 'all',
  isLoading: false,
  hasError: false,
};

// [FILTER-01] INPUT TEKS -> true/false: cocokkan semua variasi judul.
function matchesSearch(anime, query) {
  const titles = [
    anime.title,
    anime.title_english,
    anime.title_japanese,
    ...anime.titles.map((entry) => entry.title),
  ];

  return titles.some((title) => {
    return String(title ?? '')
      .toLocaleLowerCase()
      .includes(query);
  });
}

// [FILTER-02] INPUT AURA -> true/false: satu anime diperiksa pada setiap panggilan.
function matchesAura(anime, aura) {
  if (aura === 'binge') {
    return (
      anime.status === 'Finished Airing' &&
      anime.episodes >= 1 &&
      anime.episodes <= BINGE_MAX_EPISODES
    );
  }

  const allowedGenres = AURA_GENRES[aura];
  if (!allowedGenres) return true; // Aura 'all' tidak membatasi genre.

  return anime.genres.some((genre) => allowedGenres.includes(genre.name));
}

// [SAFE-01] Data API harus tetap berupa teks; URL hanya boleh HTTPS.
function escapeHTML(value) {
  const replacements = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  };
  return String(value ?? '').replace(/[&<>"']/g, (character) => replacements[character]);
}

function safeURL(value) {
  try {
    const url = new URL(value);
    return url.protocol === 'https:' ? url.href : '';
  } catch {
    return '';
  }
}

// [CARD-01] SATU OBJECT ANIME -> SATU STRING HTML (template literal).
function createCard(anime) {
  const title = anime.title_english || anime.title;
  const poster = safeURL(anime.images?.webp?.large_image_url);
  const detailURL = safeURL(anime.url);
  const genres = anime.genres
    .slice(0, 3)
    .map((genre) => genre.name)
    .join(' · ');
  const posterHTML = poster
    ? `<img src="${escapeHTML(poster)}" alt="Poster ${escapeHTML(title)}" loading="lazy" width="400" height="600">`
    : '<div class="poster-fallback">Poster belum tersedia</div>';
  const detailHTML = detailURL
    ? `<a class="detail-link" href="${escapeHTML(detailURL)}" target="_blank" rel="noopener noreferrer">Detail di Kitsu ↗</a>`
    : '';

  return `
    <article class="media-card">
      <div class="poster-wrap">
        ${posterHTML}
        <span class="score">★ ${escapeHTML(anime.score ?? '—')}</span>
      </div>
      <div class="card-content">
        <p class="card-meta">${escapeHTML(anime.type || 'Anime')} · ${escapeHTML(anime.episodes ?? '?')} episode</p>
        <h2>${escapeHTML(title)}</h2>
        <p class="card-genres">${escapeHTML(genres || 'Genre belum tersedia')}</p>
        <p class="synopsis">${escapeHTML(anime.synopsis || 'Sinopsis belum tersedia.')}</p>
        ${detailHTML}
      </div>
    </article>`;
}

// [RENDER-01] ARRAY ASLI -> FILTER SEARCH + AURA -> HTML -> OUTPUT DI HALAMAN.
function renderMedia() {
  if (state.hasError || (state.isLoading && !state.media.length)) return;

  const query = ui.search.value.trim().toLocaleLowerCase();
  const visibleMedia = state.media.filter((anime) => {
    return matchesSearch(anime, query) && matchesAura(anime, state.activeAura);
  });

  ui.grid.innerHTML = visibleMedia.map(createCard).join('');
  ui.empty.hidden = visibleMedia.length !== 0;
  ui.count.textContent = `${visibleMedia.length} dari ${state.media.length} anime ditampilkan`;
}

// [CACHE-01] Cache dibaca hanya bila umur dan struktur datanya valid.
function isCachedAnimeValid(anime) {
  return (
    anime &&
    typeof anime.title === 'string' &&
    Array.isArray(anime.genres) &&
    anime.genres.every((genre) => typeof genre?.name === 'string') &&
    Array.isArray(anime.titles) &&
    anime.titles.every((entry) => typeof entry?.title === 'string')
  );
}

function readCache() {
  try {
    const cached = JSON.parse(localStorage.getItem(CACHE_CONFIG.key));
    const age = Date.now() - cached?.fetchedAt;
    const isValidAge = Number.isFinite(age) && age >= 0 && age <= CACHE_CONFIG.maxAgeMs;
    const isValidData =
      Array.isArray(cached?.data) &&
      cached.data.length <= FlipPlayCatalog.config.pageSize &&
      cached.data.every(isCachedAnimeValid);

    if (!isValidAge || !isValidData || cached.sourceId !== 'kitsu') return null;
    return cached;
  } catch {
    return null; // Cache rusak atau storage diblokir: tetap bisa memakai API.
  }
}

// [CACHE-02] Penyimpanan boleh gagal tanpa menggagalkan tampilan katalog.
function saveCache(catalog) {
  try {
    localStorage.setItem(CACHE_CONFIG.key, JSON.stringify(catalog));
  } catch {
    // Storage privat/penuh: katalog tetap tampil selama halaman terbuka.
  }
}

// [DATA-01] HASIL API/CACHE -> STATE + keterangan sumber.
function showCatalog(catalog, fromCache = false) {
  state.media = catalog.data;
  state.hasError = false;
  ui.errorPanel.hidden = true;
  ui.sourceLabel.textContent = `Katalog anime · Data dari ${catalog.source}`;
  ui.searchHelp.textContent = `Pencarian langsung pada ${state.media.length} anime yang telah dimuat, termasuk judul alternatif.`;

  const savedAt = new Date(catalog.fetchedAt).toLocaleString('id-ID');
  ui.sourceStatus.textContent = fromCache
    ? `Menampilkan cache ${catalog.source} yang disimpan ${savedAt}. Klik Muat ulang data untuk mengambil data terbaru.`
    : 'Data terbaru berhasil dimuat langsung dari Kitsu.';
}

// [LOAD-01] Satu tempat untuk menyamakan state loading dan tampilan tombol.
function setLoading(isLoading) {
  state.isLoading = isLoading;
  ui.loading.hidden = !isLoading;
  ui.refreshButton.disabled = isLoading;
  ui.retryButton.disabled = isLoading;
  ui.grid.setAttribute('aria-busy', String(isLoading));
}

// [ERROR-01] Jika ada data, pertahankan; jika tidak ada, tampilkan pesan error.
function showLoadError(error, cached) {
  if (state.media.length || cached?.data.length) {
    if (!state.media.length) showCatalog(cached, true);
    ui.sourceStatus.textContent =
      'Pembaruan gagal. Data yang sebelumnya berhasil dimuat tetap tersedia. Klik Muat ulang data untuk mencoba kembali.';
    return;
  }

  state.hasError = true;
  ui.grid.innerHTML = '';
  ui.errorPanel.hidden = false;
  ui.errorMessage.textContent = error.message;
  ui.count.textContent = 'Katalog tidak tersedia';
  ui.sourceStatus.textContent = '';
}

// [LOAD-02] ALUR UTAMA: cache -> loading -> fetch -> simpan -> render.
async function loadMedia({ forceRefresh = false } = {}) {
  if (state.isLoading) return;

  const cached = readCache();
  if (!forceRefresh && cached) {
    showCatalog(cached, true);
    setLoading(false);
    renderMedia();
    if (Date.now() - cached.fetchedAt < CACHE_CONFIG.freshForMs) return;
  }

  state.hasError = false;
  ui.errorPanel.hidden = true;
  ui.empty.hidden = true;
  setLoading(true);
  if (!state.media.length) ui.count.textContent = 'Memuat katalog…';

  try {
    const catalog = await FlipPlayCatalog.fetchCatalog();
    showCatalog(catalog);
    saveCache(catalog);
  } catch (error) {
    showLoadError(error, cached);
  } finally {
    setLoading(false);
    renderMedia();
  }
}

// [AURA-01] Klik Aura mengubah pilihan, deskripsi, lalu hasil filter.
function selectAura(aura) {
  if (!Object.hasOwn(auraDescriptions, aura)) return;
  state.activeAura = aura;
  ui.auraButtons.forEach((button) => {
    button.setAttribute('aria-pressed', String(button.dataset.aura === aura));
  });
  ui.auraHelp.textContent = auraDescriptions[aura];
  renderMedia();
}

// [EVENT-01] INPUT PENCARIAN -> renderMedia().
function handleSearchInput() {
  renderMedia();
}

// [EVENT-02] KLIK AURA -> baca data-aura HTML -> selectAura().
function handleAuraClick(event) {
  selectAura(event.currentTarget.dataset.aura);
}

// [EVENT-03] RESET -> hapus query -> Semua Aura -> renderMedia().
function resetDiscovery() {
  ui.search.value = '';
  selectAura('all');
  ui.search.focus();
}

// [EVENT-04] MUAT ULANG / COBA LAGI -> abaikan kesegaran cache -> API.
function refreshCatalog() {
  return loadMedia({ forceRefresh: true });
}

// [EVENT-05] GAMBAR GAGAL -> teks pengganti, tanpa inline event handler.
function handlePosterError(event) {
  if (event.target.tagName !== 'IMG') return;
  const replacement = document.createElement('div');
  replacement.className = 'poster-fallback';
  replacement.textContent = 'Poster tidak dapat dimuat';
  event.target.replaceWith(replacement);
}

// [BOOT-01] TITIK MULAI: pasang event, kemudian ambil/tampilkan katalog.
function initVibeGrid() {
  ui.search.addEventListener('input', handleSearchInput);
  ui.auraButtons.forEach((button) => button.addEventListener('click', handleAuraClick));
  ui.resetButton.addEventListener('click', resetDiscovery);
  ui.refreshButton.addEventListener('click', refreshCatalog);
  ui.retryButton.addEventListener('click', refreshCatalog);
  ui.grid.addEventListener('error', handlePosterError, true);
  ui.auraHelp.textContent = auraDescriptions.all;
  loadMedia();
}

initVibeGrid();
