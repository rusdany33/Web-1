# Modul 2 — FlipPlay: The Vibe Grid

Diperbarui: 18 Oktober 2026. Panduan ini menjelaskan implementasi terbaru yang hanya menggunakan API Kitsu. Kode telah dirapikan menjadi fungsi bernama, object `ui`, dan object `state`.

Untuk belajar dari input sampai output menggunakan penanda Ctrl+F seperti `[EVENT-01]` dan `[CARD-01]`, baca [ALUR-KODE-MODUL-2.md](ALUR-KODE-MODUL-2.md).

## Ringkasan untuk presentasi

Proyek ini mengembangkan landing page FlipPlay Modul 1 dan menambahkan halaman The Vibe Grid. Data anime diambil langsung menggunakan Fetch API dari Kitsu. Data yang berhasil dimuat ditampilkan sebagai card, dapat dicari secara real-time, dan disaring berdasarkan Aura. Cache menyimpan hasil API agar halaman tidak selalu bergantung pada request baru. Halaman tetap memiliki loading state dan pesan error jika API gagal serta tidak ada data yang dapat digunakan.

## Menjalankan

1. Aktifkan Apache di Laragon.
2. Buka `http://localhost/Kuliah%20Web/src/index.html` untuk codelab Modul 1.
3. Klik Jelajahi / Lihat katalog, atau buka `http://localhost/Kuliah%20Web/src/vibe.html` untuk demo Modul 2.
4. Koneksi internet diperlukan untuk pengambilan data pertama dan poster. Aplikasi mengambil maksimal 20 anime populer dari Kitsu. Katalog bukan seluruh database. Kitsu memakai filter rating G/PG/R sesuai klasifikasi penyedia.
5. CSS hasil build sudah disertakan. Setelah mengubah HTML/CSS/class Tailwind: `npm run build`, atau `npm run watch` selama mengedit. Jika dependensi belum ada, jalankan `npm install`.

## Struktur dan tempat mengedit

- `src/index.html`: landing page Modul 1, navbar mobile, dan form kontak.
- `src/landing.js`: event click navbar dan validasi submit form.
- `src/vibe.html`: struktur halaman The Vibe Grid, kolom search, tombol Aura, area status, dan wadah grid.
- `src/vibe.js`: state, cache, filter, template card, dan event handling.
- `src/catalog-api.js`: Fetch API Kitsu, timeout sepuluh detik, dan normalisasi format data.
- `src/main.css`: warna, font, ukuran, grid responsif, dan tampilan state. Tampilan halaman baru memakai selector `.vibe-*`, `.media-card`, dan `.aura-options`.
- `src/output.css`: hasil build, jangan diedit langsung.
- `tests/catalog-api.test.cjs`: pengujian adapter API dan normalisasi data. Jalankan dengan `npm test`.
- `package.json`: perintah `build`, `watch`, dan `test`.

## Alur program

Browser membaca HTML lalu menjalankan `catalog-api.js` dan `vibe.js` melalui `defer`, sehingga elemen DOM tersedia. `loadMedia()` mencari cache yang valid di localStorage. Cache berumur kurang dari satu jam langsung dipakai. Cache lebih lama (maksimal tujuh hari) dapat ditampilkan sambil diperbarui. Tombol **Muat ulang data** selalu mengambil data baru. Cache hanya berisi hasil API yang pernah berhasil dimuat, bukan contoh data buatan.

Jika perlu data baru, loading ditampilkan dan `FlipPlayCatalog.fetchCatalog()` mengirim GET langsung ke Kitsu dengan batas waktu sepuluh detik. Format respons Kitsu diubah menjadi format internal agar fungsi render dan filter dapat membaca judul, genre, status selesai, jumlah episode, poster, dan skor. Skor Kitsu 0–100 dikonversi menjadi skor 0–10 dengan satu angka desimal; misalnya 84,41 ditampilkan sebagai 8,4. Skor yang tidak tersedia tetap dianggap tidak diketahui, bukan nol. Nama sumber dan tautan detail menggunakan Kitsu.

`response.ok` diperiksa karena HTTP 404/500 tidak otomatis ditolak oleh fetch. `response.json()` mengubah JSON menjadi object JavaScript. `payload.data` divalidasi sebagai array; item tidak valid/duplikat dibuang sebelum disimpan di `state.media`. Adapter membersihkan timer pada semua jalur hasil.

