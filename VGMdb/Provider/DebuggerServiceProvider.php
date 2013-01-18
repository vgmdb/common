<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides debugging tools and traces.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DebuggerServiceProvider implements ServiceProviderInterface
{
    protected $enabled;

    public function __construct($enabled = true)
    {
        $this->enabled = (Boolean) $enabled;
    }

    public function register(Application $app)
    {
        if (!$this->enabled) {
            return;
        }

        // stopwatch
        $app['debug.stopwatch.class'] = 'Symfony\\Component\\Stopwatch\\Stopwatch';

        $app['debug.stopwatch'] = $app->share(function ($app) {
            return new $app['debug.stopwatch.class']();
        });

        // deprecation listener
        $app['debug.deprecation_logger_listener.class'] = 'Symfony\\Component\\HttpKernel\\EventListener\\DeprecationLoggerListener';

        $app['debug.deprecation_logger_listener'] = $app->share(function ($app) {
            return new $app['debug.deprecation_logger_listener.class']($app['logger']);
        });

        // replace dispatcher class with traceable implementation
        $app['debug.dispatcher.class'] = 'VGMdb\\Component\\HttpKernel\\Debug\\TraceableEventDispatcher';

        $app['dispatcher'] = $app->share($app->extend('dispatcher', function ($dispatcher) use ($app) {
            $debugDispatcher = new $app['debug.dispatcher.class']($dispatcher, $app['debug.stopwatch'], $app['logger']);

            return $debugDispatcher;
        }));

        // replace controller resolver with traceable implementation
        $app['debug.resolver.class'] = 'Symfony\\Component\\HttpKernel\\Controller\\TraceableControllerResolver';

        $app['resolver'] = $app->share($app->extend('resolver', function ($resolver) use ($app) {
            return new $app['debug.resolver.class']($resolver, $app['debug.stopwatch']);
        }));

        // replace view logger with traceable implementation
        $app['debug.view_logger.class'] = 'VGMdb\\Component\\View\\Logging\\ViewLogger';

        $app['view.logger'] = $app->share(function ($app) {
            return new $app['debug.view_logger.class']($app['logger'], $app['debug.stopwatch']);
        });

        // replace Doctrine logger with traceable implementation
        $app['debug.db_logger.class'] = 'VGMdb\\Component\\Doctrine\\DBAL\\Logging\\SQLDebugLogger';

        $app['db.debug_logger'] = $app->share(function ($app) {
            return new $app['debug.db_logger.class']($app['logger'], $app['debug.stopwatch']);
        });

        $app['db.logger'] = $app->share($app->extend('db.logger', function ($dbLogger) use ($app) {
            $dbLogger->addLogger($app['db.debug_logger']);

            return $dbLogger;
        }));

        // replace Propel logger with traceable implementation
        $app['debug.propel_logger.class'] = 'VGMdb\\Component\\Propel\\Logger\\PropelLogger';

        $app['propel.logger'] = $app->share(function ($app) {
            return new $app['debug.propel_logger.class']($app['logger'], $app['debug.stopwatch']);
        });

        // add debug logger and request subscriber to Guzzle
        $app['guzzle.debug_logger'] = $app->share(function() use ($app) {
            return new \Guzzle\Plugin\Log\LogPlugin($app['guzzle.debug_logger.adapter']);
        });

        $app['guzzle.debug_logger.adapter'] = $app->share(function() use ($app) {
            return new \Guzzle\Log\ArrayLogAdapter();
        });

        $app['guzzle.request_listener'] = $app->share(function() use ($app) {
            return new \VGMdb\Component\Guzzle\EventListener\GuzzleRequestListener($app['debug.stopwatch']);
        });

        $app['guzzle.client'] = $app->share($app->extend('guzzle.client', function ($guzzle) use ($app) {
            $guzzle->addSubscriber($app['guzzle.debug_logger']);
            $guzzle->addSubscriber($app['guzzle.request_listener']);

            return $guzzle;
        }));

        // replace Serializer with traceable implementation
        $app['debug.serializer.class'] = 'VGMdb\\Component\\Serializer\\Debug\\TraceableSerializer';

        $app['serializer'] = $app->share($app->extend('serializer', function ($serializer) use ($app) {
            return new $app['debug.serializer.class']($serializer, $app['debug.stopwatch'], $app['logger']);
        }));
    }

    public function boot(Application $app)
    {
        if (!$this->enabled) {
            return;
        }

        $app['dispatcher']->addSubscriber($app['debug.deprecation_logger_listener']);
    }
}
