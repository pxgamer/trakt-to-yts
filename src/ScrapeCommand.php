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
    /**
     * A Trakt API key
     *
     * @var string|null
     */
    private $apiKey;

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
                'The Trakt username for the watchlist.'
            )
            ->addOption(
                'key',
                null,
                InputOption::VALUE_REQUIRED,
                'Your Trakt API key. Defaults to use the `TRAKT_API_KEY` environment variable if not provided.'
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
        $this->apiKey = $input->getOption('key') ?? getenv('TRAKT_API_KEY');

        if (!$this->apiKey) {
            throw new \ErrorException('Unspecified API key.');
        }

        $outputDirectory = $input->getOption('output');
        $traktUser = $input->getArgument('trakt-user');
        $quality = $input->getArgument('quality');

        $listData = \GuzzleHttp\json_decode((new Client())->get(
            'https://api.trakt.tv/users/'.$traktUser.'/watchlist/movies',
            [
                'headers' => [
                    'trakt-api-version' => 2,
                    'trakt-api-key'     => $this->apiKey,
                ],
            ]
        )
                                                          ->getBody()
                                                          ->getContents()
        );

        if (empty($listData)) {
            $output->writeln('<info>No movies were found in this list.</info>');
        }

        $output->writeln([
            'List data from https://trakt.tv/users/'.$traktUser.'/watchlist',
            '',
            'Movies: '.count($listData),
        ]);

        $downloadQuestion = new ConfirmationQuestion('Download torrent files for this list? (y/N) ', false);

        if ($this->getHelper('question')->ask($input, $output, $downloadQuestion)) {
            if (!is_dir($outputDirectory)) {
                mkdir($outputDirectory);
            }

            foreach ($listData as $datum) {
                if (!$datum->movie->ids->imdb) {
                    continue;
                }

                $ytsData = \GuzzleHttp\json_decode((new Client())->get(
                    'https://yts.am/api/v2/list_movies.json?query_term='.
                    $datum->movie->ids->imdb.(isset($quality) ? '&quality='.$quality : ''),
                    [
                        'headers' => [
                            'trakt-api-version' => 2,
                            'trakt-api-key'     => $this->apiKey,
                        ],
                    ]
                )
                                                                 ->getBody()
                                                                 ->getContents()
                );

                if (isset($ytsData->data->movies[0])) {
                    $current = $ytsData->data->movies[0];

                    $output->writeln('Downloading: '.$current->title_long.' ['.$current->imdb_code.']');
                    foreach ($current->torrents as $torrent) {
                        if ($torrent->quality === $quality) {
                            file_put_contents(
                                $outputDirectory.DIRECTORY_SEPARATOR.$torrent->hash.'.torrent',
                                file_get_contents($torrent->url)
                            );

                            break;
                        }
                    }
                }
            }
        }
    }
}
