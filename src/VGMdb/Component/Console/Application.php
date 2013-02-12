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
    private $app;
    protected $options;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(array $options = array(), $name = 'Silex', $version = Silex::VERSION)
    {
        $this->options = $options;

        parent::__construct($name, $version);

        $this->getDefinition()->addOption(new InputOption('--shell', '-s', InputOption::VALUE_NONE, 'Launch the shell.'));
        $this->getDefinition()->addOption(new InputOption('--process-isolation', null, InputOption::VALUE_NONE, 'Launch commands from shell as a separate processes.'));
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
        $this->getDefinition()->addOption(new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'));
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
            $class = isset($this->options['shell.class']) ? $this->options['shell.class'] : 'Symfony\\Component\\Console\\Shell';
            $shell = new $class($this);
            $shell->setProcessIsolation($input->hasParameterOption(array('--process-isolation')));
            $shell->run();

            return 0;
        }

        return parent::doRun($input, $output);
    }

    public function getContainer(InputInterface $input = null)
    {
        if (null === $this->app) {
            if (null !== $input) {
                $debug = !$input->getOption('no-debug');
                $env = $input->hasOption('env') ? $input->getOption('env') : 'dev';
                $app_name = $input->hasOption('app') ? $input->getOption('app') : 'project';
            }
            $app = require($this->options['app.path']);
            $this->app = $app;
        }

        return $this->app;
    }

    protected function registerCommands()
    {
        foreach ($this->options['command.classmap'] as $namespace => $path) {
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
