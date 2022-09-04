<?php

namespace App\Commands;

use App\Services\Trakt\TraktApi;
use App\Services\Trakt\TraktMovie;
use App\Services\YTS\YtsApi;
use App\Services\YTS\YtsTorrent;
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

            return;
        }
    }

    private function retrieveTraktList(): void
    {
        /** @var TraktApi $traktApi */
        $traktApi = app(TraktApi::class);

        $this->traktList = $traktApi->getList($this->argument('trakt-user'), $this->option('list'));

        $this->comment(
            "<options=bold>{$this->option('list')}</> (<options=bold>{$this->argument('trakt-user')}</>): Retrieved successfully",
            OutputInterface::VERBOSITY_VERY_VERBOSE
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
            if (! $movie->imdbId) {
                continue;
            }

            if (! $ytsListing = $ytsApi->getMovieByImdbId($movie->imdbId, $this->option('quality'))) {
                $this->warn(
                    "'{$movie->title} ({$movie->year})': Not found on YTS",
                    OutputInterface::VERBOSITY_VERY_VERBOSE
                );

                continue;
            }

            if ($ytsListing->torrents->isEmpty()) {
                $this->warn(
                    "'{$movie->title} ({$movie->year})': No torrents available",
                    OutputInterface::VERBOSITY_VERY_VERBOSE
                );

                continue;
            }

            /** @var YtsTorrent $matchedTorrent */
            $matchedTorrent = $ytsListing->torrents->firstWhere('quality', '=', $this->option('quality'));

            if (! $matchedTorrent) {
                $this->warn(
                    "'{$movie->title} ({$movie->year})': No torrent available in '{$this->option('quality')}' quality",
                    OutputInterface::VERBOSITY_VERY_VERBOSE
                );

                continue;
            }

            if ($ytsApi->downloadTorrentTo(
                $matchedTorrent,
                "{$this->option('output')}/{$movie->title} ({$movie->year}) {$matchedTorrent->quality}.torrent"
            )) {
                $this->comment(
                    "'<options=bold>{$movie->title} ({$movie->year})</>': Successfully downloaded at '<options=bold>{$matchedTorrent->quality}</>'",
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }
    }
}
