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
        ])->map(fn ($u) => User::create($u + ['password' => 'password']));

        // 10 media dummy dari factory.
        $media = Media::factory(10)->create();

        // Isi koleksi tiap pengguna dengan 3 media acak (attach ke tabel pivot).
        $users->each(fn ($user) => $user->media()->attach($media->random(3)->pluck('id')));

        Member::factory(3)->create();
        Book::factory(10)->create();
    }
}
