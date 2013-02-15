<?php

namespace VGMdb\Component\Config;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads configuration from YAML or JSON files.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ConfigLoader extends Loader
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

    public function load($container, $type = null)
    {
        if (!is_array($container) && !$container instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('Container must be an array or an instance of ArrayAccess.');
        }

        $configs = $this->getConfig();

        $replacements = array();
        if (isset($this->options['parameters'])) {
            $parameters = (array) $this->options['parameters'];
            foreach ($parameters as $key => $value) {
                $replacements['%' . $key . '%'] = $value;
            }
        }

        $this->replaceConfig($container, $configs, $replacements);
    }

    public function getConfig()
    {
        $filenames = (array) $this->options['files'];
        $directories = (array) $this->options['base_dirs'];

        $conf = array();
        foreach ($directories as $directory) {
            foreach ($filenames as $filename) {
                $conf = array_merge($conf, $this->loadConfig($directory . '/' . $filename));
            }
        }

        $configs = array();
        foreach ($conf as $path => $config) {
            $configs = array_replace_recursive($configs, $config);
        }

        return $configs;
    }

    protected function loadConfig($filename)
    {
        $format = $this->getFileFormat($filename);

        if (!$filename || !$format) {
            throw new \InvalidArgumentException('A valid configuration file must be passed before reading the config.');
        }

        if (!in_array($format, array('yaml', 'json'))) {
            throw new \InvalidArgumentException(sprintf("The config file '%s' has invalid format '%s'.", $filename, $format));
        }

        if ('yaml' === $format && !class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }

        if (!file_exists($filename) && !file_exists($filename . '.dist')) {
            throw new FileNotFoundException($filename . '.dist');
        }

        $configs = array();

        foreach (array($filename . '.dist', $filename) as $file) {
            if (file_exists($file)) {
                $configs[$file] = 'yaml' === $format
                    ? Yaml::parse(file_get_contents($file))
                    : json_decode(file_get_contents($file), true);
            }
        }

        return $configs;
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
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return in_array($type ?: $this->getFileFormat($resource), array('yaml', 'json'));
    }
}
