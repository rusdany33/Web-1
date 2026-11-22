<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCollectionRequest;
use App\Http\Resources\MediaResource;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // GET /api/users -> daftar pengguna beserta jumlah media di koleksinya.
    public function index()
    {
        return UserResource::collection(User::withCount('media')->paginate(10));
    }

    // GET /api/users/{user} -> detail pengguna + seluruh koleksi medianya (eager loading).
    public function show(User $user)
    {
        return new UserResource($user->load('media'));
    }

    // GET /api/users/{user}/collection -> koleksi media milik seorang pengguna.
    public function collection(User $user)
    {
        return MediaResource::collection($user->media()->paginate(10));
    }

    // ===== CUSTOM ACTION 1: tambah media ke koleksi (attach) =====
    // POST /api/users/{user}/collection   body: { "media_id": 1 }
    public function addToCollection(AddToCollectionRequest $request, User $user): JsonResponse
    {
        $mediaId = $request->validated('media_id');

        // Transaction + lockForUpdate pada baris user: dua request bersamaan untuk
        // pengguna yang sama harus antre, sehingga cek duplikat di bawah tidak bisa "kebobolan"
        // (pola baca -> cek -> ubah, lihat Bab 4.4 tentang race condition).
        return DB::transaction(function () use ($user, $mediaId) {
            User::whereKey($user->id)->lockForUpdate()->first();

            // Cek duplikat: media yang sama tidak boleh masuk dua kali ke koleksi yang sama.
            if ($user->media()->where('media_id', $mediaId)->exists()) {
                // 409 Conflict = request valid, tapi bertentangan dengan kondisi data.
                return response()->json(['message' => 'Media sudah ada di koleksi'], 409);
            }

            $user->media()->attach($mediaId); // tambah baris baru ke tabel pivot

            return response()->json([
                'message' => 'Media berhasil ditambahkan ke koleksi',
                'data' => new UserResource($user->load('media')),
            ], 201);
        });
    }

    // ===== CUSTOM ACTION 2: hapus media dari koleksi (detach) =====
    // DELETE /api/users/{user}/collection/{media}
    public function removeFromCollection(User $user, Media $media): JsonResponse
    {
        // Pastikan media memang ada di koleksi pengguna ini.
        if (! $user->media()->where('media_id', $media->id)->exists()) {
            return response()->json(['message' => 'Media tidak ada di koleksi pengguna ini'], 404);
        }

        $user->media()->detach($media->id); // hapus baris dari tabel pivot (data Media tetap ada)

        return response()->json(['message' => 'Media berhasil dihapus dari koleksi'], 200);
    }
}
