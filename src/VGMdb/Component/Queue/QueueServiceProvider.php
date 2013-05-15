<?php

namespace VGMdb\Component\Queue;

use VGMdb\Component\Silex\AbstractResourceProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Asynchronous queueing services.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class QueueServiceProvider extends AbstractResourceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['queue.configs'] = array();

        $app['queue'] = $app->share(function($app) {
            return new QueueFactory($app['queue.configs'], $app['logger']);
        });

        $app['queue.proto'] = $app->protect(function ($worker, $provider, array $options) use ($app) {
            return new Queue($worker, $provider, $options, $app['logger']);
        });
    }

    public function boot(Application $app)
    {
    }
}
