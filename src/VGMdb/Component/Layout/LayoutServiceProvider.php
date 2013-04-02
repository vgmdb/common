<?php

namespace VGMdb\Component\Layout;

use VGMdb\Component\Config\ConfigLoader;
use VGMdb\Component\Config\CachedConfigLoader;
use VGMdb\Component\Layout\EventListener\LayoutListener;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Provides layout and widget configuration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LayoutServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['layout.debug'] = false;
        $app['layout.parameters'] = array();

        $app['layout.config'] = $app->share(function ($app) {
            $options = array(
                'debug'       => $app['layout.debug'],
                'cache_dir'   => $app['layout.cache_dir'],
                'cache_class' => $app['layout.cache_class'],
                'base_dirs'   => $app['layout.base_dirs'],
                'files'       => null,
                'parameters'  => $app['layout.parameters']
            );

            $app['layout.cached_loader'] = new CachedConfigLoader($options);
            $app['layout.loader'] = new ConfigLoader($options);
            $config = array();

            if ($app['cache']) {
                $config = $app['layout.cached_loader']->load($config);
            } else {
                $config = $app['layout.loader']->load($config);
            }

            return $config;
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(new LayoutListener($app)); // -32
    }
}
