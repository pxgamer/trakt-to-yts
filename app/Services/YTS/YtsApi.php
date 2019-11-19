<?php

namespace App\Services\YTS;

use GuzzleHttp\Client;
use App\Exceptions\InvalidFilter;
use App\Exceptions\NoMovieDataFound;

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
            'query' => [
                'query_term' => $imdbId,
                'quality' => $quality,
            ],
        ])->getBody();

        $decodedResponse = \GuzzleHttp\json_decode($ytsMovieQueryResponse);

        if (! ($decodedResponse->data->movies[0] ?? null)) {
            throw NoMovieDataFound::forImdbIdOnYts($imdbId);
        }

        return new YtsMovie($decodedResponse->data->movies[0]);
    }

    public function downloadTorrentTo(YtsTorrent $torrent, ?string $destination = null): bool
    {
        return $this->getGuzzleClient()->get($torrent->url, [
                'headers' => [
                    'content-type' => 'application/x-bittorrent',
                ],
                'sink' => $destination,
            ])->getStatusCode() === 200;
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
