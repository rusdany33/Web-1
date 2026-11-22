# Alur Kode FlipPlay — dari Input sampai Output

Panduan membaca dan mengubah program, diperbarui 18 Oktober 2026.

## Cara memakai kode unik

Buka file di editor, tekan **Ctrl+F**, lalu cari penanda lengkap, misalnya `[EVENT-01]`. Penanda berada pada komentar kode dan tidak memengaruhi jalannya program. Nomor penanda adalah alamat pencarian, bukan urutan baris. Urutan eksekusi mengikuti pemanggilan fungsi.

Mulai belajar dari `[BOOT-01]` pada `src/vibe.js`, lalu ikuti alur di bawah. Jika lupa lokasi file, gunakan Ctrl+P untuk mencari nama filenya.

## Pembagian tanggung jawab file

| File | Isi | Penanda |
| --- | --- | --- |
| `src/vibe.html` | Struktur input, tombol, dan wadah output | `[HTML-01]` sampai `[HTML-07]` |
| `src/vibe.js` | Event, state, filter, cache, dan render | `[BOOT-01]`, `[EVENT-01]`, `[FILTER-01]`, dan seterusnya |
| `src/catalog-api.js` | Request Kitsu dan pengolahan respons | `[API-01]` sampai `[API-08]` |
| `src/landing.js` | Navbar dan validasi form Modul 1 | `[LAND-01]` sampai `[LAND-05]` |
| `src/main.css` | Warna, font, ukuran, dan grid | `[STYLE-01]` sampai `[STYLE-05]` |
| `src/output.css` | CSS hasil build otomatis | Jangan diedit langsung |
| `tests/catalog-api.test.cjs` | Pengujian API memakai respons terkendali | Jalankan `npm test` |

Tidak ada framework baru atau sistem build JavaScript baru. Kedua script katalog tetap dimuat dengan `defer` melalui HTML. Script API dibaca lebih dahulu agar `FlipPlayCatalog` tersedia saat script halaman memakainya.

## 1. Alur pertama kali halaman dibuka

```text
[HTML-01] Browser membaca HTML dan script defer
    ↓
[BOOT-01] initVibeGrid(): memasang event lalu memanggil loadMedia()
    ↓
[LOAD-02] loadMedia()
    ↓
[CACHE-01] readCache()
    ├─ cache segar → [DATA-01] showCatalog() → [RENDER-01] renderMedia()
    ├─ cache lama yang valid → tampilkan dulu, lanjut memperbarui API
    └─ tanpa cache → langsung mengambil API
    ↓
[LOAD-01] setLoading(true): loading muncul, tombol refresh dinonaktifkan
    ↓
[API-07] fetchCatalog() → [API-06] requestCatalog()
    ↓
fetch(URL Kitsu) → response.ok → response.json()
    ↓
[API-05] normalizeKitsu(payload)
    ├─ [API-03] buildCategoryMap(): ID kategori → nama genre
    └─ [API-04] normalizeAnime(): satu data mentah → satu anime
    ↓
[API-07] mengembalikan { data, source, sourceId, fetchedAt }
    ↓
[DATA-01] showCatalog(): catalog.data disimpan ke state.media
    ↓
[CACHE-02] saveCache(): simpan hasil API ke localStorage
    ↓
[LOAD-01] setLoading(false)
    ↓
[RENDER-01] renderMedia() → [CARD-01] createCard() → [HTML-07] card tampil
```

Jika request gagal, `catch` menuju `[ERROR-01] showLoadError()`. Jika masih ada data, data tetap tampil dengan pesan pembaruan gagal. Jika tidak ada data, pesan error muncul. `finally` tetap mematikan loading.

Cache segar berlaku kurang dari satu jam. Cache lama masih boleh digunakan sampai tujuh hari sambil dicoba diperbarui. Cache bukan seluruh website; file HTML/CSS/JS tetap harus tersedia dari Laragon.

## 2. Alur ketika mengetik pencarian

