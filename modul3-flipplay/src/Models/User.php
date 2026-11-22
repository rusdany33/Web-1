<?php

declare(strict_types=1);

namespace FlipPlay\Models;

// [M3-USER-01] User menyimpan koleksinya sendiri dengan enkapsulasi private.
final class User
{
    private string $id;
    private string $name;

    /** @var array<string, Media> */
    private array $collection = [];

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    // [M3-USER-02] Return bool memberi tahu apakah media benar-benar baru ditambahkan.
    public function addToCollection(Media $media): bool
    {
        if (isset($this->collection[$media->getId()])) {
            return false;
        }

        $this->collection[$media->getId()] = $media;
        return true;
    }

    /** @return array<string, Media> */
    public function getCollection(): array
    {
        return $this->collection;
    }
}
