<?php

namespace pxgamer\TraktToYts;

use Symfony\Component\Console\Application as BaseApplication;

/**
 * Class Application
 */
class Application extends BaseApplication
{
    const NAME = 'Trakt to YTS';
    const VERSION = '@git-version@';

    /**
     * Application constructor.
     *
     * @param null $name
     * @param null $version
     */
    public function __construct($name = null, $version = null)
    {
        parent::__construct(
            $name ?: static::NAME,
            $version ?: (static::VERSION === '@' . 'git-version@' ? 'source' : static::VERSION)
        );

        $this->setDefaultCommand('scrape', true);
    }

    /**
     * @return array|\Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new ScrapeCommand();

        return $commands;
    }
}
