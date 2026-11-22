<?php

declare(strict_types=1);

namespace FlipPlay\Models;

// [M3-DRAMA-01] Drama juga child class Media, tetapi punya property seasons sendiri.
final class Drama extends Media
{
    private int $seasons;

    public function __construct(string $id, string $title, string $genre, int $seasons)
    {
        parent::__construct($id, $title, $genre);
        $this->seasons = $seasons;
    }

    public function getType(): string
    {
        return 'Drama';
    }

    public function getSeasons(): int
    {
        return $this->seasons;
    }

    // [M3-DRAMA-02] Nama method sama seperti Anime, outputnya berbeda: polymorphism.
    public function getDetail(): string
    {
        return "[Drama] {$this->title} | Genre: {$this->genre} | Season: {$this->seasons}";
    }
}
