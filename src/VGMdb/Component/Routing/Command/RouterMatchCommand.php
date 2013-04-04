<?php

namespace VGMdb\Component\Routing\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Matcher\TraceableUrlMatcher;

/**
 * A console command to test route matching.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RouterMatchCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('router:match')
            ->setDefinition(array(
                new InputArgument('method', InputArgument::REQUIRED, 'HTTP method'),
                new InputArgument('path_info', InputArgument::REQUIRED, 'A path info'),
            ))
            ->setDescription('Helps debug routes by simulating a path info match')
            ->setHelp(<<<EOF
The <info>%command.name%</info> simulates a path info match:

  <info>php %command.full_name% /foo</info>
EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getContainer($input);
        $app->boot();
        $app->flush();
        $matcher = new TraceableUrlMatcher($app['routes'], $app['request_context']);

        $app['request_context']->setMethod($input->getArgument('method'));
        $traces = $matcher->getTraces($input->getArgument('path_info'));

        $matches = false;
        foreach ($traces as $i => $trace) {
            if (TraceableUrlMatcher::ROUTE_ALMOST_MATCHES == $trace['level']) {
                $output->writeln(sprintf('<fg=yellow>Route "%s" almost matches but %s</>', $trace['name'], lcfirst($trace['log'])));
            } elseif (TraceableUrlMatcher::ROUTE_MATCHES == $trace['level']) {
                $output->writeln(sprintf('<fg=green>Route "%s" matches</>', $trace['name']));
                $matches = true;
            } elseif ($input->getOption('verbose')) {
                $output->writeln(sprintf('Route "%s" does not match: %s', $trace['name'], $trace['log']));
            }
        }

        if (!$matches) {
            $output->writeln('<fg=red>None of the routes match</>');

            return 1;
        }
    }
}
