# Catatan Asistensi - Modul 3 PHP OOP

Gunakan dokumen ini saat menjelaskan program. Semua kode diberi penanda seperti `[M3-INDEX-02]`; tekan **Ctrl+F** di editor untuk langsung menuju bagian yang dibahas.

## Cara menjalankan

Di terminal, masuk ke folder `modul3-flipplay`, lalu jalankan:

```powershell
php index.php
```

`index.php` adalah entry point, sesuai ketentuan modul. File ini mendaftarkan autoloader, membuat object, lalu menjalankan seluruh simulasi.

## Alur simulasi terminal

```text
[M3-INDEX-01] index.php dimulai
        ↓
[M3-INDEX-02] autoloader mencari class di folder src/
        ↓
[M3-INDEX-05] membuat MediaLibrary, Anime, dan Drama
        ↓
[M3-LIB-02] addMedia() menyimpan setiap object ke library
        ↓
[M3-LIB-01] getAllMedia(), searchByTitle(), findById()
        ↓
[M3-USER-02] addToCollection() menambahkan media ke koleksi User
        ↓
[M3-INDEX-04] foreach memanggil getDetail() pada setiap object
        ↓
Output tampil di terminal
```

## Peta class, property, dan method

| Class | File | Property | Method penting | Fungsi |
| --- | --- | --- | --- | --- |
| `Media` | `src/Models/Media.php` | `protected $id`, `$title`, `$genre` | getter, `getType()`, `getDetail()` | Parent abstract untuk media umum |
| `Anime` | `src/Models/Anime.php` | `private $episodes` | `getEpisodes()`, `getDetail()` | Child class untuk anime |
| `Drama` | `src/Models/Drama.php` | `private $seasons` | `getSeasons()`, `getDetail()` | Child class untuk drama |
| `MediaLibrary` | `src/Services/MediaLibrary.php` | `private $mediaById` | `addMedia()`, `searchByTitle()`, `findById()` | Menyimpan dan mencari media |
| `User` | `src/Models/User.php` | `private $id`, `$name`, `$collection` | `addToCollection()`, `getCollection()` | Menyimpan koleksi media pengguna |

Program memakai lima class, sehingga melebihi syarat minimal tiga class.

## Penjelasan kode per bagian

### 1. Namespace dan strict types

Cari `[M3-MEDIA-01]` atau file class lain:

```php
declare(strict_types=1);
namespace FlipPlay\Models;
```

- `declare(strict_types=1)` membuat PHP lebih ketat terhadap tipe data.
- `namespace FlipPlay\Models` mengelompokkan class model. `MediaLibrary` berada pada namespace `FlipPlay\Services` karena tugasnya adalah layanan library.
- Di `index.php`, keyword `use` mengimpor nama class agar tidak perlu selalu menulis namespace lengkap.

Jawaban asistensi:

> Namespace berfungsi seperti folder untuk nama class. Dengan namespace, class dengan nama sama dari bagian project berbeda tidak saling bentrok.

### 2. Class, object, property, dan constructor

Cari `[M3-ANIME-01]` dan `[M3-ANIME-02]`:

```php
final class Anime extends Media
{
    private int $episodes;
}
```

- `class Anime` adalah cetak biru.
- `$episodes` adalah property: data yang melekat pada Anime.
- `private int` artinya property hanya dapat diakses dari class Anime dan nilainya harus integer.
- `new Anime('A-001', 'Frieren: Beyond Journey’s End', 'Fantasy', 28)` pada `[M3-INDEX-05]` membuat object nyata dari class tersebut.

Cari `[M3-ANIME-03]`:

```php
public function __construct(string $id, string $title, string $genre, int $episodes)
{
    parent::__construct($id, $title, $genre);
    $this->episodes = $episodes;
}
```

- `__construct()` dipanggil otomatis ketika `new Anime(...)` dijalankan.
- Parameter memiliki type hint: tiga `string` dan satu `int`.
- `parent::__construct(...)` mengisi property umum yang dimiliki `Media`.
- `$this->episodes` berarti property milik object Anime yang sedang aktif.

