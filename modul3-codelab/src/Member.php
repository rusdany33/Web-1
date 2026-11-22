<?php

declare(strict_types=1);

namespace Codelab\Models;

final class Member
{
    private string $name;

    private string $memberId;

    /** @var array<string, Book> */
    private array $borrowedBooks = [];

    public function __construct(string $name, string $memberId)
    {
        $this->name = $name;
        $this->memberId = $memberId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function borrowBook(Book $book): bool
    {
        if (! $book->borrow()) {
            return false;
        }

        $this->borrowedBooks[$book->getTitle()] = $book;

        return true;
    }

    public function returnBook(Book $book): bool
    {
        if (! isset($this->borrowedBooks[$book->getTitle()])) {
            return false;
        }

        $book->returnBook();
        unset($this->borrowedBooks[$book->getTitle()]);

        return true;
    }
}
