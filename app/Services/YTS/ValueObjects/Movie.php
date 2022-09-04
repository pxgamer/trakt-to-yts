<?php

namespace App\Services\YTS\ValueObjects;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Movie
{
    /** @param  Collection<Torrent>  $torrents */
    public function __construct(
        public readonly string $title,
        public readonly int|null $year,
        public readonly Collection $torrents,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            title: Arr::get($response, 'title'),
            year: Arr::get($response, 'year'),
            torrents: collect(Arr::get($response, 'torrents', []))->map(
                fn (array $torrent): Torrent => Torrent::fromResponse($torrent)
            )
        );
    }
}
