<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Form Request: validasi dijalankan OTOMATIS sebelum method Controller dieksekusi.
// Jika gagal, Laravel langsung membalas status 422 + daftar error (tanpa kode manual).
class StoreMediaRequest extends FormRequest
{
    // true = semua client boleh memakai request ini (belum ada autentikasi).
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // POST (buat baru): semua field wajib. PATCH/PUT (ubah): hanya validasi field yang dikirim.
        $presence = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            // Judul wajib, maksimal 255, dan tidak boleh duplikat di tabel media.
            // ->ignore() agar saat update, judul milik media itu sendiri tidak dianggap duplikat.
            'title' => [
                $presence, 'string', 'max:255',
                Rule::unique('media', 'title')->ignore($this->route('media')),
            ],
            'type' => [$presence, 'string', 'max:50'],
            'genre' => [$presence, 'string', 'max:50'],
            'release_year' => ['nullable', 'integer', 'min:1888', 'max:'.(date('Y') + 5)],
            'description' => ['nullable', 'string'],
        ];
    }

    // Pesan error kustom berbahasa Indonesia.
    public function messages(): array
    {
        return [
            'title.required' => 'Judul media wajib diisi',
            'title.unique' => 'Judul media sudah terdaftar',
            'type.required' => 'Jenis media wajib diisi',
            'genre.required' => 'Genre media wajib diisi',
            'release_year.integer' => 'Tahun rilis harus berupa angka',
        ];
    }
}
