<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_borrow_and_return_a_book(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->create();

        $this->postJson("/api/members/{$member->id}/borrow-book", ['book_id' => $book->id])
            ->assertCreated()
            ->assertJsonPath('data.is_available', false);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'member_id' => $member->id, 'is_available' => 0]);

        $this->patchJson("/api/members/{$member->id}/return-book/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.is_available', true);
    }

    public function test_borrowing_an_unavailable_book_returns_conflict(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->create(['is_available' => false]);

        $this->postJson("/api/members/{$member->id}/borrow-book", ['book_id' => $book->id])
            ->assertStatus(409);
    }
}
