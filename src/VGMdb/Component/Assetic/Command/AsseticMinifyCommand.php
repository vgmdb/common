<?php

namespace VGMdb\Component\Assetic\Command;

use VGMdb\Component\Assetic\EventListener\AsseticDumperListener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Task for minifying static assets.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class AsseticMinifyCommand extends Command
{
    private $app;

    protected function configure()
    {
        $this
            ->setName('assetic:minify')
            ->setDescription('Minify static assets.')
            ->setHelp(<<<EOT
Minify static assets.
EOT
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->app = $this->getApplication()->getContainer($input);
        $this->app->boot();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true) * 1000;

        $dumper = new AsseticDumperListener($this->app);
        $dumper->dumpAssets();

        // show the time needed to dump the assets
        $end = microtime(true) * 1000;

        $output->writeln('Generated in ' . strval(intval($end - $start)) . 'ms.');
    }
}
