<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class DownloadCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'download { trakt-user : Trakt username for the list }
                                     { --key= : Trakt API key (defaults to use the `TRAKT_API_KEY` environment variable if not provided) }
                                     { --l|list=watchlist : A custom list id or stub }
                                     { --o|output=./torrents : The directory to output data to }
                                     { --quality= : The quality to download (720p, 1080p or 3D) }
                                     { --statistics : Display download statistics }';

    /** {@inheritdoc} */
    protected $description = 'Download the contents of a Trakt list from YTS';

    public function handle(): void
    {
    }
}
