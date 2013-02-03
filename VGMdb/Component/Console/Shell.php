<?php

namespace VGMdb\Component\Console;

use VGMdb\Component\Console\Command\ExitCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Shell as BaseShell;

/**
 * Shell.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Shell extends BaseShell
{
    public function __construct(BaseApplication $application)
    {
        $application->add(new ExitCommand());

        parent::__construct($application);
    }

    protected function getHeader()
    {
        $header = parent::getHeader();

        return str_replace('<comment>^D</comment>', '<comment>exit</comment> or <comment>quit</comment>', $header);
    }
}
