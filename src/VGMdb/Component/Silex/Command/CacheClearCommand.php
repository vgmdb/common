<?php

namespace VGMdb\Component\Silex\Command;

use VGMdb\Component\Config\CacheWarmer\ConfigCacheWarmer;
use VGMdb\Component\Routing\CacheWarmer\RouterCacheWarmer;
use VGMdb\Component\Doctrine\CacheWarmer\ProxyCacheWarmer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate;

/**
 * Clear and warm up the cache.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CacheClearCommand extends Command
{
    protected $name;
    protected $app;
    protected $fileystem;

    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDefinition(array(
                new InputOption('no-warmup', '', InputOption::VALUE_NONE, 'Do not warm up the cache'),
                new InputOption('no-optional-warmers', '', InputOption::VALUE_NONE, 'Skip optional cache warmers (faster)'),
            ))
            ->setDescription('Clears the cache')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command clears the application cache for a given environment
and debug mode:

<info>php %command.full_name% --env=dev</info>
<info>php %command.full_name% --env=prod --no-debug</info>
EOF
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->filesystem = new Filesystem();
        $this->app = $this->getApplication()->getContainer($input);
        $this->app->boot();
        $this->app->flush();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $realCacheDir = $this->app['cache_dir'];

        if (!$this->filesystem->exists($realCacheDir)) {
            $this->filesystem->mkdir($realCacheDir);
        }

        if (!is_writable($realCacheDir)) {
            throw new \RuntimeException(sprintf('Unable to write in the "%s" directory', $realCacheDir));
        }

        $output->writeln(sprintf(
            'Clearing the <info>%s</info> cache for <info>%s</info> environment with debug mode <info>%s</info>',
            $this->app['name'],
            $this->app['env'],
            $this->app['debug'] ? 'ON' : 'OFF'
        ));

        $start = microtime(true) * 1000;

        $warmupDir = $realCacheDir.'_new';
        $this->filesystem->remove($warmupDir);
        $this->filesystem->mkdir($warmupDir);

        if (!$input->getOption('no-warmup')) {
            $this->warmUp($warmupDir, !$input->getOption('no-optional-warmers'));
        }

        $oldCacheDir  = $realCacheDir.'_old';
        $this->filesystem->remove($oldCacheDir);

        rename($realCacheDir, $oldCacheDir);
        rename($warmupDir, $realCacheDir);

        $this->filesystem->remove($oldCacheDir);

        // show the time needed to clear the cache
        $end = microtime(true) * 1000;

        $output->writeln('Cleared in ' . strval(intval($end - $start)) . 'ms.');
    }

    protected function warmUp($warmupDir, $enableOptionalWarmers = true)
    {
        $warmers = array(
            new ConfigCacheWarmer($this->app['framework.loader.cache'], 'configs'),
            new RouterCacheWarmer($this->app['router'], 'configs')
        );

        if (isset($this->app['doctrine'])) {
            $warmers[] = new ProxyCacheWarmer($this->app['doctrine']);
        }

        $warmer = new CacheWarmerAggregate($warmers);

        if ($enableOptionalWarmers) {
            $warmer->enableOptionalWarmers();
        }

        $warmer->warmUp($warmupDir);
    }
}
