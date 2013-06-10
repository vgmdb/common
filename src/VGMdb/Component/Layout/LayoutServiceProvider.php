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
        $app['layout.cache_dir'] = null;
        $app['layout.cache_class'] = null;
        $app['layout.base_dirs'] = array();
        $app['layout.files'] = null;
        $app['layout.parameters'] = array();
        $app['layout.filters'] = array();

        $app['layout.config'] = $app->share(function ($app) {
            $providers = $app['resource_locator']->getProviders();
            foreach ($providers as $provider) {
                $app['layout.base_dirs'] = array_merge($app['layout.base_dirs'], (array) $provider->getPath());
            }

            $options = array(
                'debug'       => $app['layout.debug'],
                'cache_dir'   => $app['layout.cache_dir'],
                'cache_class' => $app['layout.cache_class'],
                'base_dirs'   => $app['layout.base_dirs'],
                'files'       => $app['layout.files'],
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

            return isset($config['layouts']) ? $config['layouts'] : array();
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber(new LayoutListener($app)); // -32
    }
}