Setelah fetch selesai, `finally` menyembunyikan loading dan mengaktifkan tombol muat ulang. Jika API Kitsu gagal dan tidak ada data tersimpan, `catch` menampilkan error beserta tombol Coba lagi. Jika data pernah dimuat, data itu dipertahankan dengan keterangan bahwa pembaruan gagal. Filter tetap berfungsi. Browser yang memblokir localStorage tetap bisa mengambil data secara langsung.

`renderMedia()` membaca nilai pencarian, memangkas spasi, dan menyamakan huruf kecil. `filter()` memilih item yang cocok dengan judul DAN Aura. Judul asli, Inggris, Jepang, dan alternatif ikut dicari. `map(createCard).join('')` mengubah hasil menjadi string HTML dan memasukkannya ke `ui.grid.innerHTML`. Jika hasil kosong, halaman menampilkan petunjuk reset, bukan error jaringan.

`createCard()` memakai template literal (backtick dan `${...}`) untuk memasukkan judul, poster, skor, genre, episode, dan sinopsis. `escapeHTML()` mengubah karakter khusus data API agar dianggap teks, bukan HTML aktif. `safeURL()` membatasi URL poster/detail ke HTTPS. Poster yang gagal dimuat mendapatkan teks pengganti.

## Definisi Aura

Aura adalah aturan buatan aplikasi berdasarkan metadata API, bukan hasil analisis emosi atau kategori bawaan API. Satu anime bisa cocok dengan beberapa Aura.

| Aura | Aturan di `matchesAura()` |
| --- | --- |
| Semua Aura | Semua data yang dimuat |
| High Energy | Salah satu genre Action, Adventure, Sports |
| Melancholy | Salah satu genre Drama, Romance |
| Binge Mode | Status Finished Airing dan 1–26 episode |

Search dan klik Aura tidak melakukan fetch ulang. Data sumber tetap tersimpan dalam `state.media`; hasil filter merupakan array baru. Tombol Reset mengosongkan query serta mengembalikan Semua Aura. Karena endpoint yang dipilih adalah anime, halaman menampilkan anime; tidak mengklaim menyediakan drama live action atau manga.

## Pemetaan rubrik

| Aspek | Implementasi |
| --- | --- |
| Codelab: interaksi Modul 1 (5%) | `landing.js`: navbar toggle mobile dan validasi form |
| Codelab: public API (5%) | Halaman baru `vibe.html`, GET Kitsu, card, loading, error |
| Event handling minimal dua jenis (15%) | `input` pada search, `click` pada Aura/reset/retry/muat ulang, `submit` pada form landing |
| Fetch dan render (20%) | `fetchCatalog()` dan `requestCatalog()` mengambil API; `loadMedia()`, `createCard()`, `renderMedia()` menampilkan hasil |
| Loading dan error (15%) | `#loading`, `#error`, try/catch/finally, pengecekan response.ok |
| Search dan Aura (15%) | `renderMedia()`, `matchesAura()`, `selectAura()` |
| Template literal dan array method (10%) | Template HTML card; filter, map, forEach, some; Set untuk menghapus duplikat |
| Kesesuaian The Vibe Grid (5%) | Identitas FlipPlay, grid responsif, Unified Search, Aura-Based Discovery |
| Pemahaman (10%) | Pelajari penjelasan dan tanya jawab di bawah; nilai ditentukan saat demonstrasi |

## Fungsi penting dan lokasi logikanya

| Fungsi | File | Peran |
| --- | --- | --- |
| `fetchCatalog()` | `src/catalog-api.js` | Mengambil data Kitsu beserta nama sumber dan waktu pengambilan |
| `requestCatalog()` | `src/catalog-api.js` | Fetch, timeout, pemeriksaan HTTP, parsing JSON, dan pembersihan timer |
| `normalizeKitsu()` | `src/catalog-api.js` | Menyamakan format Kitsu, kategori, status, poster, dan skor |
| `loadMedia()` | `src/vibe.js` | Mengatur cache, loading, request, error, dan render akhir |
| `readCache()` | `src/vibe.js` | Membaca serta memvalidasi cache dari localStorage |
| `showCatalog()` | `src/vibe.js` | Menyimpan data ke state dan memperbarui keterangan sumber |
| `renderMedia()` | `src/vibe.js` | Menyaring berdasarkan search dan Aura lalu memperbarui grid serta jumlah hasil |
| `matchesAura()` | `src/vibe.js` | Memeriksa kecocokan satu anime dengan Aura aktif |
| `createCard()` | `src/vibe.js` | Membuat HTML card menggunakan template literal |
| `selectAura()` | `src/vibe.js` | Mengubah Aura aktif, atribut tombol, dan hasil render |

