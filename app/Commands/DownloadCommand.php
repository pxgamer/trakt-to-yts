<?php

namespace App\Commands;

use App\Services\YTS\YtsApi;
use App\Services\Trakt\TraktApi;
use App\Services\Trakt\TraktMovie;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'download { trakt-user : Trakt username for the list }
                                     { --l|list=watchlist : A custom list id or stub }
                                     { --o|output=./torrents : The directory to output data to }
                                     { --quality= : The quality to download (720p, 1080p or 3D) }
                                     { --statistics : Display download statistics }
                                     { --y|force : Do not prompt about downloading torrents }';

    /** {@inheritdoc} */
    protected $description = 'Download the contents of a Trakt list from YTS';

    /** @var array<TraktMovie> */
    private $traktList;

    public function handle(): void
    {
        try {
            $this->retrieveTraktList();

            $this->comment('This list contains '.count($this->traktList).' movies');

            if ($this->option('force') || $this->confirm('Are you sure you would like to download them')) {
                $this->downloadTorrentsFromYts();
            }
        } catch (\RuntimeException $exception) {
            $this->warn($exception->getMessage());
        }
    }

    private function retrieveTraktList(): void
    {
        /** @var TraktApi $traktApi */
        $traktApi = app(TraktApi::class);

        $this->traktList = $traktApi->getList($this->argument('trakt-user'), $this->option('list'));

        $this->comment(
            "<options=bold>{$this->option('list')}</> (<options=bold>{$this->argument('trakt-user')}</>): Retrieved successfully",
            OutputInterface::VERBOSITY_VERBOSE
        );

        $this->line('');
    }

    private function downloadTorrentsFromYts(): void
    {
        /** @var YtsApi $ytsApi */
        $ytsApi = app(YtsApi::class);

        $this->line('');

        /** @var TraktMovie $movie */
        foreach ($this->traktList as $movie) {
            if (! $movie->getImdbId()) {
                continue;
            }

            if (! $ytsListing = $ytsApi->getMovieByImdbId($movie->getImdbId())) {
                $this->warn(
                    "'{$movie->getTitle()} ({$movie->getYear()})': Not found on YTS",
                    OutputInterface::VERBOSITY_VERBOSE
                );
                continue;
            }

            if ($ytsListing->getTorrents()->isEmpty()) {
                $this->warn(
                    "'{$movie->getTitle()} ({$movie->getYear()})': No torrents available",
                    OutputInterface::VERBOSITY_VERBOSE
                );
                continue;
            }
        }
    }
}
