<?php

namespace VGMdb\Component\Cache;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Stash\Driver\Apc;
use Stash\Driver\Composite;
use Stash\Driver\Ephemeral;
use Stash\Driver\FileSystem;
use Stash\Driver\Memcache;
use Stash\Driver\Redis;

/**
 * Provides a caching interface using Stash.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['cache.pools'] = array('debug' => array());

        $app['cache.hosts'] = array(
            'memcache.host' => '127.0.0.1',
            'memcache.port' => 11211,
            'redis.host' => '127.0.0.1',
            'redis.port' => 6379
        );

        $app['cache.factory'] = $app->share(function ($app) {
            return new CacheFactory($app, $app['cache.pools']);
        });

        $app['cache.driver.apc'] = $app->share(function ($app) {
            return new Apc();
        });

        $app['cache.driver.ephemeral'] = $app->share(function ($app) {
            return new Ephemeral();
        });

        $app['cache.driver.filesystem'] = $app->share(function ($app) {
            return new FileSystem($app['cache.drivers']['filesystem']);
        });

        $app['cache.driver.memcache'] = $app->share(function ($app) {
            return new Memcache(array(
                array($app['cache.hosts']['memcache.host'], $app['cache.hosts']['memcache.port'])
            ));
        });

        $app['cache.driver.redis'] = $app->share(function ($app) {
            return new Redis(array(
                array($app['cache.hosts']['redis.host'], $app['cache.hosts']['redis.port'])
            ));
        });
    }

    public function boot(Application $app)
    {
    }
}
