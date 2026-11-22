const test = require('node:test');
const assert = require('node:assert/strict');
const { fetchCatalog, normalizeKitsu } = require('../src/catalog-api.js');

const kitsuFixture = () => ({
  data: [
    {
      id: '7442',
      type: 'anime',
      attributes: {
        canonicalTitle: 'Attack on Titan',
        titles: { en: 'Attack on Titan', en_jp: 'Shingeki no Kyojin', ja_jp: '進撃の巨人' },
        abbreviatedTitles: ['AoT'],
        averageRating: '84.41',
        ageRating: 'R',
        nsfw: false,
        status: 'finished',
        episodeCount: 25,
        subtype: 'TV',
        slug: 'attack-on-titan',
        posterImage: { large: 'https://media.kitsu.app/anime/poster_images/7442/large.jpg' },
      },
      relationships: {
        categories: {
          data: [
            { type: 'categories', id: '150' },
            { type: 'categories', id: '184' },
          ],
        },
      },
    },
  ],
  included: [
    { id: '184', type: 'categories', attributes: { title: 'Drama' } },
    { id: '150', type: 'unrelated', attributes: { title: 'Wrong category' } },
    { id: '150', type: 'categories', attributes: { title: 'Action' } },
  ],
});
const ok = (payload) => ({ ok: true, status: 200, json: async () => payload });
const abortable = (signal) =>
  new Promise((resolve, reject) => {
    signal.addEventListener(
      'abort',
      () => reject(Object.assign(new Error('aborted'), { name: 'AbortError' })),
      { once: true },
    );
  });

test('Requests only Kitsu with JSON:API headers and returns source metadata', async () => {
  const calls = [];
  const result = await fetchCatalog({
    fetchImpl: async (url, options) => {
      calls.push(url);
      assert.equal(options.headers.Accept, 'application/vnd.api+json');
      const endpoint = new URL(url);
      assert.equal(endpoint.hostname, 'kitsu.io');
      assert.equal(endpoint.searchParams.get('page[limit]'), '20');
      assert.equal(endpoint.searchParams.get('include'), 'categories');
      assert.equal(endpoint.searchParams.get('filter[ageRating]'), 'G,PG,R');
      return ok(kitsuFixture());
    },
  });
  assert.equal(calls.length, 1);
  assert.equal(result.sourceId, 'kitsu');
  assert.equal(result.source, 'Kitsu');
  assert.equal(result.data[0].title, 'Attack on Titan');
  assert.equal(result.data[0].id, 'kitsu-7442');
  assert.ok(Number.isFinite(result.fetchedAt));
});

for (const failure of [
  'network',
  'HTTP',
  'invalid shape',
  'invalid items',
  'invalid JSON',
  'timeout',
  'body timeout',
]) {
  test(`Kitsu ${failure} exposes an actionable error without another API request`, async () => {
    let calls = 0;
    const fetchImpl = async (url, { signal }) => {
      calls++;
      if (failure === 'network') throw new TypeError('Failed to fetch');
      if (failure === 'HTTP') return { ok: false, status: 429 };
      if (failure === 'invalid shape') return ok({ error: 'service down' });
      if (failure === 'invalid items') return ok({ data: [null, {}] });
      if (failure === 'invalid JSON')
        return {
          ok: true,
          json: async () => {
            throw new SyntaxError('bad JSON');
          },
        };
      if (failure === 'body timeout') return { ok: true, json: () => abortable(signal) };
      return abortable(signal);
    };
    await assert.rejects(fetchCatalog({ fetchImpl, timeoutMs: 8 }), (error) => {
      assert.match(error.message, /Kitsu/);
      assert.match(error.message, /Coba lagi/);
      if (failure === 'HTTP') assert.match(error.message, /HTTP 429/);
      if (failure.includes('timeout')) assert.match(error.message, /batas waktu/);
      return true;
    });
    assert.equal(calls, 1);
  });
}

test('Kitsu category relationships use type and ID, and aliases/status/poster map to the shared model', () => {
  const [item] = normalizeKitsu(kitsuFixture());
  assert.deepEqual(item.genres, [{ name: 'Action' }, { name: 'Drama' }]);
  assert.equal(item.status, 'Finished Airing');
  assert.equal(item.episodes, 25);
  assert.equal(item.score, 8.4);
  assert.equal(item.title_japanese, '進撃の巨人');
  assert.ok(item.titles.some((entry) => entry.title === 'AoT'));
  assert.match(item.images.webp.large_image_url, /^https:\/\/media\.kitsu\.app\//);
  assert.equal(item.url, 'https://kitsu.io/anime/attack-on-titan');
});

test('Kitsu missing ratings stay unknown; explicit zero remains zero; unsupported age ratings are excluded', () => {
  const payload = kitsuFixture();
  payload.data[0].attributes.averageRating = null;
  const noRating = structuredClone(payload.data[0]);
  const zeroRating = structuredClone(payload.data[0]);
  zeroRating.id = '2';
  zeroRating.attributes.averageRating = '0';
  const adult = structuredClone(payload.data[0]);
  adult.id = '3';
  adult.attributes.ageRating = 'R18';
  const unknownAge = structuredClone(payload.data[0]);
  unknownAge.id = '4';
  unknownAge.attributes.ageRating = null;
  const nsfw = structuredClone(payload.data[0]);
  nsfw.id = '5';
  nsfw.attributes.nsfw = true;
  payload.data = [noRating, zeroRating, adult, unknownAge, nsfw, null, {}];
  const items = normalizeKitsu(payload);
  assert.equal(items.length, 2);
  assert.equal(items[0].score, null);
  assert.equal(items[1].score, 0);
});

test('Malformed optional arrays are ignored and valid IDs are deduplicated', () => {
  const kitsu = kitsuFixture();
  kitsu.data[0].relationships.categories.data = {};
  kitsu.data[0].attributes.abbreviatedTitles = {};
  kitsu.data.push(kitsu.data[0], {});
  assert.equal(normalizeKitsu(kitsu).length, 1);
  assert.deepEqual(normalizeKitsu(kitsu)[0].genres, []);
});

test('An explicit empty successful catalog remains an empty success', async () => {
  assert.deepEqual(normalizeKitsu({ data: [] }), []);
  const result = await fetchCatalog({ fetchImpl: async () => ok({ data: [] }) });
  assert.equal(result.sourceId, 'kitsu');
  assert.deepEqual(result.data, []);
});
