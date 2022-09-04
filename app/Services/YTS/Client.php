<?php

namespace App\Services\YTS;

use App\Exceptions\NoMovieDataFound;
use App\Services\YTS\Enums\Quality;
use App\Services\YTS\ValueObjects\Movie;
use App\Services\YTS\ValueObjects\Torrent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class Client
{
    public const SERVICE_ID = 'YTS';

    public const BASE_URI = 'https://yts.mx';

    /**
     * @param  string  $imdbId
     * @param  Quality|null  $quality
     * @return Movie
     */
    public function getMovieByImdbId(string $imdbId, Quality|null $quality = null): Movie
    {
        $response = Http::baseUrl(self::BASE_URI)->get('/api/v2/list_movies.json', [
            'query_term' => $imdbId->value ?? $imdbId,
            'quality' => $quality,
        ])->json();

        if (! Arr::has($response, 'data.movies.0')) {
            throw NoMovieDataFound::forImdbIdOnYts($imdbId);
        }

        return Movie::fromResponse($response->data->movies[0]);
    }

    public function downloadTorrentTo(Torrent $torrent, ?string $destination = null): bool
    {
        return Http::withHeaders([
            'Content-Type' => 'application/x-bittorrent',
        ])
            ->sink($destination)
            ->get($torrent->url)
            ->isOk();
    }
}