Contoh pengguna mengetik `NARUTO`:

```text
[HTML-02] <input id="search"> menerima teks
    ↓ event input
[EVENT-01] handleSearchInput()
    ↓
[RENDER-01] renderMedia()
    ├─ ui.search.value → "NARUTO"
    ├─ trim().toLocaleLowerCase() → "naruto"
    └─ state.media.filter(...)
          ├─ [FILTER-01] matchesSearch(anime, "naruto") → true/false
          └─ [FILTER-02] matchesAura(anime, state.activeAura) → true/false
    ↓ hanya anime yang memenuhi KEDUA syarat dipertahankan
visibleMedia.map(createCard).join('')
    ↓
[HTML-07] ui.grid.innerHTML menerima HTML card
[HTML-05] ui.count.textContent menerima jumlah hasil
[HTML-06] empty state muncul jika hasilnya 0
```

**Pencarian tidak melakukan fetch.** Program memeriksa data di `state.media`, jadi hanya judul pada maksimal 20 anime yang sudah dimuat yang bisa ditemukan.

Potongan utamanya di `[RENDER-01]`:

```js
const visibleMedia = state.media.filter((anime) => {
  return matchesSearch(anime, query) && matchesAura(anime, state.activeAura);
});

ui.grid.innerHTML = visibleMedia.map(createCard).join('');
```

`filter()` memanggil callback untuk setiap anime. Parameter `anime` adalah nama lokal yang kita tentukan, bukan keyword JavaScript. Nilainya berasal dari satu anggota `state.media`. `&&` berarti judul dan Aura harus sama-sama cocok. Array asli tidak dihapus atau diubah.

## 3. Alur ketika mengeklik Aura

Contoh tombol High Energy:

```html
<button type="button" data-aura="energy">⚡ High Energy</button>
```

```text
[HTML-03] Pengguna mengeklik tombol
    ↓ event click
[EVENT-02] handleAuraClick(event)
    ↓ event.currentTarget.dataset.aura menghasilkan "energy"
[AURA-01] selectAura("energy")
    ├─ state.activeAura = "energy"
    ├─ aria-pressed tombol aktif menjadi "true"
    ├─ auraDescriptions["energy"] → ui.auraHelp.textContent → [HTML-04]
    └─ renderMedia() → [FILTER-02] memeriksa genre → card terpilih tampil
```

`event.currentTarget` adalah tombol tempat listener dipasang. `dataset.aura` membaca atribut HTML `data-aura`. Key `energy` harus sama antara tombol HTML, konfigurasi genre, dan deskripsinya.

**Deskripsi dan aturan filter berbeda tugasnya:** `auraDescriptions` menampilkan keterangan, sedangkan `matchesAura()` menentukan anime mana yang lolos. Mengganti keterangan saja tidak mengubah hasil filter.

## 4. Dari mana anime dan genres berasal?

Misalnya respons Kitsu berikut (contoh bentuk data, bukan katalog tambahan):

```js
{
  data: [{
    id: '101',
    type: 'anime',
    attributes: {
      canonicalTitle: 'Contoh Anime',
      ageRating: 'PG',
      status: 'finished',
      episodeCount: 12
    },
    relationships: {
      categories: { data: [{ type: 'categories', id: '7' }] }
    }
  }],
  included: [{
    type: 'categories',
    id: '7',
    attributes: { title: 'Action' }
  }]
}
```

Di `[API-03]`, ID `7` dicocokkan dengan nama `Action`. Di `[API-04]`, hasilnya menjadi bagian dari object anime:

```js
{
  id: 'kitsu-101',
  title: 'Contoh Anime',
  genres: [{ name: 'Action' }],
  status: 'Finished Airing',
  episodes: 12
  // Properti lain juga diisi oleh normalizeAnime().
}
```

Object ini masuk array `catalog.data`, lalu ke `state.media` di `[DATA-01]`. Saat `filter()` memeriksa satu anggotanya, anggota itu disebut `anime` dan dikirim ke `matchesAura(anime, aura)`.

