<?php

declare(strict_types=1);

namespace Codelab\Models;

// [M3-CODELAB-01] Book menyimpan data buku dan status peminjaman secara private.
final class Book
{
    private string $title;
    private string $author;
    private bool $available = true;

    public function __construct(string $title, string $author)
    {
        $this->title = $title;
        $this->author = $author;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    // [M3-CODELAB-02] Method mengubah status melalui class, bukan property dari luar.
    public function borrow(): bool
    {
        if (!$this->available) {
            return false;
        }

        $this->available = false;
        return true;
    }

    public function returnBook(): void
    {
        $this->available = true;
    }
}
