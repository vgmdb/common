<?php

namespace VGMdb\Component\Routing\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Task for generating JSON routes.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RouterJsonDumperCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('router:json')
            ->setDescription('Generate Javascript routes.')
            ->setDefinition(array(
                new InputArgument(
                    'dest', InputArgument::OPTIONAL, 'Output file.', getcwd() . '/public/js/app/routes.js'
                ),
                new InputArgument(
                    'locale', InputArgument::OPTIONAL, 'Locale.', 'en'
                )
            ))
            ->setHelp(<<<EOT
Generate JSON routes.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true) * 1000;

        $outFile = $input->getArgument('dest');

        $content = json_encode($this->compileJsRoutes());

        if (false === @file_put_contents($outFile, $content)) {
            throw new \RuntimeException('Unable to write file ' . $outFile);
        }

        // show the time needed to parse the routes
        $end = microtime(true) * 1000;

        $output->writeln('Generated in ' . strval(intval($end - $start)) . 'ms.');
    }

    private function compileJsRoutes()
    {
        $routes = array();
        $app = $this->getApplication()->getContainer();
        $app->boot();
        $app->flush();
        $collection = $app['routes'];

        foreach ($collection->all() as $name => $route) {
            if (false === $route->getOption('expose')) {
                continue;
            }
            if ($method = $route->getRequirement('_method')) {
                if (strpos($method, 'GET') === false) {
                    continue;
                }
            }
            $route->addOptions(array(
                'compiler_class' => 'VGMdb\\Component\\Routing\\JsRouteCompiler'
            ));
            $compiledJsRoute = $route->compile();
            $jsRoute = $compiledJsRoute->getJsRoute();
            if (!$jsRoute = ltrim($jsRoute, '/')) {
                $jsRoute = '*path';
            }
            $routes[$this->camelCase($name)] = $jsRoute;
        }

        return array_flip($routes);
    }

    private function camelCase($string)
    {
        return preg_replace('#[-_ ](.?)#e', 'strtoupper(\'$1\')', $string);
    }
}
