<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// CRUD standar untuk entitas Media (index, show, store, update, destroy).
class MediaController extends Controller
{
    // GET /api/media  -> daftar media + pencarian judul + pagination.
    // Contoh: GET /api/media?search=naruto
    public function index(Request $request)
    {
        $media = Media::query()
            ->search($request->query('search'))  // query scope di Model
            ->withCount('users')                 // hitung berapa pengguna yang mengoleksi
            ->latest()
            ->paginate(10)                       // 10 data per halaman
            ->withQueryString();                 // parameter ?search= ikut di link halaman berikutnya

        return MediaResource::collection($media); // status 200
    }

    // GET /api/media/{media} -> detail media + pengguna yang mengoleksinya.
    // Route Model Binding: Laravel otomatis mencari Media berdasarkan ID, 404 jika tidak ada.
    public function show(Media $media)
    {
        // load('users') = eager loading relasi agar muncul lewat whenLoaded di Resource.
        return new MediaResource($media->load('users'));
    }

    // POST /api/media -> tambah media baru. Validasi otomatis oleh StoreMediaRequest (422 jika gagal).
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = Media::create($request->validated());

        // 201 Created = data baru berhasil dibuat (beda dengan 200 OK).
        return (new MediaResource($media))->response()->setStatusCode(201);
    }

    // PATCH /api/media/{media} -> ubah sebagian data.
    public function update(StoreMediaRequest $request, Media $media)
    {
        $media->update($request->validated());

        return new MediaResource($media); // 200 OK
    }

    // DELETE /api/media/{media} -> hapus media (baris koleksinya di pivot ikut terhapus oleh cascadeOnDelete).
    public function destroy(Media $media): JsonResponse
    {
        $media->delete();

        return response()->json(['message' => 'Media berhasil dihapus'], 200);
    }
}
