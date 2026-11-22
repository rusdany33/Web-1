<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use HasFactory;

    // Ditulis eksplisit agar Eloquent tidak salah menebak nama tabel.
    protected $table = 'media';

    // Kolom yang boleh diisi lewat mass assignment (Media::create($request->validated())).
    protected $fillable = ['title', 'type', 'genre', 'release_year', 'description'];

    // Casting: release_year otomatis jadi integer saat dibaca dari database.
    protected $casts = [
        'release_year' => 'integer',
    ];

    // RELASI many-to-many: satu Media dikoleksi banyak User.
    // withTimestamps() mengisi created_at/updated_at pada tabel pivot.
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    // QUERY SCOPE: dipanggil Media::search('kata'). Filter judul memakai LIKE.
    // Kalau $keyword kosong, query dikembalikan apa adanya (tidak difilter).
    public function scopeSearch($query, ?string $keyword)
    {
        return $query->when($keyword, fn ($q) => $q->where('title', 'like', "%{$keyword}%"));
    }
}
