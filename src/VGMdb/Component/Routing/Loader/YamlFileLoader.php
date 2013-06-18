<?php

namespace VGMdb\Component\Routing\Loader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Loader\YamlFileLoader as BaseYamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Extends YamlFileLoader with the ability to glob directories.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class YamlFileLoader extends BaseYamlFileLoader
{
    /**
     * Loads an array of Yaml files.
     *
     * @param array  $file A Yaml file or array of files
     * @param string $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When route can't be parsed
     */
    public function load($files, $type = null)
    {
        if (!is_array($files) && false !== strpos($files, '*')) {
            $files = glob($files);
        }

        $collection = new RouteCollection();

        foreach ((array) $files as $file) {
            $collection->addCollection(parent::load($file, $type));
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseRoute(RouteCollection $collection, $name, array $config, $path)
    {
        if (isset($config['requirements']) && isset($config['requirements']['_method'])) {
            $config['requirements']['_method'] .= '|OPTIONS';
        } else {
            $config['requirements']['_method'] = 'GET|POST|OPTIONS';
        }

        parent::parseRoute($collection, $name, $config, $path);
    }
}
