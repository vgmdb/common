<?php

namespace VGMdb\Component\Routing;

use VGMdb\Component\Routing\Loader\YamlFileLoader;
use VGMdb\Component\Routing\Loader\CachedYamlFileLoader;
use VGMdb\Component\Routing\Loader\ClosureLoaderResolver;
use VGMdb\Component\Routing\Translation\TranslationRouter;
use VGMdb\Component\Routing\Translation\RouteExclusionStrategy;
use VGMdb\Component\Routing\Translation\PathGenerationStrategy;
use VGMdb\Component\Routing\Translation\LocaleResolver;
use VGMdb\Component\Routing\Translation\TranslationRouteLoader;
use VGMdb\Component\Routing\Translation\Extractor\YamlRouteExtractor;
use VGMdb\Component\HttpKernel\EventListener\RouteAttributeListener;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;

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
        $app['routing.translation.strategy'] = 'prefix_except_default';
        $app['routing.translation.domain'] = 'routes';

        $app['router'] = $app->share(function ($app) {
            $routerClass = isset($app['translator'])
                ? 'VGMdb\\Component\\Routing\\Translation\\TranslationRouter'
                : 'VGMdb\\Component\\Routing\\LazyRouter';
            $generatorClass = implode('', array_map('ucfirst', explode('-', $app['routing.generator_cache_class'])));
            $matcherClass = implode('', array_map('ucfirst', explode('-', $app['routing.matcher_cache_class'])));

            $router = new $routerClass(
                $app['routing.delegating_loader'],
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

            if ($router instanceof TranslationRouter) {
                $router->setLocaleResolver($app['routing.translation.locale_resolver']);
                $router->setTranslationLoader($app['routing.translation.loader']);
                $router->setDefaultLocale($app['routing.translation.locale']);
            }

            return $router;
        });

        $app['routing.loader'] = $app->share(function ($app) {
            $locator = new FileLocator($app['routing.resource']);
            $loader = new YamlFileLoader($locator);
            $loader->setReplacements($app['routing.parameters']);

            return $loader;
        });

        $app['routing.cached_loader'] = $app->share(function ($app) {
            $loader = new CachedYamlFileLoader($app['routing.loader'], array(
                'debug' => $app['debug'],
                'cache_class' => $app['routing.loader_cache_class'],
                'cache_dir' => $app['routing.cache_dir'],
                'files' => $app['routing.resource']
            ));

            return $loader;
        });

        $app['routing.delegating_loader'] = $app->share(function ($app) {
            return new DelegatingLoader(
                new ClosureLoaderResolver(function () use ($app) {
                    if ($app['cache']) {
                        return $app['routing.cached_loader'];
                    } else {
                        return $app['routing.loader'];
                    }
                })
            );
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

        $app['routing.translation.extractor'] = $app->share(function ($app) {
            return new YamlRouteExtractor($app['router'], $app['routing.translation.exclusion_strategy']);
        });

        $app['routing.translation.exclusion_strategy'] = $app->share(function ($app) {
            return new RouteExclusionStrategy();
        });

        $app['routing.translation.path_generation_strategy'] = $app->share(function ($app) {
            return new PathGenerationStrategy(
                $app['routing.translation.strategy'],
                $app['translator'],
                $app['routing.translation.locales'],
                $app['routing.translation.cache_dir'],
                $app['routing.translation.domain'],
                $app['routing.translation.locale']
            );
        });

        $app['routing.translation.locale_resolver'] = $app->share(function ($app) {
            return new LocaleResolver();
        });

        $app['routing.translation.loader'] = $app->share(function ($app) {
            return new TranslationRouteLoader(
                $app['routing.translation.exclusion_strategy'],
                $app['routing.translation.path_generation_strategy']
            );
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
