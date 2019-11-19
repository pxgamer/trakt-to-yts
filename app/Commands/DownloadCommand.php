<?php

namespace App\Commands;

use App\Services\YTS\YtsApi;
use App\Services\Trakt\TraktApi;
use App\Services\Trakt\TraktMovie;
use LaravelZero\Framework\Commands\Command;

class DownloadCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'download { trakt-user : Trakt username for the list }
                                     { --l|list=watchlist : A custom list id or stub }
                                     { --o|output=./torrents : The directory to output data to }
                                     { --quality= : The quality to download (720p, 1080p or 3D) }
                                     { --statistics : Display download statistics }';

    /** {@inheritdoc} */
    protected $description = 'Download the contents of a Trakt list from YTS';

    /** @var array<TraktMovie> */
    private $traktList;

    public function handle(): void
    {
        $this->retrieveTraktList();

        $this->downloadTorrentsFromYts();
    }

    private function retrieveTraktList(): void
    {
        /** @var TraktApi $traktApi */
        $traktApi = app(TraktApi::class);

        $this->traktList = $traktApi->getList($this->argument('trakt-user'), $this->option('list'));

        $this->comment("<options=bold>{$this->option('list')}</> (<options=bold>{$this->argument('trakt-user')}</>): Retrieved successfully");
        $this->line(null);
    }

    private function downloadTorrentsFromYts(): void
    {
        /** @var YtsApi $ytsApi */
        $ytsApi = app(YtsApi::class);

        /** @var TraktMovie $movie */
        foreach ($this->traktList as $movie) {
            if (! $movie->getImdbId()) {
                continue;
            }

            if (! $ytsListing = $ytsApi->getMovieByImdbId($movie->getImdbId())) {
                $this->warn("'{$movie->getTitle()} ({$movie->getYear()})': Not found on YTS");
                continue;
            }

            if ($ytsListing->getTorrents()->isEmpty()) {
                $this->warn("'{$movie->getTitle()} ({$movie->getYear()})': No torrents available");
                continue;
            }
        }
    }
}
