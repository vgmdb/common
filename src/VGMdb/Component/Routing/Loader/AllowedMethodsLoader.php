<?php

/*
 * This file was originally part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */

namespace VGMdb\Component\Routing\Loader;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * AllowedMethodsRouterLoader implementation using RouterInterface to fetch
 * allowed http methods
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
class AllowedMethodsLoader implements AllowedMethodsLoaderInterface, CacheWarmerInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ConfigCache
     */
    private $cache;

    /**
     * Constructor
     *
     * @param RouterInterface $router
     * @param string          $cacheDir
     * @param string          $cacheClass
     * @param boolean         $isDebug Kernel debug flag
     */
    public function __construct(RouterInterface $router, $cacheDir, $cacheClass, $isDebug)
    {
        $this->router = $router;
        $this->cache  = new ConfigCache(sprintf('%s/%s.php', $cacheDir, $cacheClass), $isDebug);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        if (!$this->cache->isFresh()) {
            $this->warmUp(null);
        }

        return require $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $processedRoutes = array();

        $routeCollection = $this->router->getRouteCollection();

        foreach ($routeCollection->all() as $name => $route) {

            if (!isset($processedRoutes[$route->getPattern()])) {
                $processedRoutes[$route->getPattern()] = array(
                    'methods' => array(),
                    'names'   => array(),
                );
            }

            $processedRoutes[$route->getPattern()]['names'][] = $name;

            $requirements = $route->getRequirements();
            if (isset($requirements['_method'])) {
                $methods = explode('|', $requirements['_method']);
                array_push($methods, 'HEAD');
                $processedRoutes[$route->getPattern()]['methods'] = array_merge(
                    $processedRoutes[$route->getPattern()]['methods'],
                    $methods
                );
            }
        }

        $allowedMethods = array();

        foreach ($processedRoutes as $processedRoute) {
            if (count($processedRoute['methods']) > 0) {
                foreach ($processedRoute['names'] as $name) {
                    $allowedMethods[$name] = array_unique($processedRoute['methods']);
                }
            }
        }

        $this->cache->write(
            sprintf('<?php return %s;', var_export($allowedMethods, true)),
            $routeCollection->getResources()
        );
    }
}
