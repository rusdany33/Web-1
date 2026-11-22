<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Model User bawaan Laravel dipakai sebagai "Pengguna" FlipPlay.
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    // Kolom yang disembunyikan dari output JSON (keamanan).
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // password otomatis di-hash saat disimpan
        ];
    }

    // RELASI many-to-many: satu User punya banyak Media di koleksinya.
    // Nama method `media` (bukan `medias`) karena Media sudah bentuk jamak.
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class)->withTimestamps();
    }
}
