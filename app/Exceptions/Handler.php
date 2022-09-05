<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use function Termwind\render;
use Throwable;

class Handler extends ExceptionHandler
{
    public function renderForConsole($output, Throwable $e): void
    {
        if ($e instanceof UnauthorizedException) {
            render(view('unauthorised')->render());

            return;
        }

        if ($e instanceof TraktToYTSException) {
            render(view('error', ['message' => $e->getMessage()])->render());

            return;
        }

        parent::renderForConsole($output, $e);
    }
}
