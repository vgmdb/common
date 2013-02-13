<?php

namespace VGMdb\Component\Config;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads configuration from YAML or JSON files.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ConfigLoader implements WarmableInterface
{
    protected $options = array();

    /**
     * Constructor.
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    public function setOptions(array $options = array())
    {
        $this->options = array_replace($this->options, $options);
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function load($container)
    {
        if (!is_array($container) && !$container instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('Container must be an array or an instance of ArrayAccess.');
        }

        foreach (array('config.files', 'config.base_dirs', 'config.cache_dir', 'config.cache_class', 'config.debug') as $key) {
            if (!array_key_exists($key, $this->options)) {
                throw new \RuntimeException(sprintf('ConfigLoader parameter missing: "%s"', $key));
            }
            $container[$key] = $this->options[$key];
        }

        $configs = $this->getConfig();

        $replacements = array();
        if (isset($this->options['config.parameters'])) {
            $parameters = (array) $this->options['config.parameters'];
            foreach ($parameters as $key => $value) {
                $replacements['%' . $key . '%'] = $value;
            }
        }

        $this->replaceConfig($container, $configs, $replacements);
    }

    public function getConfig()
    {
        $filenames = (array) $this->options['config.files'];
        $directories = (array) $this->options['config.base_dirs'];

        $cacheClass = implode('', array_map('ucfirst', explode('-', $this->options['config.cache_class'])));
        $cacheFile = $this->options['config.cache_dir'] . '/' . $cacheClass . '.php';
        $cache = new ConfigCache($cacheFile, $this->options['config.debug']);

        if (!$cache->isFresh()) {
            $configs = $resources = array();
            foreach ($directories as $directory) {
                foreach ($filenames as $filename) {
                    list($config, $resource) = $this->loadFile($directory . '/' . $filename);
                    $configs = array_replace_recursive($configs, $config);
                    $resources = array_merge($resources, $resource);
                }
            }

            $cache->write(
                '<?php' . PHP_EOL . '$configs = ' . var_export($configs, true) . ';',
                $resources
            );
        }

        require_once $cache;

        return $configs;
    }

    protected function loadFile($filename)
    {
        $format = $this->getFileFormat($filename);

        if (!$filename || !$format) {
            throw new \RuntimeException('A valid configuration file must be passed before reading the config.');
        }

        if (!file_exists($filename) && !file_exists($filename . '.dist')) {
            throw new FileNotFoundException($filename . '.dist');
        }

        $config = $resources = array();

        if ('yaml' === $format) {
            if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
                throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
            }
            if (file_exists($filename . '.dist')) {
                $resources[] = new FileResource($filename . '.dist');
                $config = array_replace_recursive($config, Yaml::parse(file_get_contents($filename . '.dist')));
            }
            if (file_exists($filename)) {
                $resources[] = new FileResource($filename);
                $config = array_replace_recursive($config, Yaml::parse(file_get_contents($filename)));
            }
        } elseif ('json' === $format) {
            if (file_exists($filename . '.dist')) {
                $resources[] = new FileResource($filename . '.dist');
                $config = array_replace_recursive($config, json_decode(file_get_contents($filename . '.dist'), true));
            }
            if (file_exists($filename)) {
                $resources[] = new FileResource($filename);
                $config = array_replace_recursive($config, json_decode(file_get_contents($filename), true));
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf("The config file '%s' appears has invalid format '%s'.", $filename, $format)
            );
        }

        return array($config, $resources);
    }

    protected function replaceConfig($container, array $config, array $replacements)
    {
        foreach ($config as $name => $value) {
            if (!isset($container[$name]) || !is_array($value)) {
                $container[$name] = $this->doReplacements($value, $replacements);
            } else {
                $container[$name] = $this->replaceConfig($container[$name], $config[$name], $replacements);
            }
        }

        return $container;
    }

    protected function doReplacements($value, array $replacements)
    {
        if (!$replacements) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->doReplacements($v, $replacements);
            }

            return $value;
        }

        if (is_string($value)) {
            return strtr($value, $this->replacements);
        }

        return $value;
    }

    protected function getFileFormat($filename)
    {
        if (preg_match('#.ya?ml(.dist)?$#i', $filename)) {
            return 'yaml';
        }

        if (preg_match('#.json(.dist)?$#i', $filename)) {
            return 'json';
        }

        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $this->setOption('config.cache_dir', $cacheDir);

        $this->getConfig();
    }
}