Pada `[FILTER-02]`:

```js
return anime.genres.some((genre) => allowedGenres.includes(genre.name));
```

- `anime.genres`: daftar object genre milik anime tersebut.
- `genre`: satu object genre yang sedang diperiksa, misalnya `{ name: 'Action' }`.
- `genre.name`: teks `Action` dari data API yang sudah dinormalisasi.
- `allowedGenres`: daftar genre yang diperbolehkan untuk Aura, dari `[CFG-01]`.
- `includes()`: memeriksa apakah teks terdapat pada daftar.
- `some()`: menghasilkan `true` jika minimal satu genre cocok.

`'Action'` merupakan string yang dibandingkan dengan nama dari API, bukan fungsi atau variabel bawaan. Huruf besar/kecil harus cocok. Jika Aura `all` tidak memiliki aturan genre, fungsi mengembalikan `true`.

## 5. Dari object menjadi tampilan card

`[CARD-01] createCard(anime)` menerima satu object dan **mengembalikan string HTML**, bukan langsung menampilkannya. Misalnya judul object akan masuk ke:

```js
<h2>${escapeHTML(title)}</h2>
```

Teks tersebut berada di dalam template literal dengan backtick. Setelah semua object diubah oleh `map(createCard)`, `join('')` menyatukannya. `[RENDER-01]` baru memasukkan string gabungannya ke `ui.grid.innerHTML`.

`[SAFE-01]` mengubah karakter khusus menjadi teks aman dan membatasi URL ke HTTPS. Tetap gunakan `escapeHTML()` untuk nilai API yang disisipkan ke HTML. Jika poster gagal dimuat, `[EVENT-05]` menggantinya dengan teks. Card menampilkan maksimal tiga genre agar ringkas; filter tetap memeriksa semua genre anime.

## 6. Reset, muat ulang, navbar, dan form

| Aksi | Alur penanda | Output |
| --- | --- | --- |
| Reset pencarian & aura | `[EVENT-03]` → `[AURA-01]` → `[RENDER-01]` | Query kosong, Semua Aura, card dihitung ulang |
| Muat ulang data / Coba lagi | `[EVENT-04]` → `[LOAD-02]` dengan `forceRefresh: true` → `[API-07]` | Data API baru; filter pengguna tetap diterapkan |
| Klik Menu pada landing | `[LAND-05]` memasang listener → `[LAND-02]` | Class menu dan aria-expanded berubah |
| Submit form landing | `[LAND-03]` → `reportValidity()` → `formStatus.textContent` | Validasi atau konfirmasi demo |
| Mengubah isian form | `[LAND-04]` | Pesan validasi sebelumnya dihapus |

Form demo tidak mengirim data ke server. `preventDefault()` mencegah pengiriman dan reload bawaan form.

## 7. Peta state dan data

| Nama | Letak | Isi dan tujuan |
| --- | --- | --- |
| `ui` | `[DOM-01]` | Referensi elemen HTML; tidak menyimpan data anime |
| `state.media` | `[STATE-01]` | Array anime asli setelah normalisasi |
| `state.activeAura` | `[STATE-01]` | Key pilihan aktif: all/energy/melancholy/binge |
| `state.isLoading` | `[STATE-01]` | Menandai request sedang berjalan dan mencegah request ganda |
| `state.hasError` | `[STATE-01]` | Menandai kegagalan penuh agar render tidak menimpa error |
| `query` | Lokal di `[RENDER-01]` | Isi search setelah spasi tepi dipangkas dan huruf diperkecil |
| `visibleMedia` | Lokal di `[RENDER-01]` | Array baru hasil pencarian dan Aura |
| `anime` | Parameter fungsi | Satu object anime, bukan seluruh array |
| `payload` | `[API-06]` | Object JSON mentah hasil API |
| `catalog` | `[LOAD-02]` | Object hasil adapter: data, sumber, waktu pengambilan |

