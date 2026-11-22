<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Media;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 3 pengguna FlipPlay (password di-hash otomatis oleh cast 'hashed' di Model).
        $users = collect([
            ['name' => 'Budi Santoso', 'email' => 'budi@example.com'],
            ['name' => 'Siti Aminah',  'email' => 'siti@example.com'],
            ['name' => 'Andi Wijaya',  'email' => 'andi@example.com'],
        ])->map(fn ($u) => User::firstOrCreate(['email' => $u['email']], $u + ['password' => 'password']));

        $media = collect([
            ['title' => 'Agak Laen', 'type' => 'film', 'genre' => 'komedi', 'release_year' => 2024, 'description' => 'Empat sahabat mengelola rumah hantu dengan cara yang tidak biasa.'],
            ['title' => 'Ngeri-Ngeri Sedap', 'type' => 'film', 'genre' => 'drama keluarga', 'release_year' => 2022, 'description' => 'Kisah keluarga Batak yang belajar memahami arti pulang dan kebersamaan.'],
            ['title' => 'Gadis Kretek', 'type' => 'series', 'genre' => 'drama sejarah', 'release_year' => 2023, 'description' => 'Perjalanan cinta, keluarga, dan industri kretek di Indonesia.'],
            ['title' => 'The Last of Us', 'type' => 'series', 'genre' => 'drama post-apocalyptic', 'release_year' => 2023, 'description' => 'Dua penyintas melintasi Amerika Serikat setelah wabah menghancurkan dunia.'],
            ['title' => 'Spirited Away', 'type' => 'film', 'genre' => 'animasi fantasi', 'release_year' => 2001, 'description' => 'Petualangan Chihiro di dunia roh yang penuh keajaiban.'],
            ['title' => 'Interstellar', 'type' => 'film', 'genre' => 'sci-fi drama', 'release_year' => 2014, 'description' => 'Misi melintasi ruang dan waktu demi masa depan umat manusia.'],
            ['title' => 'One Piece', 'type' => 'series', 'genre' => 'petualangan', 'release_year' => 1999, 'description' => 'Luffy dan kru Topi Jerami berlayar mengejar harta karun legendaris.'],
            ['title' => 'The Batman', 'type' => 'film', 'genre' => 'aksi kriminal', 'release_year' => 2022, 'description' => 'Batman menyelidiki korupsi dan kejahatan yang mengakar di Gotham.'],
            ['title' => 'Dilan 1990', 'type' => 'film', 'genre' => 'romansa remaja', 'release_year' => 2018, 'description' => 'Kisah asmara Milea dan Dilan yang tumbuh di Bandung tahun 1990.'],
            ['title' => 'Minecraft', 'type' => 'game', 'genre' => 'sandbox petualangan', 'release_year' => 2011, 'description' => 'Bangun, jelajah, dan ciptakan dunia tanpa batas.'],
            ['title' => 'The Witcher 3', 'type' => 'game', 'genre' => 'RPG fantasi', 'release_year' => 2015, 'description' => 'Geralt of Rivia memburu monster di dunia fantasi yang luas.'],
            ['title' => 'Laskar Pelangi', 'type' => 'film', 'genre' => 'drama inspiratif', 'release_year' => 2008, 'description' => 'Semangat belajar anak-anak Belitung menghadapi keterbatasan.'],
        ])->map(fn (array $item) => Media::firstOrCreate(['title' => $item['title']], $item));

        // Isi koleksi tiap pengguna dengan 3 media acak (attach ke tabel pivot).
        $users->each(fn ($user) => $user->media()->syncWithoutDetaching($media->random(3)->pluck('id')));

        Member::factory(3)->create();
        Book::factory(10)->create();
    }
}
