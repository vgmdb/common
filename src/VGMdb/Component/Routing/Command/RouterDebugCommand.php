<?php

namespace VGMdb\Component\Routing\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * A console command for retrieving information about routes
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RouterDebugCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('router:debug')
            ->setDefinition(array(
                new InputArgument('name', InputArgument::OPTIONAL, 'A route name'),
            ))
            ->setDescription('Displays current routes for an application')
            ->setHelp(<<<EOF
The <info>%command.name%</info> displays the configured routes:

  <info>php %command.full_name%</info>
EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        if ($name) {
            $this->outputRoute($output, $name);
        } else {
            $this->outputRoutes($output);
        }
    }

    protected function outputRoutes(OutputInterface $output, $routes = null)
    {
        if (null === $routes) {
            $app = $this->getApplication()->getContainer();
            $app->boot();
            $app->flush();
            $routes = $app['routes']->all();
        }

        $output->writeln($this->getHelper('formatter')->formatSection('router', 'Current routes'));

        $maxName = strlen('name');
        $maxMethod = strlen('method');
        $maxHost = strlen('host');

        foreach ($routes as $name => $route) {
            $requirements = $route->getRequirements();
            $method = isset($requirements['_method'])
                ? strtoupper(is_array($requirements['_method'])
                    ? implode(', ', $requirements['_method']) : $requirements['_method']
                )
                : 'ANY';
            $host = '' !== $route->getHost() ? $route->getHost() : 'ANY';
            $maxName = max($maxName, strlen($name));
            $maxMethod = max($maxMethod, strlen($method));
            $maxHost = max($maxHost, strlen($host));
        }
        $format  = '%-'.$maxName.'s %-'.$maxMethod.'s %-'.$maxHost.'s %s';

        // displays the generated routes
        $format1  = '%-'.($maxName + 19).'s %-'.($maxMethod + 19).'s %-'.($maxHost + 19).'s %s';
        $output->writeln(sprintf($format1, '<comment>Name</comment>', '<comment>Method</comment>', '<comment>Host</comment>', '<comment>Pattern</comment>'));
        foreach ($routes as $name => $route) {
            $requirements = $route->getRequirements();
            $method = isset($requirements['_method'])
                ? strtoupper(is_array($requirements['_method'])
                    ? implode(', ', $requirements['_method']) : $requirements['_method']
                )
                : 'ANY';
            $host = '' !== $route->getHost() ? $route->getHost() : 'ANY';
            $output->writeln(sprintf($format, $name, $method, $host, $route->getPath()));
        }
    }

    /**
     * @throws \InvalidArgumentException When route does not exist
     */
    protected function outputRoute(OutputInterface $output, $name)
    {
        $app = $this->getApplication()->getContainer();
        $app->boot();
        $app->flush();
        $route = $app['routes']->get($name);
        if (!$route) {
            throw new \InvalidArgumentException(sprintf('The route "%s" does not exist.', $name));
        }

        $host = '' !== $route->getHost() ? $route->getHost() : 'ANY';

        $output->writeln($this->getHelper('formatter')->formatSection('router', sprintf('Route "%s"', $name)));

        $output->writeln(sprintf('<comment>Name</comment>            %s', $name));
        $output->writeln(sprintf('<comment>Pattern</comment>         %s', $route->getPath()));
        $output->writeln(sprintf('<comment>Host</comment>            %s', $host));
        $output->writeln(sprintf('<comment>Class</comment>           %s', get_class($route)));

        $defaults = '';
        $d = $route->getDefaults();
        ksort($d);
        foreach ($d as $name => $value) {
            $defaults .= ($defaults ? "\n".str_repeat(' ', 16) : '').$name.': '.$this->formatValue($value);
        }
        $output->writeln(sprintf('<comment>Defaults</comment>        %s', $defaults));

        $requirements = '';
        $r = $route->getRequirements();
        ksort($r);
        foreach ($r as $name => $value) {
            $requirements .= ($requirements ? "\n".str_repeat(' ', 16) : '').$name.': '.$this->formatValue($value);
        }
        $requirements = '' !== $requirements ? $requirements : 'NONE';
        $output->writeln(sprintf('<comment>Requirements</comment>    %s', $requirements));

        $options = '';
        $o = $route->getOptions();
        ksort($o);
        foreach ($o as $name => $value) {
            $options .= ($options ? "\n".str_repeat(' ', 16) : '').$name.': '.$this->formatValue($value);
        }
        $output->writeln(sprintf('<comment>Options</comment>         %s', $options));
        $output->write('<comment>Regex</comment>           ');
        $output->writeln(preg_replace('/^             /', '', preg_replace('/^/m', '             ', $route->compile()->getRegex())), OutputInterface::OUTPUT_RAW);
    }

    protected function formatValue($value)
    {
        if (is_object($value)) {
            return sprintf('object(%s)', get_class($value));
        }

        if (is_string($value)) {
            return $value;
        }

        return preg_replace("/\n\s*/s", '', var_export($value, true));
    }
}
