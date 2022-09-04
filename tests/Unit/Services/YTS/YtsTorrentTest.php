<?php

declare(strict_types=1);

use App\Services\YTS\YtsTorrent;

it('can populate a YTS movie from the metadata object', function () {
    $metadata = (object) [
        'hash' => '5398E1142A3710D683C88404D57B6966990A4535',
        'url' => 'https://yts.lt/torrent/download/5398E1142A3710D683C88404D57B6966990A4535.torrent',
        'quality' => '1080p',
        'type' => 'bluray',
        'size' => '1.72GB',
    ];

    $subject = new YtsTorrent($metadata);

    $this->assertEquals('5398E1142A3710D683C88404D57B6966990A4535', $subject->hash);
    $this->assertEquals('https://yts.lt/torrent/download/5398E1142A3710D683C88404D57B6966990A4535.torrent',
        $subject->url);
    $this->assertEquals('1080p', $subject->quality);
    $this->assertEquals('bluray', $subject->type);
    $this->assertEquals('1.72GB', $subject->size);
});
