<?php

namespace VGMdb\Provider;

use VGMdb\Component\Queue\QueueService;
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
        $app['queue.service_factory'] = $app->protect(function ($worker, $provider, array $config) use ($app) {
            return new QueueService($worker, $provider, $config, $app['logger']);
        });
    }

    public function boot(Application $app)
    {
    }
}
