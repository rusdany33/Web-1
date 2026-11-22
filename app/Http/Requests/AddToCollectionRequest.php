<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validasi untuk custom action "tambah media ke koleksi pengguna".
class AddToCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // exists:media,id = media_id harus benar-benar ada di tabel media.
            'media_id' => ['required', 'integer', 'exists:media,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'media_id.required' => 'media_id wajib diisi',
            'media_id.exists' => 'Media dengan ID tersebut tidak ditemukan',
        ];
    }
}
