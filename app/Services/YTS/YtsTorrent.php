<?php

namespace App\Services\YTS;

use stdClass;

class YtsTorrent
{
    /** @var string */
    private $hash;
    /** @var string */
    private $url;
    /** @var string|null */
    private $quality;
    /** @var string|null */
    private $type;
    /** @var string|null */
    private $size;

    public function __construct(stdClass $metadata)
    {
        $this->hash = $metadata->hash ?? null;
        $this->url = $metadata->url ?? null;
        $this->quality = $metadata->quality ?? null;
        $this->type = $metadata->type ?? null;
        $this->size = $metadata->size ?? null;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

}
