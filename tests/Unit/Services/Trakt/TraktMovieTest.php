<?php

declare(strict_types=1);

use App\Services\Trakt\TraktMovie;

it('can populate a Trakt movie from the metadata object', function () {
    $metadata = (object) [
        'title' => 'Star Wars',
        'year' => 1977,
        'ids' => (object) [
            'imdb' => 'tt0076759',
        ],
    ];

    $traktMovie = new TraktMovie($metadata);

    $this->assertEquals('Star Wars', $traktMovie->title);
    $this->assertEquals(1977, $traktMovie->year);
    $this->assertEquals('tt0076759', $traktMovie->imdbId);
});
