<?php

declare(strict_types=1);

// [M3-MEDIA-01] Namespace mencegah nama class bentrok dengan project lain.

namespace FlipPlay\Models;

// [M3-MEDIA-02] Abstract class adalah cetak biru media umum; object Media tidak dibuat langsung.
abstract class Media
{
    // [M3-MEDIA-03] Protected: class turunan boleh memakai data ini, kode luar tidak boleh mengubah langsung.
    protected string $id;

    protected string $title;

    protected string $genre;

    // [M3-MEDIA-04] Constructor dipanggil otomatis saat object Anime atau Drama dibuat.
    public function __construct(string $id, string $title, string $genre)
    {
        $this->id = $id;
        $this->title = $title;
        $this->genre = $genre;
    }

    // [M3-MEDIA-05] Getter public memberi akses baca yang aman terhadap property protected.
    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getGenre(): string
    {
        return $this->genre;
    }

    // [M3-MEDIA-06] Method abstract wajib dibuat ulang oleh setiap child class.
    abstract public function getType(): string;

    abstract public function getDetail(): string;
}
