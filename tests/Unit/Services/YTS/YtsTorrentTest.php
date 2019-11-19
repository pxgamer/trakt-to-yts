<?php

namespace Tests\Unit\Services\Trakt;

use Tests\TestCase;
use App\Services\YTS\YtsTorrent;

class YtsTorrentTest extends TestCase
{
    /** @test */
    public function itCanPopulateAYtsMovieFromTheMetadataObject(): void
    {
        $metadata = (object) [
            'hash' => '5398E1142A3710D683C88404D57B6966990A4535',
            'url' => 'https://yts.lt/torrent/download/5398E1142A3710D683C88404D57B6966990A4535.torrent',
            'quality' => '1080p',
            'type' => 'bluray',
            'size' => '1.72GB',
        ];

        $subject = new YtsTorrent($metadata);

        $this->assertEquals('5398E1142A3710D683C88404D57B6966990A4535', $subject->getHash());
        $this->assertEquals('https://yts.lt/torrent/download/5398E1142A3710D683C88404D57B6966990A4535.torrent', $subject->getUrl());
        $this->assertEquals('1080p', $subject->getQuality());
        $this->assertEquals('bluray', $subject->getType());
        $this->assertEquals('1.72GB', $subject->getSize());
    }
}
