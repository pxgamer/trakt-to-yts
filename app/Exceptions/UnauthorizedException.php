<?php

namespace App\Exceptions;

use RuntimeException;

class UnauthorizedException extends RuntimeException implements TraktToYTSException
{
    public static function forServiceWithId(string $serviceId): self
    {
        return new self("An API key has not been specified for service '{$serviceId}'");
    }
}
