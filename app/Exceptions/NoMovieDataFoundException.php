<?php

namespace App\Exceptions;

use RuntimeException;

class NoMovieDataFoundException extends RuntimeException implements TraktToYTSException
{
    public static function forImdbIdOnYts(string $imdbId): self
    {
        return new self("No YTS data was found for IMDb id '{$imdbId}'");
    }
}
