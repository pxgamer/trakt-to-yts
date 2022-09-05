<?php

namespace App\Providers;

use App\Exceptions\UnauthorizedException;
use App\Services\Trakt\Client;
use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function (): Client {
            if (! $traktApiKey = $this->app->get(Repository::class)->get('services.trakt.token')) {
                throw UnauthorizedException::forServiceWithId(Client::SERVICE_ID);
            }

            return new Client($traktApiKey);
        });
    }
}
