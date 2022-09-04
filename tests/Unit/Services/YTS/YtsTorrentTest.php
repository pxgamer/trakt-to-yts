<?php

declare(strict_types=1);

use App\Services\YTS\Enums\Quality;
use App\Services\YTS\ValueObjects\Torrent;

it('can populate a YTS movie from the metadata object', function () {
    $metadata = [
        'hash' => '5398E1142A3710D683C88404D57B6966990A4535',
        'url' => 'https://yts.lt/torrent/download/5398E1142A3710D683C88404D57B6966990A4535.torrent',
        'quality' => '1080p',
        'type' => 'bluray',
        'size' => '1.72GB',
    ];

    $subject = Torrent::fromResponse($metadata);

    $this->assertEquals('5398E1142A3710D683C88404D57B6966990A4535', $subject->hash);
    $this->assertEquals('https://yts.lt/torrent/download/5398E1142A3710D683C88404D57B6966990A4535.torrent',
        $subject->url);
    $this->assertEquals(Quality::Q_1080P, $subject->quality);
    $this->assertEquals('bluray', $subject->type);
    $this->assertEquals('1.72GB', $subject->size);
});
