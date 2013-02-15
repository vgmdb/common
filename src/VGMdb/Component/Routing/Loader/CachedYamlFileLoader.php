<?php

namespace VGMdb\Component\Routing\Loader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * CachedYamlFileLoader loads and caches Yaml routing configs.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CachedYamlFileLoader implements LoaderInterface, WarmableInterface
{
    protected $loader;
    protected $options;

    public function __construct(YamlFileLoader $loader, array $options)
    {
        $this->loader = $loader;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function load($files, $type = null)
    {
        $this->options['files'] = $files;

        $routes = $this->getRoutes();

        return $this->loader->getRouteCollection($routes);
    }

    public function getRoutes()
    {
        $class = implode('', array_map('ucfirst', explode('-', $this->options['cache_class'])));
        $cache = new ConfigCache($this->options['cache_dir'] . '/' . $class . '.php', $this->options['debug']);

        if (!$cache->isFresh()) {
            $routes = $this->loader->getRoutes($this->options['files']);

            $resources = array();
            $paths = array_keys($routes);
            foreach ($paths as $path) {
                $resources[] = new FileResource($path);
            }

            $cache->write(
                '<?php' . PHP_EOL . '$routes = ' . var_export($routes, true) . ';',
                $resources
            );
        }

        require_once $cache;

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return $this->loader->supports($resource, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getResolver()
    {
        return $this->loader->getResolver();
    }

    /**
     * {@inheritdoc}
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        $this->loader->setResolver($resolver);
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $this->options['cache_dir'] = $cacheDir;

        $this->getRoutes();
    }
}
