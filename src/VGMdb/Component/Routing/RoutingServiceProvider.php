<?php

namespace VGMdb\Component\Routing;

use VGMdb\Component\Silex\AbstractResourceProvider;
use VGMdb\Component\Routing\Loader\YamlFileLoader;
use VGMdb\Component\Routing\Loader\ClosureLoaderResolver;
use VGMdb\Component\Routing\Loader\AllowedMethodsLoader;
use VGMdb\Component\Routing\EventListener\RouteAttributeListener;
use VGMdb\Component\Routing\EventListener\AllowedMethodsListener;
use VGMdb\Component\Config\FileLocator;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator as BaseFileLocator;

/**
 * Provides caching for routes loaded from YAML configuration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RoutingServiceProvider extends AbstractResourceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['routing.router_class'] = 'VGMdb\\Component\\Routing\\LazyRouter';
        $app['routing.matcher_class'] = 'VGMdb\\Component\\Routing\\Matcher\\RedirectableUrlMatcher';
        $app['routing.matcher_base_class'] = 'VGMdb\\Component\\Routing\\Matcher\\RedirectableUrlMatcher';
        $app['routing.matcher_proxy_class'] = 'VGMdb\\Component\\Routing\\Matcher\\RedirectableProxyUrlMatcher';
        $app['routing.matcher_cache_class'] = 'ProjectUrlMatcher';
        $app['routing.generator_cache_class'] = 'ProjectUrlGenerator';
        $app['routing.methods_cache_class'] = 'ProjectUrlMethods';
        $app['routing.parameters'] = array();

        $app['router'] = $app->share(function ($app) {
            $generatorClass = implode('', array_map('ucfirst', explode('-', $app['routing.generator_cache_class'])));
            $matcherClass = implode('', array_map('ucfirst', explode('-', $app['routing.matcher_cache_class'])));

            $router = new $app['routing.router_class'](
                $app['routing.loader'],
                $app['routing.paths'],
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

            return $router;
        });

        $app['routing.loader'] = $app->share(function ($app) {
            if (isset($app['resource_locator'])) {
                $locator = new FileLocator($app['resource_locator'], $app['routing.resource'], $app['routing.paths']);
            } else {
                $locator = new BaseFileLocator($app['routing.paths']);
            }
            $loader = new YamlFileLoader($locator);

            return $loader;
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

        // make sure the default route collection is attached to the router
        $app['routes'] = $app->share($app->extend('routes', function ($routes, $app) {
            $collection = $app['router']->getRouteCollection();
            $collection->addCollection($routes);

            return $collection;
        }));

        $app['routing.attribute_listener'] = $app->share(function ($app) {
            return new RouteAttributeListener($app['request_context'], $app['logger']);
        });

        $app['routing.methods_loader'] = $app->share(function ($app) {
            $cacheClass = implode('', array_map('ucfirst', explode('-', $app['routing.methods_cache_class'])));

            return new AllowedMethodsLoader($app['router'], $app['routing.cache_dir'], $cacheClass, $app['debug']);
        });

        $app['routing.methods_listener'] = $app->share(function ($app) {
            return new AllowedMethodsListener($app['routing.methods_loader']);
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['routing.attribute_listener']);
        $app['dispatcher']->addSubscriber($app['routing.methods_listener']);
    }
}
