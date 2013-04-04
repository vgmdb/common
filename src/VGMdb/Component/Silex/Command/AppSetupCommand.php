<?php

namespace VGMdb\Component\Silex\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Task for initializing the workspace.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class AppSetupCommand extends Command
{
    protected $app;

    protected function configure()
    {
        $this
            ->setName('app:setup')
            ->setDescription('Initialize the workspace.')
            ->setHelp(<<<EOT
Initialize the workspace.
EOT
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->app = $this->getApplication()->getContainer($input);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true) * 1000;

        $debug = !$input->getOption('no-debug');
        $env = $input->hasOption('env') ? $input->getOption('env') : 'dev';
        $app_name = $input->hasOption('app') ? $input->getOption('app') : 'propertyguru-sg';

        $output->writeln(sprintf(
            'Setting up the <info>%s</info> app for <info>%s</info> environment with debug mode <info>%s</info>',
            $app_name,
            $env,
            $debug ? 'ON' : 'OFF'
        ));

        $output->writeln('<info>Fixing permissions...</info>');

        $baseDir = $this->app['base_dir'];
        $cacheDir = $this->app['cache_dir'];
        $logDir = $this->app['log_dir'];
        $filesystem = new Filesystem();

        $directories = array(
            $logDir,
            $cacheDir,
            $cacheDir . '/annotations',
            $cacheDir . '/assets',
            $cacheDir . '/configs',
            $cacheDir . '/metadata',
            $cacheDir . '/proxies',
            $cacheDir . '/translations',
            $cacheDir . '/views',
            $cacheDir . '/sessions'
        );

        $filesystem->mkdir($directories);
        $filesystem->chmod($directories, 0777);

        if (!file_exists($baseDir . '/public/css/lib.css')) {
            $filesystem->touch($baseDir . '/public/css/lib.css');
        }
        $filesystem->chmod($baseDir . '/public/css/lib.css', 0777);

        // show the time needed
        $end = microtime(true) * 1000;

        $output->writeln('Setup in ' . strval(intval($end - $start)) . 'ms.');
    }
}
