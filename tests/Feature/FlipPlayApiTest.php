<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlipPlayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_crud_and_search_flow_works(): void
    {
        $created = $this->postJson('/api/media', [
            'title' => 'Interstellar',
            'type' => 'film',
            'genre' => 'sci-fi',
            'release_year' => 2014,
            'description' => 'A space adventure.',
        ])->assertCreated()->assertJsonPath('data.title', 'Interstellar');

        $mediaId = $created->json('data.id');
        $this->getJson('/api/media?search=Interstellar')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/media/{$mediaId}")->assertOk()->assertJsonPath('data.id', $mediaId);
        $this->patchJson("/api/media/{$mediaId}", ['title' => 'Interstellar Updated'])
            ->assertOk()->assertJsonPath('data.title', 'Interstellar Updated');
        $this->deleteJson("/api/media/{$mediaId}")->assertOk();
        $this->getJson("/api/media/{$mediaId}")->assertNotFound();
    }

    public function test_media_validation_and_duplicate_errors_are_returned(): void
    {
        $this->postJson('/api/media', [])->assertStatus(422)->assertJsonValidationErrors(['title', 'type', 'genre']);
        Media::factory()->create(['title' => 'Duplicated Title']);
        $this->postJson('/api/media', [
            'title' => 'Duplicated Title', 'type' => 'film', 'genre' => 'drama',
        ])->assertStatus(422)->assertJsonValidationErrors(['title']);
    }

    public function test_collection_custom_actions_cover_success_conflict_and_not_found(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create();

        $this->postJson("/api/users/{$user->id}/collection", ['media_id' => $media->id])
            ->assertCreated();
        $this->getJson("/api/users/{$user->id}")->assertOk()->assertJsonPath('data.media.0.id', $media->id);
        $this->postJson("/api/users/{$user->id}/collection", ['media_id' => $media->id])
            ->assertStatus(409);
        $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}")->assertOk();
        $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}")->assertNotFound();
    }
}
