<?php

namespace App\Services\Trakt\Types;

use Illuminate\Support\Arr;

class Movie
{
    public function __construct(
        public readonly string|null $title,
        public readonly int|null $year,
        public readonly string|null $imdbId
    ) {
    }

    public static function fromListing(array $response): self
    {
        return new self(
            title: Arr::get($response, 'movie.title'),
            year: Arr::get($response, 'movie.year'),
            imdbId: Arr::get($response, 'movie.ids.imdb'),
        );
    }
}
