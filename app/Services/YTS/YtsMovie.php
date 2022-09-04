<?php

namespace App\Services\YTS;

use Illuminate\Support\Collection;
use stdClass;

class YtsMovie
{
    /** @var string */
    public $title;

    /** @var int|null */
    public $year;

    /** @var Collection<YtsTorrent> */
    public $torrents;

    public function __construct(stdClass $metadata)
    {
        $this->title = $metadata->title ?? null;
        $this->year = $metadata->year ?? null;

        $this->torrents = Collection::make();

        /** @var stdClass $torrent */
        foreach ($metadata->torrents ?? [] as $torrent) {
            $this->torrents->push(new YtsTorrent($torrent));
        }
    }
}
