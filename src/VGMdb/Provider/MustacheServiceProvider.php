<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Mustache templating integration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MustacheServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['mustache.loader_class'] = 'VGMdb\\Component\\View\\Mustache\\Loader\\PrefixLoader';
        $app['mustache.helpers'] = array();

        $app['mustache'] = $app->share(function ($app) {
            $loader = new $app['mustache.loader_class'](
                $app['mustache.template_dir'],
                array('extension' => $app['mustache.extension'])
            );

            $mustache = new \Mustache_Engine(array(
                'cache' => $app['mustache.cache_dir'],
                'loader' => $loader,
                'partials_loader' => $loader,
                'logger' => $app['logger']
            ));

            if (isset($app['translator'])) {
                $mustache->addHelper('t', function ($string) use ($app) {
                    $string = trim($string);
                    if (false !== strpos($string, '{{!')) {
                        $string = trim(substr(strstr($string, '}}', true), 3));
                    }
                    return $app['translator']->trans($string);
                });
            } else {
                $mustache->addHelper('t', function ($string) {
                    return $string;
                });
            }

            foreach ($app['mustache.helpers'] as $name => $helper) {
                if (is_callable($helper) || $helper instanceof \Closure) {
                    $mustache->addHelper($name, $helper);
                } elseif (class_exists($helper)) {
                    $mustache->addHelper($name, new $helper($app));
                }
            }

            return $mustache;
        });
    }

    public function boot(Application $app)
    {
    }
}
