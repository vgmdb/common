<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides YAML or JSON configuration.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    private $cache;
    private $options = array();
    private $replacements = array();

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function register(Application $app)
    {
        foreach (array('config.files', 'config.base_dir', 'config.cache_dir') as $key) {
            if (!array_key_exists($key, $this->options)) {
                throw new \RuntimeException(sprintf('Config service parameter missing: "%s"', $key));
            }
            $app[$key] = $this->options[$key];
        }

        $filenames = $this->options['config.files'];
        $base_dir = $this->options['config.base_dir'];
        $cache_dir = $this->options['config.cache_dir'];

        if (isset($this->options['config.parameters'])) {
            $replacements = $this->options['config.parameters'];
            if (is_array($replacements)) {
                foreach ($replacements as $key => $value) {
                    $this->replacements['%'.$key.'%'] = $value;
                }
            }
        }

        if (!is_array($filenames)) {
            $filenames = array($filenames);
        }

        foreach ($filenames as $filename) {
            if (!$filename) {
                continue;
            }

            $id = $this->getFileHash($filename);
            $cacheFile = $cache_dir . '/' . $id . '.php';
            $this->cache[$id] = new ConfigCache($cacheFile, $app['debug']);

            $config = $this->readConfig($base_dir . '/' . $filename, $id);

            $this->replaceConfig($app, $config);
        }
    }

    public function boot(Application $app)
    {
    }

    private function doReplacements($value)
    {
        if (!$this->replacements) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->doReplacements($v);
            }

            return $value;
        }

        if (is_string($value)) {
            return strtr($value, $this->replacements);
        }

        return $value;
    }

    private function readConfig($filename, $id)
    {
        $format = $this->getFileFormat($filename);

        if (!$filename || !$format) {
            throw new \RuntimeException('A valid configuration file must be passed before reading the config.');
        }

        if (!$this->cache[$id]->isFresh()) {
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

            if (!$config) {
                $config = array();
            }

            $this->cache[$id]->write(
                '<?php' . PHP_EOL . '$config = ' . var_export($config, true) . ';',
                $resources
            );
        }

        require_once $this->cache[$id];

        return $config;
    }

    private function replaceConfig($app, array $config = array())
    {
        foreach ($config as $name => $value) {
            if (!isset($app[$name])) {
                $app[$name] = $this->doReplacements($value);
            } elseif (is_array($value)) {
                $app[$name] = $this->replaceConfig($app[$name], $config[$name]);
            } else {
                $app[$name] = $this->doReplacements($value);
            }
        }

        return $app;
    }

    public function getFileFormat($filename)
    {
        if (preg_match('#.ya?ml(.dist)?$#i', $filename)) {
            return 'yaml';
        }

        if (preg_match('#.json(.dist)?$#i', $filename)) {
            return 'json';
        }

        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    public function getFileHash($filename)
    {
        return str_replace('/', '-', $filename);
    }
}