### 3. Access modifier dan encapsulation
| Modifier | Contoh di project | Alasan |
| --- | --- | --- |
| `public` | `addMedia()`, `getTitle()` | Harus dapat dipanggil dari `index.php` atau class lain |
| `protected` | `$id`, `$title`, `$genre` di `Media` | Boleh dipakai oleh `Anime` dan `Drama`, tetapi tidak dari luar object |
| `private` | `$episodes`, `$seasons`, `$collection` | Detail internal hanya dikelola class pemiliknya |

Contoh encapsulation pada `[M3-USER-02]`:

```php
public function addToCollection(Media $media): bool
{
    if (isset($this->collection[$media->getId()])) {
        return false;
    }

    $this->collection[$media->getId()] = $media;
    return true;
}
```

Kode luar tidak mengubah `$collection` secara langsung. Kode wajib menggunakan `addToCollection()`, dan method itu mencegah media yang sama masuk dua kali.

Jawaban asistensi:

> Encapsulation menjaga data internal object. Collection dibuat private agar penambahan media melewati method yang dapat memvalidasi duplikasi.

### 4. Inheritance dan polymorphism

Cari `[M3-MEDIA-02]`, `[M3-ANIME-01]`, dan `[M3-DRAMA-01]`.

```php
abstract class Media { ... }
final class Anime extends Media { ... }
final class Drama extends Media { ... }
```

- `Media` adalah parent class dan abstract, karena hanya menjadi konsep umum; object `new Media()` tidak boleh dibuat.
- `Anime extends Media` dan `Drama extends Media` adalah inheritance. Keduanya memperoleh `id`, `title`, `genre`, serta getter dari parent.
- `Anime` menambahkan `episodes`, sementara `Drama` menambahkan `seasons`.

`Media` mewajibkan child class membuat `getDetail()` melalui method abstract. Karena itu `Anime::getDetail()` menampilkan episode, sedangkan `Drama::getDetail()` menampilkan season. Saat `printMediaList()` menerima keduanya sebagai `Media`, baris yang sama:

```php
echo '- ' . $media->getDetail() . PHP_EOL;
```

menghasilkan output berbeda sesuai object. Ini adalah polymorphism.

### 5. Type hint dan return type

Cari `[M3-LIB-02]`:

```php
public function addMedia(Media $media): void
```

- `Media $media` adalah type hint. Method hanya menerima object `Media` atau child class seperti `Anime`/`Drama`.
- `: void` adalah return type: method tidak mengembalikan nilai.

Contoh lain:

```php
public function findById(string $id): ?Media
```

- Parameter wajib string.
- `?Media` berarti hasilnya object `Media` atau `null` apabila ID tidak ada.

### 6. Cara library bekerja

Cari `[M3-LIB-02]`:

```php
$this->mediaById[$media->getId()] = $media;
```

Array memakai ID sebagai key. Ini membuat pencarian `findById()` sederhana:

```php
return $this->mediaById[$id] ?? null;
```

`?? null` berarti jika ID belum ada, kembalikan `null` tanpa error.

Pencarian judul ada pada `searchByTitle()`:

```php
return array_filter(
    $this->mediaById,
    static fn (Media $media): bool => str_contains(strtolower($media->getTitle()), $keyword)
);
```

- `trim()` membersihkan spasi ujung.
- `strtolower()` membuat pencarian tidak membedakan huruf besar/kecil.
- `array_filter()` menyimpan hanya media yang judulnya mengandung keyword.
- Arrow function menerima satu object `Media` dan mengembalikan `true` atau `false`.

### 7. Autoload dan entry point

Cari `[M3-INDEX-02]`. Fungsi `spl_autoload_register()` berjalan ketika PHP membutuhkan class yang belum dimuat.

Contoh: saat index menjalankan `new Anime(...)`, PHP meminta class `FlipPlay\Models\Anime`. Autoloader mengubah nama itu menjadi file `src/Models/Anime.php`, lalu menjalankan `require` pada file tersebut. Karena itu `index.php` tidak perlu menulis banyak `require` manual.

### 8. Penjelasan output

