<?php

namespace App\Services\YTS;

use stdClass;
use Illuminate\Support\Collection;

class YtsMovie
{
    /** @var string */
    private $title;
    /** @var int|null */
    private $year;
    /** @var Collection<YtsTorrent> */
    private $torrents;

    public function __construct(stdClass $metadata)
    {
        $this->title = $metadata->title ?? null;
        $this->year = $metadata->year ?? null;

        $this->torrents = Collection::make();

        /** @var stdClass $torrent */
        foreach ($metadata->torrents ?? [] as $torrent) {
            $ytsTorrent = new YtsTorrent($torrent);

            $this->torrents->put($ytsTorrent->getQuality(), $ytsTorrent);
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    /** @return Collection<YtsTorrent> */
    public function getTorrents(): Collection
    {
        return $this->torrents;
    }
}
