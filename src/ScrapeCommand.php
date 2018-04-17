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
     * The list id or slug
     *
     * @var string
     */
    private $list;
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
    private $quality = '1080p';
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
    protected function configure()
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
            ->addArgument(
                'quality',
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->apiKey = $input->getOption('key') ?? getenv('TRAKT_API_KEY');

        if (!$this->apiKey) {
            throw new \ErrorException('Unspecified API key.');
        }

        $this->outputDirectory = $input->getOption('output');
        $this->traktUser = $input->getArgument('trakt-user');
        $this->quality = $input->getArgument('quality');
        $this->list = $input->getArgument('list');

        $listData = $this->getJson(
            self::TRAKT_API_URI.'/users/'.$this->traktUser.'/watchlist/movies',
            [
                'headers' => [
                    'trakt-api-version' => 2,
                    'trakt-api-key'     => $this->apiKey,
                ],
            ]
        );

        if (empty($listData)) {
            $this->output->writeln('<info>No movies were found in this list.</info>');
        }

        $this->output->writeln([
            'List data from '.self::TRAKT_MAIN_URI.'/users/'.$this->traktUser.'/watchlist',
            '',
            'Movies: '.count($listData),
        ]);

        $downloadQuestion = new ConfirmationQuestion('Download torrent files for this list? (y/N) ', false);

        if ($this->getHelper('question')->ask($input, $output, $downloadQuestion)) {
            if (!is_dir($this->outputDirectory)) {
                mkdir($this->outputDirectory);
            }

            foreach ($listData as $datum) {
                if (!$datum->movie->ids->imdb) {
                    continue;
                }

                $ytsData = $this->getJson(
                    self::YTS_API_URI.'/list_movies.json?query_term='.
                    $datum->movie->ids->imdb.(isset($this->quality) ? '&quality='.$this->quality : '')
                );

                if (isset($ytsData->data->movies[0])) {
                    $current = $ytsData->data->movies[0];

                    foreach ($current->torrents as $torrent) {
                        if ($torrent->quality === $this->quality) {
                            $this->output->writeln('Downloading: '.$current->title_long.
                                                   ' ['.$current->imdb_code.'] in '.$torrent->quality);

                            $outputFile = $this->outputDirectory.DIRECTORY_SEPARATOR.$torrent->title_long.'.torrent';

                            file_put_contents(
                                $outputFile,
                                file_get_contents($torrent->url)
                            );

                            if (!file_exists($outputFile) || filesize($outputFile) < 1) {
                                $this->output->writeln('<error>Failed to download '.$current->title_long.'</error>');
                            }

                            break;
                        }
                    }
                }
            }
        }
    }

    private function getJson(string $url, array $options = null)
    {
        if (!isset($this->guzzle)) {
            $this->guzzle = new Client();
        }

        return \GuzzleHttp\json_decode(
            $this->guzzle->get($url, $options)
                         ->getBody()
                         ->getContents()
        );
    }
}
