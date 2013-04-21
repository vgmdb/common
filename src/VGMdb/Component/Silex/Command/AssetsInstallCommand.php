<?php

namespace VGMdb\Component\Silex\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class AssetsInstallCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('assets:install')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'public'),
            ))
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
            ->setDescription('Installs web assets under a public web directory')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command installs web assets into a given
directory (e.g. the public directory).

<info>php %command.full_name% public</info>

An "assets" directory will be created inside the target directory, and the
"Resources/public" directory of each resource will be copied into it.

To create a symlink to each resource instead of copying its assets, use the
<info>--symlink</info> option:

<info>php %command.full_name% public --symlink</info>

To make symlink relative, add the <info>--relative</info> option:

<info>php %command.full_name% public --symlink --relative</info>

EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetArg = rtrim($input->getArgument('target'), '/');

        if (!is_dir($targetArg)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }

        if (!function_exists('symlink') && $input->getOption('symlink')) {
            throw new \InvalidArgumentException('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }

        $app = $this->getApplication()->getContainer($input);
        $filesystem = new Filesystem();

        // Create the bundles directory otherwise symlink will fail.
        $filesystem->mkdir($targetArg.'/assets/', 0777);

        $output->writeln(sprintf("Installing assets using the <comment>%s</comment> option", $input->getOption('symlink') ? 'symlink' : 'hard copy'));

        foreach ($app['resource_locator']->getProviders() as $provider) {
            if (is_dir($originDir = $provider->getPath().'/Resources/public')) {
                $assetsDir = $targetArg.'/assets/';
                $targetDir  = $assetsDir.strtolower($provider->getName());

                $output->writeln(sprintf('Installing assets for <comment>%s</comment> into <comment>%s</comment>', $provider->getNamespace(), $targetDir));

                $filesystem->remove($targetDir);

                if ($input->getOption('symlink')) {
                    if ($input->getOption('relative')) {
                        $relativeOriginDir = $filesystem->makePathRelative($originDir, realpath($assetsDir));
                    } else {
                        $relativeOriginDir = $originDir;
                    }
                    $filesystem->symlink($relativeOriginDir, $targetDir);
                } else {
                    $filesystem->mkdir($targetDir, 0777);
                    // We use a custom iterator to ignore VCS files
                    $filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
                }
            }
        }
    }
}
