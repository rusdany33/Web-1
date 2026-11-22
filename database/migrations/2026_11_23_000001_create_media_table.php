<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // up() = dijalankan saat `php artisan migrate`: membuat struktur tabel.
    public function up(): void
    {
        // Nama tabel 'media' (bentuk jamak dari "medium" di Laravel juga 'media').
        Schema::create('media', function (Blueprint $table) {
            $table->id();                                   // primary key auto-increment
            $table->string('title')->unique();              // judul WAJIB unik (syarat demo: tidak boleh duplikat)
            $table->string('type');                         // jenis: film, series, game, musik, dll.
            $table->string('genre');                        // genre: aksi, drama, dll.
            $table->unsignedSmallInteger('release_year')->nullable(); // tahun rilis, boleh kosong
            $table->text('description')->nullable();        // deskripsi, boleh kosong
            $table->timestamps();                           // created_at & updated_at
        });
    }

    // down() = dijalankan saat rollback: kebalikan dari up().
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