### Mengapa data Kitsu perlu dinormalisasi?

Kitsu menempatkan informasi anime di dalam `attributes`. Genre tersedia sebagai relasi kategori yang perlu dicocokkan dengan bagian `included`. Fungsi `normalizeKitsu()` membuat struktur internal yang lebih mudah digunakan oleh tampilan dan filter.

| Data aplikasi | Asal data Kitsu |
| --- | --- |
| `id` | ID anime dengan awalan `kitsu-` |
| `title`, `title_english`, `title_japanese` | `attributes.canonicalTitle` dan `attributes.titles` |
| `genres[].name` | Relasi `relationships.categories.data` dicocokkan dengan `included` berdasarkan type dan ID |
| `episodes` | `attributes.episodeCount` |
| `status` | `attributes.status: "finished"` diubah menjadi `Finished Airing` |
| `score` | `attributes.averageRating` dikonversi dari skala 0–100 ke 0–10 |
| Poster | `attributes.posterImage` |
| Detail | URL anime di Kitsu |

Request memakai header `Accept: application/vnd.api+json`. `URLSearchParams` menyusun parameter jumlah data, urutan popularitas, kategori, dan rating. Batas jumlah data adalah 20.

### Contoh potongan kode yang perlu dipahami

Pencarian diperbarui setiap kali pengguna mengetik:

```js
ui.search.addEventListener('input', handleSearchInput);
// handleSearchInput() memanggil renderMedia().
```

Hasil filter dirender menjadi HTML dinamis:

```js
ui.grid.innerHTML = visibleMedia.map(createCard).join('');
```

`map()` menghasilkan array string HTML card. `join('')` menyatukannya sebelum dimasukkan ke DOM. Di dalam `createCard()`, template literal memasukkan nilai data menggunakan `${...}`; data eksternal terlebih dahulu diproses dengan `escapeHTML()`.

Aturan Binge Mode:

```js
anime.status === 'Finished Airing' && anime.episodes >= 1 && anime.episodes <= BINGE_MAX_EPISODES
```

Aturan ini tidak memeriksa jenis TV, sehingga film dengan satu episode juga dapat masuk. Aura merupakan pengelompokan sederhana untuk praktikum, bukan penilaian emosi otomatis.

### Perilaku cache dan kegagalan API

Kunci localStorage: `flipplay.catalog.kitsu.v2`.

| Kondisi | Perilaku aplikasi |
| --- | --- |
| Cache valid berumur kurang dari satu jam | Langsung ditampilkan tanpa request katalog baru |
| Cache valid berumur satu jam sampai tujuh hari | Ditampilkan, lalu aplikasi mencoba memperbarui dari API |
| Cache rusak, tidak valid, atau lebih dari tujuh hari | Diabaikan; aplikasi mengambil data baru |
| Klik Muat ulang data atau Coba lagi | Mengambil data baru meskipun ada cache segar |
| API Kitsu gagal tetapi masih ada data | Data tetap tersedia, disertai pesan pembaruan gagal |
| API Kitsu gagal tanpa data yang dapat dipakai | Pesan error dan tombol Coba lagi ditampilkan |
| Browser menolak localStorage | Request dan filter tetap berfungsi; penyimpanan cache dilewati |

Timeout Kitsu adalah sepuluh detik untuk proses fetch beserta pembacaan body JSON. Tidak ada request ke penyedia lain. Jika gagal, aplikasi memakai data yang masih tersedia atau menampilkan pesan error.

## Urutan demo untuk presentasi

