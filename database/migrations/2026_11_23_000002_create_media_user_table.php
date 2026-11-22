<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabel PIVOT untuk relasi many-to-many: koleksi media milik pengguna.
    // Nama mengikuti konvensi Laravel: singular, urut alfabet -> media_user (bukan user_media).
    public function up(): void
    {
        Schema::create('media_user', function (Blueprint $table) {
            $table->id();
            // constrained('media') = foreign key ke tabel media; nama tabel ditulis eksplisit.
            // cascadeOnDelete = jika media/user dihapus, baris koleksinya ikut terhapus (tidak ada data yatim).
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps(); // kapan media ditambahkan ke koleksi

            // Pasangan (media, user) harus unik: pengaman level database
            // agar media yang sama tidak bisa masuk dua kali ke koleksi yang sama.
            $table->unique(['media_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_user');
    }
};
