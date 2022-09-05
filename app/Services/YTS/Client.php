<?php

namespace App\Services\YTS;

use App\Exceptions\NoMovieDataFoundException;
use App\Services\YTS\Enums\Quality;
use App\Services\YTS\ValueObjects\Movie;
use App\Services\YTS\ValueObjects\Torrent;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class Client
{
    public const BASE_URI = 'https://yts.mx';

    public function getMovieByImdbId(string|null $imdbId, Quality|null $quality = null): Movie
    {
        $response = Http::baseUrl(self::BASE_URI)->get('/api/v2/list_movies.json', [
            'query_term' => $imdbId,
            'quality' => $quality,
        ])->json();

        if (! Arr::has($response, 'data.movies.0')) {
            throw NoMovieDataFoundException::forImdbIdOnYts($imdbId);
        }

        return Movie::fromResponse(
            Arr::get($response, 'data.movies.0')
        );
    }

    public function downloadTorrentTo(Torrent $torrent, ?string $destination = null): bool
    {
        return Http::withHeaders([
            'Content-Type' => 'application/x-bittorrent',
        ])
            ->sink($destination)
            ->get($torrent->url)
            ->ok();
    }
}
