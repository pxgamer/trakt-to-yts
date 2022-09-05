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
use function Termwind\render;

class DownloadCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'download { trakt-user? : Trakt username for the list }
                                     { --l|list=watchlist : A custom list id or stub }
                                     { --o|output=./torrents : The directory to output data to }
                                     { --quality=1080p : The quality to download (720p, 1080p or 3D) }
                                     { --y|force : Do not prompt about downloading torrents }';

    /** {@inheritdoc} */
    protected $description = 'Download the contents of a Trakt list from YTS';

    /** @var Collection<Movie> */
    private Collection $traktList;

    private Quality $quality;

    private TraktClient $trakt;

    private YTSClient $yts;

    public function handle(TraktClient $traktClient, YTSClient $ytsClient): void
    {
        $this->trakt = $traktClient;
        $this->yts = $ytsClient;

        $this->quality = Quality::tryFrom($this->option('quality')) ?? Quality::Q_1080P;

        try {
            $this->retrieveTraktList(
                $this->argument('trakt-user') ?? $this->ask('What is the Trakt username'),
                $this->option('list') ?? $this->ask('What is the list slug or id'),
            )->downloadTorrentsFromYts();
        } catch (\RuntimeException $exception) {
            $this->warn($exception->getMessage());

            return;
        }
    }

    private function retrieveTraktList(string $username, string|null $list): self
    {
        $this->traktList = $this->trakt->getList($username, $list);

        $this->components->info(
            "<options=bold>{$list}</> (<options=bold>{$username}</>): Retrieved successfully",
            OutputInterface::VERBOSITY_VERBOSE
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

        $this->line('');

        /** @var Movie $movie */
        foreach ($this->traktList as $movie) {
            if (! $movie->imdbId) {
                continue;
            }

            if (! $ytsListing = $this->yts->getMovieByImdbId($movie->imdbId, $this->quality)) {
                $this->components->warn(
                    "'{$movie->title} ({$movie->year})': Not found on YTS",
                    OutputInterface::VERBOSITY_VERBOSE
                );

                continue;
            }

            if ($ytsListing->torrents->isEmpty()) {
                $this->components->warn(
                    "'{$movie->title} ({$movie->year})': No torrents available",
                    OutputInterface::VERBOSITY_VERBOSE
                );

                continue;
            }

            /** @var Torrent $matchedTorrent */
            $matchedTorrent = $ytsListing->torrents
                ->filter(fn (Torrent $torrent) => $torrent->quality instanceof $this->quality)
                ->first();

            if (! $matchedTorrent) {
                $this->components->warn(
                    "  ↳ '{$movie->title} ({$movie->year})': No torrent available in '{$this->quality->value}' quality",
                    OutputInterface::VERBOSITY_VERBOSE
                );

                continue;
            }

            $outputDirectory = $this->option('output');

            File::ensureDirectoryExists($outputDirectory);

            if ($this->yts->downloadTorrentTo(
                $matchedTorrent,
                "{$outputDirectory}/{$movie->title} ({$movie->year}) {$matchedTorrent->quality?->value}.torrent"
            )) {
                $this->components->info(
                    "'{$movie->title} ({$movie->year})': Successfully downloaded at '<options=bold>{$matchedTorrent->quality?->value}</>'"
                );
            }
        }
    }
}
