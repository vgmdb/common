<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides YAML or JSON configuration.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    private $filename;
    private $cache;
    private $replacements = array();

    public function register(Application $app)
    {
        $cacheFile = $app['config.cache_dir'] . '/' . basename($this->filename) . '.php';
        $this->cache = new ConfigCache($cacheFile, $app['debug']);

        $config = $this->readConfig();

        foreach ($config as $name => $value) {
            $app[$name] = $this->doReplacements($value);
        }
    }

    public function boot(Application $app)
    {
    }

    public function __construct($filename, array $replacements = array())
    {
        $this->filename = $filename;

        if ($replacements) {
            foreach ($replacements as $key => $value) {
                $this->replacements['%'.$key.'%'] = $value;
            }
        }
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

    private function readConfig()
    {
        $format = $this->getFileFormat();

        if (!$this->filename || !$format) {
            throw new \RuntimeException('A valid configuration file must be passed before reading the config.');
        }

        if (!$this->cache->isFresh()) {
            if (!file_exists($this->filename)) {
                if (!file_exists($this->filename . '.dist')) {
                    throw new FileNotFoundException($this->filename . '.dist');
                }

                if (!@rename($this->filename . '.dist', $this->filename)) {
                    $error = error_get_last();
                    throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->filename . '.dist', $this->filename, str_replace(',', ', ', strip_tags($error['message']))));
                }
            }

            if ('yaml' === $format) {
                if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
                    throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
                }
                $config = Yaml::parse($this->filename);
            } elseif ('json' === $format) {
                $config = json_decode(file_get_contents($this->filename), true);
            } else {
                throw new \InvalidArgumentException(
                    sprintf("The config file '%s' appears has invalid format '%s'.", $this->filename, $format)
                );
            }

            if (!$config) {
                $config = array();
            }

            $this->cache->write(
                '<?php' . PHP_EOL . '$config = ' . var_export($config, true) . ';',
                array(new FileResource($this->filename))
            );
        }

        require_once $this->cache;

        return $config;
    }

    public function getFileFormat()
    {
        $filename = $this->filename;

        if (preg_match('#.ya?ml(.dist)?$#i', $filename)) {
            return 'yaml';
        }

        if (preg_match('#.json(.dist)?$#i', $filename)) {
            return 'json';
        }

        return pathinfo($filename, PATHINFO_EXTENSION);
    }
}
