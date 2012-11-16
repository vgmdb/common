<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * @brief       Mustache templating integration.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class MustacheServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['mustache'] = $app->share(function () use ($app) {
            $loader = new \Mustache_Loader_FilesystemLoader(
                $app['mustache.template_dir'],
                array('extension' => $app['mustache.extension'])
            );
            $mustache = new \Mustache_Engine(array(
                'cache' => $app['mustache.cache_dir'],
                'loader' => $loader,
                'partials_loader' => $loader
            ));

            return $mustache;
        });
    }

    public function boot(Application $app)
    {
    }
}