| Bagian output | Kode yang menghasilkan | Arti |
| --- | --- | --- |
| `Berhasil menambahkan 4 media` | `[M3-INDEX-05]` + `MediaLibrary::count()` | Empat object masuk ke library |
| Daftar Anime dan Drama | `printMediaList()` + `getAllMedia()` | `foreach` menampilkan seluruh object |
| Hasil pencarian Frieren | `searchByTitle('frieren')` | Filter judul bekerja tanpa membedakan kapital |
| Detail A-001 | `findById('A-001')` | Lookup memakai ID sebagai key array |
| Koleksi Alya | `User::addToCollection()` + `getCollection()` | User menyimpan dua object Media |

## Pemetaan rubrik nilai

| Rubrik | Bukti di project | Cara membuktikan saat demo |
| --- | --- | --- |
| Codelab: kode lengkap | `modul3-codelab/src/Book.php`, `Member.php`, `Library.php` | Tunjukkan method `borrow()`, `returnBook()`, `borrowBook()`, `returnBook()` |
| Codelab: output | `modul3-codelab/index.php` | Jalankan `php index.php`; status buku berubah Ya → Tidak → Ya |
| Simulasi FlipPlay | `modul3-flipplay/index.php` | Jalankan lima langkah: tambah, daftar, cari, detail, koleksi user |
| PHP OOP | Semua class di `src/` | Tunjukkan class, object, property, method, constructor, inheritance, encapsulation, polymorphism |
| Ketentuan modul | namespace, type hint, return type, `index.php` | Jelaskan tabel class dan contoh signature method |
| Program berjalan | `php index.php` | Jalankan langsung dari terminal |
| Pemahaman | Bagian tanya jawab berikut | Jawab dengan menunjuk penanda kode |

## Tanya jawab yang mungkin keluar

**Apa perbedaan class dan object?** Class adalah cetak biru, object adalah instance nyata dari class. `Anime` adalah class; `$library->addMedia(new Anime(...))` membuat object Anime.

**Mengapa Media dibuat abstract?** Media menyimpan perilaku dan data bersama, tetapi jenis media umum tidak cukup spesifik untuk dibuat sebagai object. Anime dan Drama wajib menjelaskan tipe serta detailnya masing-masing.

**Mengapa memakai inheritance?** Anime dan Drama sama-sama memiliki ID, judul, dan genre. Inheritance menghindari penulisan ulang property dan getter yang sama.

**Apa contoh polymorphism di program ini?** `printMediaList()` memanggil `$media->getDetail()` untuk object Anime dan Drama. Method sama, tetapi output berbeda karena masing-masing class mengimplementasikannya sendiri.

**Mengapa collection private?** Agar kode luar tidak bisa menambah data sembarangan. User mengontrol penambahan lewat `addToCollection()` dan dapat menolak duplikasi.

**Apa fungsi `?Media`?** Itu nullable return type. Method dapat mengembalikan object Media, atau `null` jika media tidak ditemukan.

**Apa fungsi namespace?** Mengorganisasi class dan mencegah bentrok nama. Models dan Services dipisahkan sesuai perannya.

**Apa fungsi autoloader?** Memuat file class secara otomatis berdasarkan namespace ketika class diperlukan.

**Mengapa `Media $media` dapat menerima Anime dan Drama?** Karena Anime dan Drama adalah child class dari Media; keduanya memenuhi tipe Media.

## Urutan demo yang disarankan

1. Buka `modul3-codelab`, jalankan `php index.php`, dan jelaskan status buku sebelum pinjam, sesudah pinjam, dan sesudah kembali.
2. Buka `modul3-flipplay`, tunjukkan struktur folder serta lima class di `src/`.
3. Tunjukkan `Media`, `Anime`, dan `Drama` untuk menjelaskan inheritance dan polymorphism.
4. Jalankan `php index.php`.
5. Ikuti output lima tahap, lalu tunjukkan fungsi sumbernya menggunakan penanda `[M3-INDEX-05]`, `[M3-LIB-02]`, `[M3-LIB-01]`, dan `[M3-USER-02]`.
6. Jika ditanya teknis, cari penanda di atas dengan Ctrl+F dan jawab berdasarkan bagian Tanya jawab.
