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
use Stash\Pool;

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

        $app['cache.driver.apc'] = $app->share(function ($app) {
            return new Apc();
        });

        $app['cache.driver.ephemeral'] = $app->share(function ($app) {
            return new Ephemeral();
        });

        $app['cache.driver.filesystem'] = $app->share(function ($app) {
            return new FileSystem($app['cache.driver.filesystem.path']);
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

        foreach ($app['cache.pools'] as $name => $options) {
            $app['cache.'.$name] = $app->share(function ($app) use ($name, $options) {
                $drivers = array('drivers' => array());

                foreach ($options['drivers'] as $driver) {
                    if (!isset($app['cache.driver.'.$driver])) {
                        continue;
                    }
                    try {
                        $drivers['drivers'][] = $app['cache.driver.'.$driver];
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if (!count($drivers['drivers'])) {
                    $drivers['drivers'][] = $app['cache.driver.ephemeral'];
                }

                return new Pool($drivers);
            });
        }
    }

    public function boot(Application $app)
    {
    }
}
