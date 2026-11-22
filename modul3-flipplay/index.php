<?php

declare(strict_types=1);

// [M3-INDEX-01] Entry point CLI: jalankan dengan php index.php dari folder modul3-flipplay.

// [M3-INDEX-02] Autoloader sederhana: namespace FlipPlay\X\Y menjadi file src/X/Y.php.
spl_autoload_register(static function (string $className): void {
    $prefix = 'FlipPlay\\';
    if (!str_starts_with($className, $prefix)) {
        return;
    }

    $relativeClass = substr($className, strlen($prefix));
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use FlipPlay\Models\Anime;
use FlipPlay\Models\Drama;
use FlipPlay\Models\Media;
use FlipPlay\Models\User;
use FlipPlay\Services\MediaLibrary;

// [M3-INDEX-03] Helper output agar simulasi terminal mudah dibaca.
function heading(string $text): void
{
    echo "\n=== {$text} ===\n";
}

/** @param array<string, Media> $mediaList */
function printMediaList(array $mediaList): void
{
    if ($mediaList === []) {
        echo "Tidak ada media.\n";
        return;
    }

    foreach ($mediaList as $media) {
        // [M3-INDEX-04] Polymorphism: Anime dan Drama menjalankan getDetail() versinya sendiri.
        echo '- ' . $media->getDetail() . PHP_EOL;
    }
}

heading('FlipPlay Backend Engine - Modul 3 PHP OOP');

// [M3-INDEX-05] Membuat object class Media child menggunakan constructor.
$library = new MediaLibrary();
$library->addMedia(new Anime('A-001', 'Frieren: Beyond Journey’s End', 'Fantasy', 28));
$library->addMedia(new Anime('A-002', 'Haikyu!!', 'Sports', 85));
$library->addMedia(new Drama('D-001', 'Hospital Playlist', 'Slice of Life', 2));
$library->addMedia(new Drama('D-002', 'Moving', 'Action', 1));

heading('1. Menambahkan Media ke Library');
echo "Berhasil menambahkan {$library->count()} media.\n";

heading('2. Daftar Media Tersedia');
printMediaList($library->getAllMedia());

heading('3. Mencari Media Berdasarkan Judul: "frieren"');
$searchResult = $library->searchByTitle('frieren');
printMediaList($searchResult);

heading('4. Detail Media dengan ID A-001');
$selectedMedia = $library->findById('A-001');
echo $selectedMedia?->getDetail() ?? "Media tidak ditemukan.";
echo PHP_EOL;

heading('5. Koleksi Media Pengguna');
$user = new User('U-001', 'Alya');
if ($selectedMedia instanceof Media && $user->addToCollection($selectedMedia)) {
    echo "{$selectedMedia->getTitle()} ditambahkan ke koleksi {$user->getName()}.\n";
}

$hospitalPlaylist = $library->findById('D-001');
if ($hospitalPlaylist instanceof Media && $user->addToCollection($hospitalPlaylist)) {
    echo "{$hospitalPlaylist->getTitle()} ditambahkan ke koleksi {$user->getName()}.\n";
}

echo "\nKoleksi {$user->getName()}:\n";
printMediaList($user->getCollection());

heading('Simulasi Selesai');
