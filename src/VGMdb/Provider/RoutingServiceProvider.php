<?php

namespace VGMdb\Provider;

use VGMdb\Component\Routing\Loader\CachedYamlFileLoader;
use VGMdb\Component\Routing\Matcher\RedirectableUrlMatcher;
use VGMdb\Component\Routing\Matcher\RedirectableProxyUrlMatcher;
use VGMdb\Component\HttpKernel\EventListener\RouteAttributeListener;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;

/**
 * Provides caching for routes loaded from YAML configuration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RoutingServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['routing.matcher_cache_class'] = 'ProjectUrlMatcher';
        $app['routing.loader_cache_class'] = 'ProjectRouteLoader';
        $app['routing.parameters'] = array();

        // replace the default url matcher with one that caches compiled routes
        $app['url_matcher'] = $app->share(function ($app) {
            if (!isset($app['routing.cache_dir']) || !isset($app['routing.matcher_cache_class'])) {
                return new RedirectableUrlMatcher($app['routes'], $app['request_context']);
            }

            $class = $app['routing.matcher_cache_class'];
            $cache = new ConfigCache($app['routing.cache_dir'] . '/' . $class . '.php', $app['debug']);
            if (!$cache->isFresh()) {
                $dumper = new PhpMatcherDumper($app['routes']);

                $options = array(
                    'class'      => $class,
                    'base_class' => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher'
                );

                $cache->write($dumper->dump($options), $app['routes']->getResources());
            }

            require_once $cache;

            return new RedirectableProxyUrlMatcher(new $class($app['request_context']));
        });

        $app['routing.attribute_listener'] = $app->share(function ($app) {
            return new RouteAttributeListener($app['request_context'], $app['logger']);
        });

        $app['routes'] = $app->share($app->extend('routes', function ($routes, $app) {
            $collection = new RouteCollection();
            $paths = array();

            if (substr($app['routing.config_dir'], -4) === '.yml') {
                $paths[] = $app['routing.config_dir'];
            } else {
                $paths = glob($app['routing.config_dir'] . '/*.yml');
            }

            $class = $app['routing.loader_cache_class'];
            $cache = new ConfigCache($app['routing.cache_dir'] . '/' . $class . '.php', $app['debug']);
            $locator = new FileLocator($paths);
            $loader = new CachedYamlFileLoader($locator);
            $loader->setCache($cache);
            $loader->setReplacements($app['routing.parameters']);
            $collection->addCollection($loader->load($paths));
            $routes->addCollection($collection);

            return $routes;
        }));
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['routing.attribute_listener']);
    }
}
