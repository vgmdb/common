<?php

namespace VGMdb\Component\Routing\Loader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Loader\YamlFileLoader;

/**
 * CachedYamlFileLoader loads and caches Yaml routing configs.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CachedYamlFileLoader extends YamlFileLoader
{
    protected $cache;
    protected $replacements = array();

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
     * Sets the replacement parameters.
     *
     * @param array $replacements
     */
    public function setReplacements(array $replacements)
    {
        foreach ($replacements as $key => $value) {
            $this->replacements['%'.$key.'%'] = $value;
        }
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

                $configs = Yaml::parse(file_get_contents($path));
                // empty file
                if (null === $configs) {
                    $configs = array();
                }
                // not an array
                if (!is_array($configs)) {
                    throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $file));
                }

                if ($this->replacements) {
                    foreach ($configs as $name => $value) {
                        $configs[$name] = $this->doReplacements($value);
                    }
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
                if (isset($config['pattern'])) {
                    if (isset($config['path'])) {
                        throw new \InvalidArgumentException(sprintf('The file "%s" cannot define both a "path" and a "pattern" attribute. Use only "path".', $path));
                    }

                    $config['path'] = $config['pattern'];
                    unset($config['pattern']);
                }

                $this->validate($config, $name, $path);

                if (isset($config['resource'])) {
                    $this->parseImport($collection, $config, $path, $file);
                } else {
                    $this->parseRoute($collection, $name, $config, $path);
                }
            }
        }

        return $collection;
    }

    private function doReplacements($value)
    {
        if (!$this->replacements) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->doReplacements($val);
            }

            return $value;
        }

        if (is_string($value)) {
            return strtr($value, $this->replacements);
        }

        return $value;
    }
}
