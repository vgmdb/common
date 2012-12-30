<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\Provider\MonologServiceProvider as BaseMonologServiceProvider;
use Monolog\Handler\FirePHPHandler;

/**
 * Monolog Provider without the cruft in boot().
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MonologServiceProvider extends BaseMonologServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['monolog.handler'] = $app->share(function () use ($app) {
            return new FirePHPHandler();
        });
    }

    public function boot(Application $app)
    {
    }
}