1. Aktifkan Apache Laragon dan buka landing page. Gunakan lebar mobile, klik Menu untuk membuka/menutup navbar.
2. Submit form kosong atau email salah. Tunjukkan validasi; isi data benar lalu submit lagi. Jelaskan bahwa pesan hanya divalidasi di frontend dan tidak dikirim ke server.
3. Klik Jelajahi. Jika memakai cache, tunjukkan keterangan cache. Klik **Muat ulang data** untuk menunjukkan loading dan request baru. Tunjukkan keterangan sumber Kitsu.
4. Ketik salah satu judul yang terlihat. Card berubah langsung tanpa refresh. Coba huruf kecil/besar dan judul alternatif.
5. Pilih High Energy, Melancholy, dan Binge Mode. Jelaskan aturan filter, tombol aktif, serta penghitung hasil.
6. Gabungkan search dengan Aura. Ketik judul yang tidak ada untuk menunjukkan empty state, lalu klik Reset.
7. Buka DevTools > Network dan klik Muat ulang data. Pastikan request langsung menuju Kitsu. Mengetik atau memilih Aura tidak menambah request katalog API.
8. Untuk mendemokan kegagalan pembaruan, blokir hanya request API sebagaimana petunjuk di bawah, lalu klik Muat ulang data. Data yang sudah tampil tetap ada dengan pesan pembaruan gagal.
9. Untuk mendemokan error tanpa cache, hapus cache, tetap blokir API, lalu reload. HTML/CSS/JS lokal harus tetap dapat dimuat. Tunjukkan pesan error dan tombol Coba lagi.
10. Nonaktifkan pemblokiran API, lalu klik Coba lagi. Tunjukkan katalog berhasil dimuat kembali. Sesudah demo, pastikan pemblokiran sudah dimatikan.

### Cara menguji error tanpa memblokir halaman Laragon

Gunakan DevTools Chrome/Edge > menu tiga titik > More tools > Network request blocking. Tambahkan pola berikut dan aktifkan pemblokiran:

```text
*kitsu.io/api/edge/anime*
```

Untuk menghapus cache, buka DevTools > Application > Local Storage > pilih origin halaman > hapus hanya entri `flipplay.catalog.kitsu.v2`. Setelah itu reload dengan pola API masih diblokir. Matikan pemblokiran sebelum klik Coba lagi.

Hindari memilih Offline lalu reload untuk pengujian ini: browser juga dapat memblokir permintaan HTML dari localhost sehingga halaman aplikasinya tidak terbuka. Cache aplikasi hanya menyimpan data katalog, bukan seluruh website.

### Menjalankan pengujian otomatis

Dari folder proyek, jalankan:

```powershell
npm test
```

Pengujian ini memakai respons terkendali untuk memeriksa berbagai kondisi gagal secara konsisten. Pengujian otomatis tersebut berbeda dari pengujian browser dengan data API nyata yang dicatat pada bagian hasil verifikasi.

## Tanya jawab pemahaman

**Apa perbedaan HTML, CSS, JavaScript?** HTML menyusun struktur, CSS mengatur tampilan, JavaScript menjalankan interaksi dan pengolahan data.

**Apa itu DOM?** Representasi elemen HTML yang dapat dipilih/diubah melalui JavaScript, misalnya `querySelector`, `textContent`, `innerHTML`, dan `setAttribute`.

**Mengapa memakai defer?** Script dijalankan setelah HTML selesai diparsing sehingga elemen yang dipilih sudah tersedia.

**Apa bedanya const dan let?** Const tidak bisa diassign ulang; let dipakai jika variabel akan diassign ulang. Object `state` memakai const, tetapi propertinya seperti `state.media` dan `state.activeAura` tetap dapat berubah.

**Apa itu event listener?** Fungsi yang dijalankan ketika kejadian tertentu terjadi. Input menjalankan render, click memilih Aura, submit memvalidasi form.

**Mengapa preventDefault pada form?** Mencegah reload/pengiriman bawaan browser supaya validasi demo ditangani JavaScript.

**Apa itu Fetch API dan JSON?** Fetch mengirim request HTTP. JSON adalah format pertukaran data; `response.json()` membacanya menjadi data JavaScript.

**Mengapa response.ok diperiksa?** Fetch tetap resolve ketika server mengembalikan HTTP error; pengecekan manual memungkinkan error masuk ke catch.

**Apa fungsi async/await dan finally?** Async/await merapikan proses Promise. Finally berjalan saat berhasil maupun gagal, sehingga loading selalu dihentikan.

**Apa bedanya filter, map, forEach?** Filter memilih item menjadi array baru; map mengubah setiap item menjadi nilai baru; forEach menjalankan aksi pada setiap item tanpa menghasilkan array hasil.

