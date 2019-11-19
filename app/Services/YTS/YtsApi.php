<?php

namespace App\Services\YTS;

use stdClass;
use GuzzleHttp\Client;
use App\Exceptions\InvalidFilter;

class YtsApi
{
    public const SERVICE_ID = 'YTS';
    public const BASE_URI = 'https://yts.lt';

    /**
     * @param  string  $imdbId
     * @param  string|null  $quality
     * @return YtsMovie
     */
    public function getMovieByImdbId(string $imdbId, ?string $quality = null): YtsMovie
    {
        if (! in_array($quality, ['1080p', '720p', '3D', null], true)) {
            throw InvalidFilter::forQuality($quality);
        }

        $ytsMovieQueryResponse = $this->getGuzzleClient()->get('/api/v2/list_movies.json', [
            'query_term' => $imdbId,
            'quality' => $quality,
        ])->getBody();

        $decodedResponse = \GuzzleHttp\json_decode($ytsMovieQueryResponse);

        return new YtsMovie($decodedResponse->movies);
    }

    private function getGuzzleClient(): Client
    {
        return new Client([
            'base_uri' => self::BASE_URI,
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);
    }
}
