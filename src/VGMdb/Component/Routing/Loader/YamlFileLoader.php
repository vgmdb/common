<?php

namespace VGMdb\Component\Routing\Loader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Loader\YamlFileLoader as BaseYamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Extends YamlFileLoader with the ability to replace route placeholders.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class YamlFileLoader extends BaseYamlFileLoader
{
    protected $replacements = array();

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
        $routes = $this->getRoutes((array) $files);

        return $this->getRouteCollection($routes);
    }

    public function getRoutes($files)
    {
        $routes = array();

        foreach ($files as $file) {
            $path = $this->locator->locate($file);
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
                $configs = $this->doReplacements($configs);
            }

            $routes[$path] = $configs;
        }

        return $routes;
    }

    public function getRouteCollection(array $routes)
    {
        $collection = new RouteCollection();

        foreach ($routes as $path => $configs) {
            $collection->addResource(new FileResource($path));

            foreach ($configs as $name => $config) {
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

    protected function doReplacements($value)
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
