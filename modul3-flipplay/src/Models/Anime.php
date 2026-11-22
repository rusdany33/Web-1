<?php

declare(strict_types=1);

namespace FlipPlay\Models;

// [M3-ANIME-01] Anime mewarisi id, title, genre, serta getter dari Media.
final class Anime extends Media
{
    // [M3-ANIME-02] Private: episode hanya boleh diubah dari dalam class Anime.
    private int $episodes;

    public function __construct(string $id, string $title, string $genre, int $episodes)
    {
        parent::__construct($id, $title, $genre); // [M3-ANIME-03] Panggil constructor parent.
        $this->episodes = $episodes;
    }

    public function getType(): string
    {
        return 'Anime';
    }

    public function getEpisodes(): int
    {
        return $this->episodes;
    }

    // [M3-ANIME-04] Implementasi khusus Anime untuk method abstract dari Media.
    public function getDetail(): string
    {
        return "[Anime] {$this->title} | Genre: {$this->genre} | Episode: {$this->episodes}";
    }
}
