<?php

namespace VGMdb\Component\Console;

use Silex\Application as Silex;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Silex-compatible console application.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Application extends BaseApplication
{
    private $container;

    /**
     * Constructor.
     *
     * @param Silex $container
     */
    public function __construct(Silex $container, $name = 'Silex', $version = Silex::VERSION)
    {
        $this->container = $container;

        parent::__construct($name, $version);

        $this->getDefinition()->addOption(new InputOption('--shell', '-s', InputOption::VALUE_NONE, 'Launch the shell.'));
        $this->getDefinition()->addOption(new InputOption('--process-isolation', null, InputOption::VALUE_NONE, 'Launch commands from shell as a separate processes.'));
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
        $this->getDefinition()->addOption(new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'));
        $this->getDefinition()->addOption(new InputOption('--enable-cache', null, InputOption::VALUE_NONE, 'Switches on caching.'));
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands();

        if (true === $input->hasParameterOption(array('--shell', '-s'))) {
            $class = isset($this->container['shell.class']) ? $this->container['shell.class'] : 'Symfony\\Component\\Console\\Shell';
            $shell = new $class($this);
            $shell->setProcessIsolation($input->hasParameterOption(array('--process-isolation')));
            $shell->run();

            return 0;
        }

        return parent::doRun($input, $output);
    }

    public function getContainer()
    {
        return $this->container;
    }

    protected function registerCommands()
    {
        $this->container->boot();

        // @todo Get Commands from each ServiceProvider that implements SilexBundleInterface

        foreach ($this->container['command.classmap'] as $namespace => $path) {
            $files = glob($path . '/*.php');
            foreach ($files as $file) {
                $class = $namespace . '\\' . basename($file, '.php');
                if (class_exists($class)) {
                    $this->add(new $class());
                }
            }
        }
    }
}
