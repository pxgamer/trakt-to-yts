<?php

namespace Tests\Unit\Services\Trakt;

use Tests\TestCase;
use App\Services\Trakt\TraktMovie;

class TraktMovieTest extends TestCase
{
    /** @test */
    public function itCanPopulateATraktMovieFromTheMetadataObject(): void
    {
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
    }
}