## 8. Bagian yang bisa kamu ubah

| Ingin mengubah | Cari penanda | Cara |
| --- | --- | --- |
| Genre High Energy / Melancholy | `[CFG-01]` di vibe.js | Ubah array `AURA_GENRES`; deskripsi kedua Aura ikut dibentuk dari array ini |
| Batas episode Binge | `[CFG-01]` di vibe.js | Ubah `BINGE_MAX_EPISODES`; aturan dan deskripsi ikut berubah |
| Kalimat deskripsi Aura | `[CFG-01]` di vibe.js | Ubah `auraDescriptions` |
| Tambah Aura baru | `[CFG-01]` dan `[HTML-03]` | Tambah daftar genre, deskripsi, dan tombol dengan key yang sama |
| Data/urutan/rating/timeout API | `[API-01]` | Ubah config atau parameter di `buildCatalogURL()`; jumlah data Kitsu maksimal 20 |
| Lama cache | `[CFG-01]` di vibe.js | Ubah freshForMs dan maxAgeMs; satuan milidetik |
| Field yang dicari | `[FILTER-01]` | Ubah daftar `titles` yang diperiksa |
| Tampilan isi card | `[CARD-01]` | Ubah template HTML dan field yang ditampilkan |
| Warna dan font katalog | `[STYLE-02]` | Ubah color, background, font-family di main.css |
| Kolom desktop / HP | `[STYLE-03]` / `[STYLE-05]` | Ubah grid-template-columns |
| Tampilan judul dan poster | `[STYLE-04]` | Ubah selector card-content, poster-wrap, dll. |

Jika mengubah jumlah data, tekan Muat ulang data agar aplikasi tidak memakai cache lama. Jika mengubah struktur data normalisasi, pertimbangkan mengganti key cache agar data versi lama tidak terbaca.

### Latihan: ubah Binge menjadi maksimal 12 episode

Di `[CFG-01]`:

```js
const BINGE_MAX_EPISODES = 12;
```

Simpan, refresh halaman, lalu pilih Binge Mode. Deskripsi dan hasil filter mengikuti nilai tersebut. Data tidak perlu diambil ulang karena aturan bekerja pada data yang sudah dimuat.

### Latihan: tambah Aura Comedy

Di `[CFG-01]`, tambahkan pasangan berikut pada object yang sesuai:

```js
// Di dalam AURA_GENRES:
comedy: ['Comedy'],

// Di dalam auraDescriptions:
comedy: 'Comedy: anime dengan genre Comedy.',
```

Di `[HTML-03]`, tambahkan tombol:

```html
<button type="button" data-aura="comedy" aria-pressed="false">Comedy</button>
```

Tidak perlu listener baru. `[BOOT-01]` memasang listener untuk semua tombol `[data-aura]`, dan `[FILTER-02]` membaca aturan genre berdasarkan key. Contoh ini belum diterapkan; ini latihan perubahan untuk kamu coba.

## 9. Cara menjalankan dan memeriksa perubahan

1. Aktifkan Apache Laragon, buka `http://localhost/Kuliah%20Web/src/vibe.html`.
2. Simpan perubahan JS, lalu refresh browser. Jika masih memakai file lama, tekan Ctrl+F5.
3. Setelah mengubah CSS/HTML/class Tailwind, jalankan `npm run build` atau gunakan `npm run watch` selama mengedit.
4. Jalankan `npm test` untuk pengujian adapter API. Ini bukan pengganti uji interaksi halaman.
5. Uji search, Aura, reset, refresh, dan form setelah perubahan yang berkaitan.
6. Jika ada error, buka DevTools > Console. Cari nama fungsi error lalu Ctrl+F penandanya di file terkait.

Urutan belajar yang disarankan: pencarian → Aura → render card → data API → cache/error. Baca satu alur sampai selesai sebelum berpindah ke alur berikutnya.
