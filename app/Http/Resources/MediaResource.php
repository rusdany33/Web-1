<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// API Resource: satu tempat untuk mengatur format JSON Media agar selalu konsisten.
class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'genre' => $this->genre,
            'release_year' => $this->release_year,
            'description' => $this->description,
            // whenLoaded: field 'users' HANYA muncul jika relasinya sudah di-load (eager loading),
            // sehingga tidak ada query tambahan yang tidak perlu (N+1).
            'users' => UserResource::collection($this->whenLoaded('users')),
            // whenCounted: muncul hanya jika memakai withCount('users').
            'users_count' => $this->whenCounted('users'),
            'created_at' => $this->created_at,
        ];
    }
}
