<?php

namespace VGMdb\Provider;

use VGMdb\Component\HttpFoundation\Session\Storage\Handler\NativeRedisSessionHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\Provider\SessionServiceProvider as BaseSessionServiceProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

/**
 * Extends Session Provider with Redis support.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class SessionServiceProvider extends BaseSessionServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['session.storage.handler'] = $app->share(function ($app) {
            if (extension_loaded('redis')) {
                return new NativeRedisSessionHandler();
            } else {
                return new NativeFileSessionHandler($app['session.storage.save_path']);
            }
        });
    }

    public function boot(Application $app)
    {
        parent::boot($app);

        $app->before(function ($request) use ($app) {
            $app['session']->start();
        });
    }
}
