<?php

declare(strict_types=1);

use App\Services\Trakt\Types\Movie;

it('can populate a Trakt movie from the metadata object', function () {
    $metadata = [
        'movie' => [
            'title' => 'Star Wars',
            'year' => 1977,
            'ids' => [
                'imdb' => 'tt0076759',
            ],
        ],
    ];

    $traktMovie = Movie::fromListing($metadata);

    $this->assertEquals('Star Wars', $traktMovie->title);
    $this->assertEquals(1977, $traktMovie->year);
    $this->assertEquals('tt0076759', $traktMovie->imdbId);
});
