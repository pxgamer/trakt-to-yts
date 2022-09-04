<?php

declare(strict_types=1);

namespace App\Services\Trakt;

use App\Services\Trakt\Types\Movie;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Client
{
    public const SERVICE_ID = 'Trakt';

    public const API_VERSION = 2;

    public const BASE_URI = 'https://api.trakt.tv';

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /** @return Collection<Movie> */
    public function getList(string $username, ?string $listId = null): Collection
    {
        $listUrl = $listId === null || $listId === 'watchlist' ?
            "/users/{$username}/watchlist/movies" :
            "/users/{$username}/lists/{$listId}/items/movies";

        $response = Http::baseUrl(self::BASE_URI)->withHeaders([
            'trakt-api-version' => self::API_VERSION,
            'trakt-api-key' => $this->apiKey,
        ])->get($listUrl)->json();

        return collect($response)
            ->map(fn (array $listing): Movie => Movie::fromListing($listing));
    }
}
