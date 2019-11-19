<?php

namespace pxgamer\TraktToYts;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

class Application extends BaseApplication
{
    public const NAME = 'Trakt to YTS';
    public const VERSION = '@git-version@';

    /**
     * Application constructor.
     *
     * @param  null  $name
     * @param  null  $version
     */
    public function __construct($name = null, $version = null)
    {
        if (! $version) {
            $version = static::VERSION === '@'.'git-version@' ?
                'source' :
                static::VERSION;
        }

        parent::__construct(
            $name ?: static::NAME,
            $version
        );

        $this->setDefaultCommand('scrape', true);
    }

    /**
     * @return array|Command[]
     */
    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new ScrapeCommand();

        return $commands;
    }
}
