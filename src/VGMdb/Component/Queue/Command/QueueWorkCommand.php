<?php

namespace VGMdb\Component\Queue\Command;

use VGMdb\Component\Queue\ContainerAwareWorker;
use VGMdb\Component\Queue\Runner;
use VGMdb\Component\Silex\ResourceLocatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Task for running queue workers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class QueueWorkCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('queue:work')
            ->setDefinition(array(
                new InputArgument('queue', InputArgument::REQUIRED, 'The queue identifier'),
                new InputArgument('worker', InputArgument::REQUIRED, 'The worker class'),
                new InputOption(
                    'run-once', null, InputOption::VALUE_NONE,
                    'Whether to run the job once'
                ),
                new InputOption(
                    'no-wait', null, InputOption::VALUE_NONE,
                    'Whether to wait for a job'
                )
            ))
            ->setDescription('Process jobs on a queue')
            ->setHelp(<<<EOF
Process jobs on a queue. This will run continuously unless the --run-once flag is set.
EOF
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getContainer($input);

        $workerClass = $this->resolveWorker($input->getArgument('worker'), $app['resource_locator']);

        $output->writeln(sprintf('Processing jobs on "%s" with worker "%s"...', $input->getArgument('queue'), $input->getArgument('worker')));

        $queue = $app['queue']->getQueue($input->getArgument('queue'));
        $worker = new $workerClass($queue);

        if ($worker instanceof ContainerAwareWorker) {
            $worker->setContainer($app);
        }

        $counter = intval($input->getOption('run-once')) ?: null;
        $wait = $input->getOption('no-wait') ? false : true;

        $runner = new Runner($worker, array('wait' => $wait));
        $runner->run($counter);
    }

    private function resolveWorker($worker, ResourceLocatorInterface $locator)
    {
        if (2 != count($parts = explode(':', $worker))) {
            throw new \InvalidArgumentException(sprintf('The "%s" worker is not a valid a:b worker string.', $worker));
        }

        list($provider, $worker) = $parts;
        $worker = str_replace('/', '\\', $worker);
        $providers = array();

        foreach ($locator->getProvider($provider, false) as $prov) {
            $try = $prov->getNamespace().'\\Worker\\'.$worker.'Worker';
            if (class_exists($try)) {
                return $try;
            }

            $providers[] = $prov->getName();
            $msg = sprintf('Unable to find worker "%s:%s" - class "%s" does not exist.', $provider, $worker, $try);
        }

        if (count($providers) > 1) {
            $msg = sprintf('Unable to find worker "%s:%s" in providers %s.', $provider, $worker, implode(', ', $providers));
        }

        throw new \InvalidArgumentException($msg);
    }
}
