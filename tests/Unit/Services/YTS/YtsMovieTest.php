<?php

namespace Tests\Unit\Services\Trakt;

use Tests\TestCase;
use App\Services\YTS\YtsMovie;
use App\Services\YTS\YtsTorrent;

class YtsMovieTest extends TestCase
{
    /** @test */
    public function itCanPopulateAYtsMovieFromTheMetadataObject(): void
    {
        $metadata = (object) [
            'title' => 'Star Wars',
            'year' => 1977,
            'torrents' => (object) [
                (object) [
                    'hash' => '5398E1142A3710D683C88404D57B6966990A4535',
                    'url' => 'https://yts.lt/torrent/download/5398E1142A3710D683C88404D57B6966990A4535.torrent',
                    'quality' => '1080p',
                    'type' => 'bluray',
                    'size' => '1.72GB',
                ],
            ],
        ];

        $subject = new YtsMovie($metadata);

        $this->assertEquals('Star Wars', $subject->title);
        $this->assertEquals(1977, $subject->year);
        $this->assertContainsOnlyInstancesOf(YtsTorrent::class, $subject->torrents);
    }
}
