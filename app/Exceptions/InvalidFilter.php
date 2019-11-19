<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidFilter extends RuntimeException
{
    public static function forQuality(string $providedQuality): self
    {
        return new self("The quality filter '{$providedQuality}' is invalid");
    }
}
