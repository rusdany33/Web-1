// [API-01] PENGATURAN API: jumlah data, rating, dan timeout ada di sini.
(function registerCatalogAPI(root) {
  'use strict';

  const config = Object.freeze({
    endpoint: 'https://kitsu.io/api/edge/anime',
    pageSize: 20,
    timeoutMs: 10000,
    allowedRatings: Object.freeze(['G', 'PG', 'R']),
  });
  const statuses = {
    finished: 'Finished Airing',
    current: 'Currently Airing',
    upcoming: 'Not yet aired',
    unreleased: 'Not yet aired',
    tba: 'Not yet aired',
  };

  function buildCatalogURL() {
    const parameters = new URLSearchParams({
      'page[limit]': String(config.pageSize),
      sort: 'popularityRank',
      include: 'categories',
      'filter[ageRating]': config.allowedRatings.join(','),
    });
    return `${config.endpoint}?${parameters}`;
  }

  // [API-02] VALIDASI NILAI: data kosong/salah tipe diberi nilai pengganti.
  function getText(value) {
    return typeof value === 'string' ? value.trim() : '';
  }

  function getArray(value) {
    return Array.isArray(value) ? value : [];
  }

  function getIdentifier(value) {
    if (typeof value !== 'number' && typeof value !== 'string') return '';
    return String(value).trim();
  }

  function numberOrNull(value, maximum = Infinity) {
    const isNumberOrString = typeof value === 'number' || typeof value === 'string';
    if (!isNumberOrString || String(value).trim() === '') return null;
    const number = Number(value);
    return Number.isFinite(number) && number >= 0 && number <= maximum ? number : null;
  }

  function episodesOrNull(value) {
    const episodes = numberOrNull(value);
    return Number.isInteger(episodes) && episodes > 0 ? episodes : null;
  }

  // [API-03] INCLUDED -> kamus kategori: ID kategori dipasangkan dengan namanya.
  function buildCategoryMap(included) {
    const categories = new Map();
    getArray(included).forEach((category) => {
      const id = getIdentifier(category?.id);
      const name = getText(category?.attributes?.title);
      if (category?.type === 'categories' && id && name) categories.set(id, name);
    });
    return categories;
  }

  function getGenreNames(item, categories) {
    const references = getArray(item.relationships?.categories?.data);
    const names = references
      .filter((reference) => reference?.type === 'categories')
      .map((reference) => categories.get(getIdentifier(reference.id)))
      .filter(Boolean);
    return [...new Set(names)];
  }

  // [API-04] SATU ITEM KITSU -> SATU OBJECT ANIME untuk filter dan card.
  function normalizeAnime(item, categories) {
    const id = getIdentifier(item?.id);
    const attributes = item?.attributes;
    if (item?.type !== 'anime' || !id || !attributes) return null;
    if (!config.allowedRatings.includes(attributes.ageRating) || attributes.nsfw === true)
      return null;

    const hasTitlesObject =
      attributes.titles &&
      typeof attributes.titles === 'object' &&
      !Array.isArray(attributes.titles);
    const titles = hasTitlesObject ? attributes.titles : {};
    const title =
      getText(attributes.canonicalTitle) ||
      getText(titles.en_jp) ||
      getText(titles.en) ||
      getText(titles.en_us) ||
      getText(titles.ja_jp);
    if (!title) return null;

    const aliases = [...Object.values(titles), ...getArray(attributes.abbreviatedTitles)]
      .map(getText)
      .filter(Boolean);
    const rating = numberOrNull(attributes.averageRating, 100);
    const posterURL =
      getText(attributes.posterImage?.large) ||
      getText(attributes.posterImage?.original) ||
      getText(attributes.posterImage?.medium);

    return {
      id: `kitsu-${id}`,
      title,
      title_english: getText(titles.en) || getText(titles.en_us),
      title_japanese: getText(titles.ja_jp),
      titles: [...new Set(aliases)].map((alias) => ({ title: alias })),
      genres: getGenreNames(item, categories).map((name) => ({ name })),
      status: statuses[attributes.status] || '',
      episodes: episodesOrNull(attributes.episodeCount),
      images: { webp: { large_image_url: posterURL } },
      score: rating === null ? null : Math.round(rating) / 10,
      type: getText(attributes.subtype).toUpperCase() || 'Anime',
      synopsis: getText(attributes.synopsis) || getText(attributes.description),
      url: `https://kitsu.io/anime/${encodeURIComponent(getText(attributes.slug) || id)}`,
      sourceName: 'Kitsu',
    };
  }

  // [API-05] RESPON JSON -> ARRAY ANIME VALID: buang item salah dan duplikat.
  function normalizeKitsu(payload) {
    if (!payload || !Array.isArray(payload.data)) {
      throw new Error('Format data Kitsu tidak sesuai.');
    }

    const categories = buildCategoryMap(payload.included);
    const seenIDs = new Set();
    const animeList = [];

    payload.data.forEach((item) => {
      const anime = normalizeAnime(item, categories);
      if (!anime || seenIDs.has(anime.id)) return;
      seenIDs.add(anime.id);
      animeList.push(anime);
    });

    if (payload.data.length && !animeList.length) {
      throw new Error('Tidak ada data anime valid dari Kitsu.');
    }
    return animeList;
  }

  // [API-06] FETCH -> CEK HTTP -> BACA JSON -> NORMALISASI; timer selalu dibersihkan.
  async function requestCatalog(fetchImpl, timeoutMs) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), timeoutMs);

    try {
      const response = await fetchImpl(buildCatalogURL(), {
        signal: controller.signal,
        headers: { Accept: 'application/vnd.api+json' },
      });
      if (!response.ok) throw new Error(`Kitsu merespons HTTP ${response.status}.`);
      const payload = await response.json();
      return normalizeKitsu(payload);
    } catch (error) {
      if (controller.signal.aborted || error?.name === 'AbortError') {
        throw new Error('Kitsu melewati batas waktu tunggu.');
      }
      if (error instanceof TypeError) throw new Error('Kitsu tidak dapat dihubungi.');
      if (error instanceof SyntaxError) throw new Error('Format data Kitsu tidak sesuai.');
      throw error;
    } finally {
      clearTimeout(timeout);
    }
  }

  // [API-07] ARRAY ANIME -> OBJECT KATALOG: hasil dikembalikan ke loadMedia().
  async function fetchCatalog({
    fetchImpl = root.fetch?.bind(root),
    timeoutMs = config.timeoutMs,
  } = {}) {
    try {
      const data = await requestCatalog(fetchImpl, timeoutMs);
      return { data, source: 'Kitsu', sourceId: 'kitsu', fetchedAt: Date.now() };
    } catch (error) {
      const detail = error instanceof Error ? error.message : 'Kitsu gagal memuat data.';
      throw new Error(
        `Katalog belum dapat dimuat. ${detail} Periksa koneksi internet lalu tekan Coba lagi.`,
      );
    }
  }

  // [API-08] Browser memakai FlipPlayCatalog; Node memakai module.exports untuk tes.
  const api = { config, fetchCatalog, normalizeKitsu };
  if (typeof module === 'object' && module.exports) module.exports = api;
  else root.FlipPlayCatalog = api;
})(globalThis);
