<?php

namespace App\Services\Trakt;

use stdClass;

class TraktMovie
{
    /** @var string */
    public $title;
    /** @var int|null */
    public $year;
    /** @var string|null */
    public $imdbId;

    public function __construct(stdClass $metadata)
    {
        $this->title = $metadata->title ?? null;
        $this->year = $metadata->year ?? null;
        $this->imdbId = $metadata->ids->imdb ?? null;
    }
}
