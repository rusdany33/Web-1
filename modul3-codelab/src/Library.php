<?php

declare(strict_types=1);

namespace Codelab\Services;

use Codelab\Models\Book;

final class Library
{
    /** @var array<int, Book> */
    private array $books = [];

    public function addBook(Book $book): void
    {
        $this->books[] = $book;
    }

    /** @return array<int, Book> */
    public function getBooks(): array
    {
        return $this->books;
    }
}
