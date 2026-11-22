<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'title' => $this->title, 'author' => $this->author, 'is_available' => $this->is_available, 'member_id' => $this->member_id, 'member' => new MemberResource($this->whenLoaded('member'))];
    }
}
