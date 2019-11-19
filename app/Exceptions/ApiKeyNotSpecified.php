<?php

namespace App\Exceptions;

use RuntimeException;

class ApiKeyNotSpecified extends RuntimeException
{
    public static function forServiceWithId(string $serviceId): self
    {
        return new self("An API key has not been specified for service '{$serviceId}'");
    }
}
