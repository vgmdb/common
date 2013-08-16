<?php

namespace VGMdb\Component\Whoops;

use VGMdb\Component\Whoops\EventListener\WhoopsExceptionListener;
use Silex\ServiceProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

/**
 * Provides error handling with a pretty debug interface.
 *
 * @author Filipe Dobreira <http://github.com/filp>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class WhoopsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['whoops.error_page_handler'] = $app->share(function ($app) {
            return new PrettyPageHandler();
        });

        $app['whoops.silex_info_handler'] = $app->protect(function () use ($app) {
            /** @var PrettyPageHandler $errorPageHandler */
            $errorPageHandler = $app["whoops.error_page_handler"];

            // General application info:
            $errorPageHandler->addDataTable('Silex Application', array(
                'Charset'          => $app['charset'],
                'Locale'           => $app['locale'],
                'Route Class'      => $app['route_class'],
                'Dispatcher Class' => get_class($app['dispatcher']),
                'Application Class'=> get_class($app)
            ));

            try {
                /** @var Request $request */
                $request = $app['request'];
            } catch (\RuntimeException $e) {
                // This error occurred too early in the application's life
                // and the request instance is not yet available.
                return;
            }

            // Request info:
            $errorPageHandler->addDataTable('Silex Application (Request)', array(
                'URI'         => $request->getUri(),
                'Request URI' => $request->getRequestUri(),
                'Path Info'   => $request->getPathInfo(),
                'Query String'=> $request->getQueryString() ?: '<none>',
                'HTTP Method' => $request->getMethod(),
                'Script Name' => $request->getScriptName(),
                'Base Path'   => $request->getBasePath(),
                'Base URL'    => $request->getBaseUrl(),
                'Scheme'      => $request->getScheme(),
                'Port'        => $request->getPort(),
                'Host'        => $request->getHost(),
            ));

            // Request context:
            $errorPageHandler->addDataTable('Silex Application (Request Context)', $app['request_context']->toArray());
        });

        $app['whoops'] = $app->share(function ($app) {
            $run = new Run();
            $run->allowQuit(false);
            $run->pushHandler($app['whoops.error_page_handler']);
            $run->pushHandler($app['whoops.silex_info_handler']);

            return $run;
        });

        $app['whoops.exception_listener'] = $app->share(function ($app) {
            return new WhoopsExceptionListener($app, $app['whoops']);
        });
    }

    public function boot(Application $app)
    {
        $app['whoops']->register();
        $app['dispatcher']->addSubscriber($app['whoops.exception_listener']); // -127
    }
}
