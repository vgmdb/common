<?php

namespace VGMdb\Component\Routing\Loader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * CachedYamlFileLoader loads and caches Yaml routing configs.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CachedYamlFileLoader extends YamlFileLoader
{
    protected static $availableKeys = array(
        'type', 'resource', 'prefix', 'pattern', 'options', 'defaults', 'requirements'
    );
    protected $cache;

    /**
     * Sets the configuration cache.
     *
     * @param ConfigCache $cache
     */
    public function setCache(ConfigCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Loads an array of Yaml files.
     *
     * @param array  $file A Yaml file or array of files
     * @param string $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When route can't be parsed
     *
     * @api
     */
    public function load($files, $type = null)
    {
        $collection = new RouteCollection();
        $routes = array();

        if (!is_array($files)) {
            $files = array($files);
        }

        if (!$this->cache->isFresh()) {
            foreach ($files as $file) {
                $path = $this->locator->locate($file);
                $collection->addResource(new FileResource($path));

                $configs = Yaml::parse($path);
                // empty file
                if (null === $configs) {
                    $configs = array();
                }
                // not an array
                if (!is_array($configs)) {
                    throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $file));
                }

                $routes[$path] = $configs;
            }

            $this->cache->write(
                '<?php' . PHP_EOL . '$routes = ' . var_export($routes, true) . ';',
                $collection->getResources()
            );
        }

        require_once $this->cache;

        foreach ($routes as $path => $configs) {
            foreach ($configs as $name => $config) {
                foreach ($config as $key => $value) {
                    if (!in_array($key, self::$availableKeys)) {
                        throw new \InvalidArgumentException(sprintf(
                            'Yaml routing loader does not support given key: "%s". Expected one of the (%s).',
                            $key, implode(', ', self::$availableKeys)
                        ));
                    }
                }

                if (isset($config['resource'])) {
                    $type = isset($config['type']) ? $config['type'] : null;
                    $prefix = isset($config['prefix']) ? $config['prefix'] : null;
                    $defaults = isset($config['defaults']) ? $config['defaults'] : array();
                    $requirements = isset($config['requirements']) ? $config['requirements'] : array();
                    $options = isset($config['options']) ? $config['options'] : array();

                    $this->setCurrentDir(dirname($path));
                    $collection->addCollection($this->import($config['resource'], $type, false, $file), $prefix, $defaults, $requirements, $options);
                } else {
                    $this->parseRoute($collection, $name, $config, $path);
                }
            }
        }

        return $collection;
    }
}
