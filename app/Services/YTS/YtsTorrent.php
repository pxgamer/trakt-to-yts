<?php

namespace App\Services\YTS;

use stdClass;

class YtsTorrent
{
    /** @var string */
    public $hash;
    /** @var string */
    public $url;
    /** @var string|null */
    public $quality;
    /** @var string|null */
    public $type;
    /** @var string|null */
    public $size;

    public function __construct(stdClass $metadata)
    {
        $this->hash = $metadata->hash ?? null;
        $this->url = $metadata->url ?? null;
        $this->quality = $metadata->quality ?? null;
        $this->type = $metadata->type ?? null;
        $this->size = $metadata->size ?? null;
    }
}
