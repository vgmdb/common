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
    private $filenames;
    private $cache;
    private $replacements = array();

    public function register(Application $app)
    {
        foreach ($this->filenames as $filename) {
            if (!$filename) {
                continue;
            }

            $id = hash('md4', $filename);
            $cacheFile = $app['config.cache_dir'] . '/' . $id . '.php';
            $this->cache[$id] = new ConfigCache($cacheFile, $app['debug']);

            $config = $this->readConfig($filename);

            $this->replaceConfig($app, $config);
        }
    }

    public function boot(Application $app)
    {
    }

    public function __construct($filenames, array $replacements = array())
    {
        if (!is_array($filenames)) {
            $filenames = array($filenames);
        }

        $this->filenames = $filenames;

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

    private function readConfig($filename)
    {
        $format = $this->getFileFormat($filename);
        $id = hash('md4', $filename);

        if (!$filename || !$format) {
            throw new \RuntimeException('A valid configuration file must be passed before reading the config.');
        }

        if (!$this->cache[$id]->isFresh()) {
            if (!file_exists($filename)) {
                if (!file_exists($filename . '.dist')) {
                    throw new FileNotFoundException($filename . '.dist');
                }

                try {
                    if (false === @copy($filename . '.dist', $filename)) {
                        throw new \ErrorException('Copy operation failed.');
                    }
                } catch (\Exception $error) {
                    throw new FileException(sprintf('Could not copy the file "%s" to "%s".', $filename . '.dist', $filename), 0, $error);
                }
            }

            if ('yaml' === $format) {
                if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
                    throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
                }
                $config = Yaml::parse(file_get_contents($filename));
            } elseif ('json' === $format) {
                $config = json_decode(file_get_contents($filename), true);
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
                array(new FileResource($filename))
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
}
