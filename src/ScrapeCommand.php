<?php

namespace pxgamer\TraktToYts;

use function count;
use ErrorException;
use Exception;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;
use function in_array;
use function is_dir;
use function mkdir;
use RuntimeException;
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ScrapeCommand extends Command
{
    public const ALLOWED_QUALITIES = [
        Quality::Q_1080P,
        Quality::Q_720P,
        Quality::Q_3D,
    ];
    public const STATUS_DOWNLOADED = 'downloaded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_NO_RELEASE = 'no-release';
    public const TRAKT_API_URI = 'https://api.trakt.tv';
    public const TRAKT_MAIN_URI = 'https://trakt.tv';
    public const YTS_API_URI = 'https://yts.am/api/v2';

    /** @var string|null A Trakt API key */
    private $apiKey;

    /** @var Client The Guzzle client */
    private $guzzle;

    /** @var InputInterface The Input interface */
    private $input;

    /** @var string The list id or slug */
    private $list;

    /** @var array The list data from Trakt */
    private $listData;

    /** @var OutputInterface The Output interface */
    private $output;

    /** @var string|null The directory to download torrent files to */
    private $outputDirectory = 'torrents';

    /** @var string|null The quality to download from YTS */
    private $quality;

    /** @var array Recorded statistics for the app */
    private $statistics = [
        self::STATUS_DOWNLOADED => 0,
        self::STATUS_FAILED => 0,
        self::STATUS_NO_RELEASE => 0,
    ];

    /** @var string A Trakt username */
    private $traktUser;

    protected function configure(): void
    {
        $this
            ->setName('scrape')
            ->setDescription('Scrape a user\'s list from Trakt to YTS.')
            ->addArgument(
                'trakt-user',
                InputArgument::REQUIRED,
                'The Trakt username for the list.'
            )
            ->addOption(
                'key',
                null,
                InputOption::VALUE_REQUIRED,
                'Your Trakt API key. Defaults to use the `TRAKT_API_KEY` environment variable if not provided.'
            )
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_REQUIRED,
                'A custom list id or stub (defaults to wishlist).'
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'The directory to output data to (defaults to `torrents`).',
                'torrents'
            )
            ->addOption(
                'quality',
                null,
                InputOption::VALUE_REQUIRED,
                'The quality to download (720p, 1080p or 3D).',
                '1080p'
            )
            ->addOption(
                'statistics',
                null,
                InputOption::VALUE_NONE,
                'Display statistics information output.'
            );
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;

        $this->parseInput($input);

        $this->getListData();

        $this->output->writeln([
            'List data from '.($this->list ?
                self::TRAKT_MAIN_URI.'/users/'.$this->traktUser.'/lists/'.$this->list :
                self::TRAKT_MAIN_URI.'/users/'.$this->traktUser.'/watchlist'),
            '',
            'Movies: '.count($this->listData),
        ]);

        if ($this->askConfirmation('Download torrent files for this list? (y/N) ')) {
            $this->downloadTorrents();

            $this->statistics();
        }
    }

    /**
     * @param  InputInterface  $input
     * @throws ErrorException
     */
    private function parseInput(InputInterface $input): void
    {
        $this->apiKey = $input->getOption('key') ?? getenv('TRAKT_API_KEY');

        if (! $this->apiKey) {
            throw new ErrorException('Unspecified API key.');
        }

        $this->traktUser = $input->getArgument('trakt-user');
        $this->outputDirectory = $input->getOption('output');
        $this->quality = $input->getOption('quality');

        if (! in_array($this->quality, self::ALLOWED_QUALITIES, true)) {
            throw new ErrorException('Invalid quality specified.');
        }

        $this->list = $input->getOption('list');
    }

    /**
     * @throws ErrorException
     */
    private function getListData(): void
    {
        $listUrl = $this->list ?
            self::TRAKT_API_URI.'/users/'.$this->traktUser.'/lists/'.$this->list.'/items/movies' :
            self::TRAKT_API_URI.'/users/'.$this->traktUser.'/watchlist/movies';

        $this->listData = $this->getJson(
            $listUrl,
            [
                'headers' => [
                    'trakt-api-version' => 2,
                    'trakt-api-key' => $this->apiKey,
                ],
            ]
        );

        if (empty($this->listData)) {
            throw new ErrorException('No movies were found in this list.');
        }
    }

    /**
     * @param  string  $url
     * @param  array|null  $options
     * @return array
     */
    private function getJson(string $url, array $options = null): array
    {
        if ($this->guzzle === null) {
            $this->guzzle = new Client();
        }

        return json_decode(
            $this->guzzle->get($url, $options)
                ->getBody()
                ->getContents(),
            true
        );
    }

    /**
     * @param  string  $question
     * @return bool
     */
    private function askConfirmation(string $question): bool
    {
        $questionHelper = new ConfirmationQuestion(
            $question,
            false
        );

        return $this
            ->getHelper('question')
            ->ask($this->input, $this->output, $questionHelper);
    }

    private function downloadTorrents(): void
    {
        if (! is_dir($this->outputDirectory) &&
            ! mkdir($concurrentDirectory = $this->outputDirectory) &&
            ! is_dir($concurrentDirectory)
        ) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        foreach ($this->listData as $datum) {
            if (! $datum['movie']['ids']['imdb']) {
                continue;
            }

            $ytsData = $this->getJson(
                self::YTS_API_URI.'/list_movies.json?query_term='.
                $datum['movie']['ids']['imdb'].($this->quality !== null ? '&quality='.$this->quality : '')
            );

            if (isset($ytsData['data']['movies'][0])) {
                $current = $ytsData['data']['movies'][0];

                foreach ($current['torrents'] as $torrent) {
                    if ($torrent['quality'] === $this->quality) {
                        $this->output->writeln(
                            '<info>Downloading:</info> '.$current['title_long'].
                            ' in '.$torrent['quality']
                        );

                        $outputFile = $this->outputDirectory.DIRECTORY_SEPARATOR.
                            $current['title_long'].' '.$torrent['quality'].'.torrent';

                        if (! $this->getTorrentFile($torrent['url'], $outputFile)) {
                            $this->output->writeln(
                                '<error>Failed to download:</error> '.$current['title_long']
                            );
                            $this->statistics[self::STATUS_FAILED]++;
                        }

                        $this->statistics[self::STATUS_DOWNLOADED]++;

                        break;
                    }
                }
            } else {
                $this->output->writeln(
                    '<comment>No YTS release:</comment> '.$datum['movie']['title']
                );
                $this->statistics[self::STATUS_NO_RELEASE]++;
            }
        }
    }

    private function getTorrentFile(string $url, string $downloadPath): bool
    {
        if ($this->guzzle === null) {
            $this->guzzle = new Client();
        }

        return $this->guzzle
                ->get($url, [
                    'sink' => $downloadPath,
                ])
                ->getStatusCode() === 200;
    }

    private function statistics(): void
    {
        if ($this->input->getOption('statistics')) {
            $this->output->writeln([
                '',
                '<options=bold,underscore>Statistics</>',
                '<info>Downloaded: </info>'.$this->statistics[self::STATUS_DOWNLOADED],
                '<comment>No release: </comment>'.$this->statistics[self::STATUS_NO_RELEASE],
                '<comment>Failed: </comment>    '.$this->statistics[self::STATUS_FAILED],
            ]);
        }
    }
}