**Apa itu template literal?** String dengan backtick yang mendukung banyak baris dan penyisipan ekspresi `${...}`. Dipakai untuk HTML card dinamis.

**Mengapa pencarian cepat?** Pencarian dilakukan di memori pada maksimal 20 item yang sudah dimuat, bukan meminta server setiap ketikan. Keterbatasannya: judul di luar kumpulan itu tidak bisa ditemukan.

**Mengapa hanya memakai Kitsu?** Kitsu menyediakan data anime dari API publik, sehingga memenuhi ketentuan Fetch API pada modul. Request langsung ke satu API membuat alur kode lebih sederhana.

**Apa beda cache dan state?** State `state.media` menyimpan data selama halaman terbuka. Cache localStorage bertahan setelah reload pada origin browser yang sama. Keduanya berisi data yang pernah berhasil diperoleh dari API.

**Mengapa loading kadang tidak terlihat saat reload?** Cache segar ditampilkan langsung tanpa request baru. Klik Muat ulang data untuk mengambil data terbaru dan melihat loading.

**Apa bedanya error state dan empty state?** Error berarti katalog gagal dimuat tanpa data yang dapat dipakai. Empty state berarti proses data berhasil, tetapi tidak ada item yang cocok dengan search/Aura, atau API memang mengirim array kosong.

**Mengapa skor dan genre Kitsu perlu diubah?** Format tiap API berbeda. Normalisasi membuat fungsi render dan filter menerima struktur yang seragam, termasuk konversi skor dan pencocokan kategori berdasarkan ID.

**Apakah form benar-benar mengirim pesan?** Tidak. Ini codelab validasi frontend; pengiriman memerlukan backend yang tidak diminta Modul 2.

## Sumber API

Aplikasi hanya menggunakan API publik Kitsu. Loading, timeout, error, cache, pencarian, dan filter Aura tetap diterapkan.

Dokumentasi sumber: [Kitsu JSON:API](https://hummingbird-me.github.io/api-docs/).

## Hasil verifikasi

- `npm test`: 12 pengujian lulus, mencakup request hanya ke Kitsu, header dan parameter, timeout koneksi/body, error jaringan/HTTP/JSON, normalisasi kategori/rating, duplikat, dan respons kosong.
- Browser dengan data live: 20 card dari Kitsu beserta poster; link detail dan label sumber sesuai Kitsu.
- Subset pada data pengujian: High Energy 17, Melancholy 9, Binge Mode 16. Jumlah dapat berubah mengikuti API.
- Search tanpa membedakan kapital, gabungan search + Aura, empty state, dan reset lulus. Filter tidak menambah request API.
- Pada pengujian browser sebelumnya, kegagalan seluruh request API disimulasikan: data lama dipertahankan; tanpa cache tampil error yang menjelaskan kegagalan Kitsu. Tombol Coba lagi kemudian berhasil memuat 20 data dari API live.
- Reload dengan cache segar menampilkan 20 card dan melakukan nol request katalog API.
- Mobile lebar 390px: satu kolom, tanpa horizontal overflow.
- Cache ini menyimpan data katalog; HTML/CSS/JavaScript tetap harus dilayani Laragon. Ini bukan aplikasi offline penuh, dan gambar baru tetap membutuhkan jaringan.

## Penanda kode setelah refactor

Penanda berada dalam komentar dan bisa dicari dengan Ctrl+F. Detail alur dan contoh modifikasi ada di **ALUR-KODE-MODUL-2.md**. Pengaturan Aura ada di `[CFG-01]`, state di `[STATE-01]`, penghubung HTML di `[DOM-01]`, pengambilan data di `[API-06]`, alur loading di `[LOAD-02]`, filter di `[FILTER-01]`/`[FILTER-02]`, dan output card di `[RENDER-01]`/`[CARD-01]`.

Verifikasi refactor 18 Oktober 2026: 12 tes otomatis dan build Tailwind lulus. Uji browser dengan 20 anime live memeriksa pencarian, semua Aura dan deskripsinya, reset, empty state, refresh gagal yang mempertahankan data, error tanpa cache, retry dengan API live, serta navbar dan validasi form. Seluruh 45 penanda pada panduan alur ditemukan dalam source.
