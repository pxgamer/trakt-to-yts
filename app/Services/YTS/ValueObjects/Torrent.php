<?php

namespace App\Services\YTS\ValueObjects;

use App\Services\YTS\Enums\Quality;
use Illuminate\Support\Arr;

class Torrent
{
    public function __construct(
        public readonly string $hash,
        public readonly string $url,
        public readonly Quality|null $quality,
        public readonly string|null $type,
        public readonly string|null $size,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            hash: Arr::get($response, 'hash'),
            url: Arr::get($response, 'url'),
            quality: Quality::tryFrom(Arr::get($response, 'quality')),
            type: Arr::get($response, 'type'),
            size: Arr::get($response, 'size'),
        );
    }
}
