<?php

namespace pxgamer\TraktToYts;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class RunCommand
 */
class ScrapeCommand extends Command
{
    const TRAKT_API_URI = 'https://api.trakt.tv';
    const TRAKT_MAIN_URI = 'https://trakt.tv';
    const YTS_API_URI = 'https://yts.am/api/v2';
    const ALLOWED_QUALITIES = [
        Quality::Q_1080P,
        Quality::Q_720P,
        Quality::Q_3D,
    ];

    /**
     * A Trakt API key
     *
     * @var string|null
     */
    private $apiKey;
    /**
     * The Guzzle client
     *
     * @var Client
     */
    private $guzzle;
    /**
     * The Input interface
     *
     * @var InputInterface
     */
    private $input;
    /**
     * The list id or slug
     *
     * @var string
     */
    private $list;
    /**
     * The list data from Trakt
     *
     * @var array
     */
    private $listData;
    /**
     * The Output interface
     *
     * @var OutputInterface
     */
    private $output;
    /**
     * The directory to download torrent files to
     *
     * @var string|null
     */
    private $outputDirectory = 'torrents';
    /**
     * The quality to download from YTS
     *
     * @var string|null
     */
    private $quality;
    /**
     * A Trakt username
     *
     * @var string
     */
    private $traktUser;

    /**
     * Configure the command options.
     *
     * @return void
     */
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
            );
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     * @throws \Exception
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
        }
    }

    /**
     * @param InputInterface $input
     * @throws \ErrorException
     */
    private function parseInput(InputInterface $input): void
    {
        $this->apiKey = $input->getOption('key') ?? getenv('TRAKT_API_KEY');

        if (!$this->apiKey) {
            throw new \ErrorException('Unspecified API key.');
        }

        $this->traktUser = $input->getArgument('trakt-user');
        $this->outputDirectory = $input->getOption('output');
        $this->quality = $input->getOption('quality');

        if (!in_array($this->quality, self::ALLOWED_QUALITIES)) {
            throw new \ErrorException('Invalid quality specified.');
        }

        $this->list = $input->getOption('list');
    }

    /**
     * @throws \ErrorException
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
                    'trakt-api-key'     => $this->apiKey,
                ],
            ]
        );

        if (empty($this->listData)) {
            throw new \ErrorException('No movies were found in this list.');
        }
    }

    /**
     * @param string     $url
     * @param array|null $options
     * @return array
     */
    private function getJson(string $url, array $options = null): array
    {
        if (!isset($this->guzzle)) {
            $this->guzzle = new Client();
        }

        return \GuzzleHttp\json_decode(
            $this->guzzle->get($url, $options)
                ->getBody()
                ->getContents(),
            true
        );
    }

    /**
     * @param string $question
     * @return bool
     */
    private function askConfirmation(string $question): bool
    {
        $question = new ConfirmationQuestion(
            $question,
            false
        );

        return $this
            ->getHelper('question')
            ->ask($this->input, $this->output, $question);
    }

    /**
     * Download the torrent files from YTS
     */
    private function downloadTorrents(): void
    {
        if (!is_dir($this->outputDirectory)) {
            mkdir($this->outputDirectory);
        }

        foreach ($this->listData as $datum) {
            if (!$datum['movie']['ids']['imdb']) {
                continue;
            }

            $ytsData = $this->getJson(
                self::YTS_API_URI.'/list_movies.json?query_term='.
                $datum['movie']['ids']['imdb'].(isset($this->quality) ? '&quality='.$this->quality : '')
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

                        if (!$this->getTorrentFile($torrent['url'], $outputFile)) {
                            $this->output->writeln(
                                '<error>Failed to download:</error> '.$current['title_long']
                            );
                        }

                        break;
                    }
                }
            } else {
                $this->output->writeln(
                    '<comment>No YTS release:</comment> '.$datum['movie']['title']
                );
            }
        }
    }

    /**
     * @param string $url
     * @param string $downloadPath
     * @return bool
     */
    private function getTorrentFile(string $url, string $downloadPath): bool
    {
        if (!isset($this->guzzle)) {
            $this->guzzle = new Client();
        }

        return $this->guzzle
                ->get($url, [
                    'sink' => $downloadPath,
                ])
                ->getStatusCode() === 200;
    }
}
