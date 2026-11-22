# FlipPlay Backend Engine - Modul 3

Jalankan simulasi dari terminal pada folder ini:

```powershell
php index.php
```

Struktur project:

```text
modul3-flipplay/
├── index.php              # Entry point simulasi terminal
└── src/
    ├── Models/
    │   ├── Media.php       # Parent abstract class
    │   ├── Anime.php       # Child class Media
    │   ├── Drama.php       # Child class Media
    │   └── User.php        # Koleksi pengguna
    └── Services/
        └── MediaLibrary.php # Menyimpan, mencari, dan menampilkan media
```

Setiap penjelasan penting diberi kode pencarian seperti `[M3-INDEX-02]` atau `[M3-INDEX-05]`. Cari kode tersebut dengan Ctrl+F di editor.

Baca `CATATAN-MODUL-3.md` untuk alur output, pemetaan rubrik, dan jawaban saat asistensi.
