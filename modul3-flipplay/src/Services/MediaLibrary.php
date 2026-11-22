<?php

declare(strict_types=1);

namespace FlipPlay\Services;

use FlipPlay\Models\Media;

// [M3-LIB-01] Class service mengelola kumpulan Media, bukan detail tiap jenis media.
final class MediaLibrary
{
    /** @var array<string, Media> */
    private array $mediaById = [];

    // [M3-LIB-02] Parameter Media menerima Anime maupun Drama karena keduanya mewarisi Media.
    public function addMedia(Media $media): void
    {
        $this->mediaById[$media->getId()] = $media;
    }

    /** @return array<string, Media> */
    public function getAllMedia(): array
    {
        return $this->mediaById;
    }

    public function findById(string $id): ?Media
    {
        return $this->mediaById[$id] ?? null;
    }

    /** @return array<string, Media> */
    public function searchByTitle(string $keyword): array
    {
        $keyword = strtolower(trim($keyword));

        return array_filter(
            $this->mediaById,
            static fn (Media $media): bool => str_contains(strtolower($media->getTitle()), $keyword)
        );
    }

    public function count(): int
    {
        return count($this->mediaById);
    }
}
