<?php

namespace App\Commands;

use App\Services\Trakt\Client as TraktClient;
use App\Services\Trakt\Types\Movie;
use App\Services\YTS\Client as YTSClient;
use App\Services\YTS\Enums\Quality;
use App\Services\YTS\ValueObjects\Torrent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'download { trakt-user : Trakt username for the list }
                                     { --l|list=watchlist : A custom list id or stub }
                                     { --o|output=./torrents : The directory to output data to }
                                     { --quality=1080p : The quality to download (720p, 1080p or 3D) }
                                     { --statistics : Display download statistics }
                                     { --y|force : Do not prompt about downloading torrents }';

    /** {@inheritdoc} */
    protected $description = 'Download the contents of a Trakt list from YTS';

    /** @var Collection<Movie> */
    private Collection $traktList;

    private Quality|null $quality;

    private string $apiToken;

    public function handle(): void
    {
        $this->quality = Quality::tryFrom($this->option('quality'));

        try {
            $this->retrieveTraktList()
                ->downloadTorrentsFromYts();
        } catch (\RuntimeException $exception) {
            $this->warn($exception->getMessage());

            return;
        }
    }

    private function retrieveTraktList(): self
    {
        /** @var TraktClient $traktApi */
        $traktApi = app(TraktClient::class);

        $this->traktList = $traktApi->getList($this->argument('trakt-user'), $this->option('list'));

        $this->comment(
            "<options=bold>{$this->option('list')}</> (<options=bold>{$this->argument('trakt-user')}</>): Retrieved successfully",
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );

        $this->line('');

        $this->components->info("This list contains {$this->traktList->count()} movies");

        return $this;
    }

    private function downloadTorrentsFromYts(): void
    {
        if (! $this->option('force') && $this->confirm('Are you sure you would like to download them') === false) {
            return;
        }

        /** @var YTSClient $ytsApi */
        $ytsApi = app(YTSClient::class);

        $this->line('');

        /** @var Movie $movie */
        foreach ($this->traktList as $movie) {
            if (! $movie->imdbId) {
                continue;
            }

            if (! $ytsListing = $ytsApi->getMovieByImdbId($movie->imdbId, $this->quality)) {
                $this->components->warn(
                    "'{$movie->title} ({$movie->year})': Not found on YTS",
                    OutputInterface::VERBOSITY_VERY_VERBOSE
                );

                continue;
            }

            if ($ytsListing->torrents->isEmpty()) {
                $this->components->warn(
                    "'{$movie->title} ({$movie->year})': No torrents available",
                    OutputInterface::VERBOSITY_VERY_VERBOSE
                );

                continue;
            }

            /** @var Torrent $matchedTorrent */
            $matchedTorrent = $ytsListing->torrents
                ->filter(fn (Torrent $torrent) => $torrent->quality instanceof $this->quality)
                ->first();

            if (! $matchedTorrent) {
                $this->components->warn(
                    "'{$movie->title} ({$movie->year})': No torrent available in '{$this->quality?->value}' quality",
                    OutputInterface::VERBOSITY_VERY_VERBOSE
                );

                continue;
            }

            $outputDirectory = $this->option('output');

            File::ensureDirectoryExists($outputDirectory);

            if ($ytsApi->downloadTorrentTo(
                $matchedTorrent,
                "{$outputDirectory}/{$movie->title} ({$movie->year}) {$matchedTorrent->quality?->value}.torrent"
            )) {
                $this->components->info(
                    "'<options=bold>{$movie->title} ({$movie->year})</>': Successfully downloaded at '<options=bold>{$matchedTorrent->quality?->value}</>'",
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }
    }
}
