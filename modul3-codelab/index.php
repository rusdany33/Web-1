<?php

declare(strict_types=1);

// [M3-CODELAB-03] Entry point codelab: menunjukkan tambah, pinjam, dan kembalikan buku.
spl_autoload_register(static function (string $className): void {
    $prefix = 'Codelab\\';
    if (str_starts_with($className, $prefix)) {
        // [M3-CODELAB-04] File codelab berada langsung dalam src, walau namespace memakai Models/Services.
        $relativeClass = str_replace(['Models\\', 'Services\\'], '', substr($className, strlen($prefix)));
        require __DIR__.'/src/'.$relativeClass.'.php';
    }
});

use Codelab\Models\Book;
use Codelab\Models\Member;
use Codelab\Services\Library;

$library = new Library;
$book = new Book('Laut Bercerita', 'Leila S. Chudori');
$member = new Member('Alya', 'M-001');
$library->addBook($book);

echo "=== Codelab Perpustakaan ===\n";
echo 'Buku tersedia: '.($book->isAvailable() ? 'Ya' : 'Tidak').PHP_EOL;
echo $member->borrowBook($book) ? "Alya berhasil meminjam buku.\n" : "Peminjaman gagal.\n";
echo 'Buku tersedia setelah dipinjam: '.($book->isAvailable() ? 'Ya' : 'Tidak').PHP_EOL;
echo $member->returnBook($book) ? "Alya berhasil mengembalikan buku.\n" : "Pengembalian gagal.\n";
echo 'Buku tersedia setelah dikembalikan: '.($book->isAvailable() ? 'Ya' : 'Tidak').PHP_EOL;
