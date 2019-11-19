<?php

namespace App\Services\Trakt;

use stdClass;

class TraktMovie
{
    /** @var string */
    private $title;
    /** @var int|null */
    private $year;
    /** @var string|null */
    private $imdbId;

    public function __construct(stdClass $metadata)
    {
        $this->title = $metadata->title ?? null;
        $this->year = $metadata->year ?? null;
        $this->imdbId = $metadata->ids->imdb ?? null;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function getImdbId(): ?string
    {
        return $this->imdbId;
    }
}
