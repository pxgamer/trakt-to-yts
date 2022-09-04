<?php

namespace App\Services\Trakt;

use GuzzleHttp\Client;
use stdClass;

class TraktApi
{
    public const SERVICE_ID = 'Trakt';

    public const BASE_URI = 'https://api.trakt.tv';

    /** @var string */
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param  string  $username
     * @param  string|null  $listId
     * @return array<TraktMovie>
     */
    public function getList(string $username, ?string $listId = null): array
    {
        $listUrl = $listId === null || $listId === 'watchlist' ?
            "/users/{$username}/watchlist/movies" :
            "/users/{$username}/lists/{$listId}/items/movies";

        $traktListResponse = $this->getGuzzleClient()->get($listUrl)->getBody();

        $decodedResponse = \GuzzleHttp\json_decode($traktListResponse);

        return array_map(static function (stdClass $listing): TraktMovie {
            return new TraktMovie($listing->movie);
        }, $decodedResponse);
    }

    private function getGuzzleClient(): Client
    {
        return new Client([
            'base_uri' => self::BASE_URI,
            'headers' => [
                'content-type' => 'application/json',
                'trakt-api-version' => 2,
                'trakt-api-key' => $this->apiKey,
            ],
        ]);
    }
}
