<?php

namespace App\Providers;

use App\Exceptions\ApiKeyNotSpecified;
use App\Services\Trakt\TraktApi;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TraktApi::class, static function (): TraktApi {
            if (! $traktApiKey = env('TRAKT_API_KEY')) {
                throw ApiKeyNotSpecified::forServiceWithId(TraktApi::SERVICE_ID);
            }

            return new TraktApi($traktApiKey);
        });
    }
}
