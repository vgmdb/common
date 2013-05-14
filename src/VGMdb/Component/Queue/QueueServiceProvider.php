<?php

namespace VGMdb\Component\Queue;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Asynchronous queueing services.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class QueueServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['queue.configs'] = array();

        $app['queue'] = $app->share(function($app) {
            return new QueueServiceFactory($app['queue.configs'], $app['logger']);
        });

        $app['queue.service'] = $app->protect(function ($worker, $provider, array $options) use ($app) {
            return new QueueService($worker, $provider, $options, $app['logger']);
        });
    }

    public function boot(Application $app)
    {
    }
}
