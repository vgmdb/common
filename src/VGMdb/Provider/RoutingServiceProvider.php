<?php

namespace VGMdb\Provider;

use VGMdb\Component\Routing\LazyRouter;
use VGMdb\Component\Routing\Loader\CachedYamlFileLoader;
use VGMdb\Component\HttpKernel\EventListener\RouteAttributeListener;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;

/**
 * Provides caching for routes loaded from YAML configuration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RoutingServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['routing.matcher_class'] = 'VGMdb\\Component\\Routing\\Matcher\\RedirectableUrlMatcher';
        $app['routing.matcher_base_class'] = 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher';
        $app['routing.matcher_proxy_class'] = 'VGMdb\\Component\\Routing\\Matcher\\RedirectableProxyUrlMatcher';
        $app['routing.matcher_cache_class'] = 'ProjectUrlMatcher';
        $app['routing.generator_cache_class'] = 'ProjectUrlGenerator';
        $app['routing.loader_cache_class'] = 'ProjectUrlLoader';
        $app['routing.parameters'] = array();

        $app['router'] = $app->share(function ($app) {
            $generatorClass = implode('', array_map('ucfirst', explode('-', $app['routing.generator_cache_class'])));
            $matcherClass = implode('', array_map('ucfirst', explode('-', $app['routing.matcher_cache_class'])));

            return new LazyRouter(
                $app,
                $app['routing.resource'],
                array(
                    'cache_dir'             => $app['routing.cache_dir'],
                    'debug'                 => $app['debug'],
                    'generator_cache_class' => $generatorClass,
                    'matcher_class'         => $app['routing.matcher_class'],
                    'matcher_base_class'    => $app['routing.matcher_base_class'],
                    'matcher_proxy_class'   => $app['routing.matcher_proxy_class'],
                    'matcher_cache_class'   => $matcherClass
                ),
                $app['routing.parameters'],
                $app['request_context'],
                $app['logger']
            );
        });

        $app['routing.loader'] = $app->share(function ($app) {
            $locator = new FileLocator($app['routing.resource']);
            $loader = new CachedYamlFileLoader($locator);

            $class = implode('', array_map('ucfirst', explode('-', $app['routing.loader_cache_class'])));
            $cache = new ConfigCache($app['routing.cache_dir'] . '/' . $class . '.php', $app['debug']);
            $loader->setCache($cache);

            $loader->setReplacements($app['routing.parameters']);

            return $loader;
        });

        $app['routing.resource'] = $app->share(function ($app) {
            $paths = array();
            if (substr($app['routing.config_dir'], -4) === '.yml') {
                $paths[] = $app['routing.config_dir'];
            } else {
                $paths = glob($app['routing.config_dir'] . '/*.yml');
            }

            return $paths;
        });

        // replace the default url matcher with the router cache
        $app['url_matcher'] = $app->share(function ($app) {
            return $app['router']->getMatcher();
        });

        // use the router's URL generator
        $app['url_generator'] = $app->share(function ($app) {
            $app->flush();

            return $app['router']->getGenerator();
        });

        $app['url'] = $app->protect(function ($name, $data = array(), $absolute = false) use ($app) {
            return $app['url_generator']->generate($name, $data, $absolute);
        });

        // make sure the default route collection is attached to the router
        $app['routes'] = $app->share($app->extend('routes', function ($routes, $app) {
            $collection = $app['router']->getRouteCollection();
            $collection->addCollection($routes);

            return $collection;
        }));

        $app['routing.attribute_listener'] = $app->share(function ($app) {
            return new RouteAttributeListener($app['request_context'], $app['logger']);
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['routing.attribute_listener']);
    }
}
