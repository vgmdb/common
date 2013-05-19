<?php

namespace VGMdb\Component\Silex\Loader;

use Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;

/**
 * FileLoader is the abstract class used by all built-in loaders that are file based.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class FileLoader extends BaseFileLoader
{
    protected $currentDir;
    protected $container;
    protected $options = array();
    protected $replacements = array();

    /**
     * Constructor.
     *
     * @param ArrayAccess|array    $container An array or object implementing ArrayAccess
     * @param FileLocatorInterface $locator   A FileLocator instance
     * @param array                $options   Array of options
     */
    public function __construct($container, FileLocatorInterface $locator, array $options = array())
    {
        $this->container = $container;
        $this->options = $options;

        foreach ($this->options['parameters'] as $key => $value) {
            $this->replacements['%' . $key . '%'] = $value;
        }

        parent::__construct($locator);
    }

    /**
     * {@inheritdoc}
     */
    public function import($resource, $type = null, $ignoreErrors = false, $sourceResource = null)
    {
        try {
            $loader = $this->resolve($resource, $type);

            if ($loader instanceof FileLoader && null !== $this->currentDir) {
                $resource = $this->locator->locate($resource, $this->currentDir);
            }

            if (isset(self::$loading[$resource])) {
                throw new FileLoaderImportCircularReferenceException(array_keys(self::$loading));
            }
            self::$loading[$resource] = true;

            $ret = $loader->loadConfig($resource, $type);

            unset(self::$loading[$resource]);

            return $ret;
        } catch (FileLoaderImportCircularReferenceException $e) {
            throw $e;
        } catch (\Exception $e) {
            if (!$ignoreErrors) {
                // prevent embedded imports from nesting multiple exceptions
                if ($e instanceof FileLoaderLoadException) {
                    throw $e;
                }

                throw new FileLoaderLoadException($resource, $sourceResource, null, $e);
            }
        }
    }

    public function setOptions(array $options = array())
    {
        $this->options = array_replace($this->options, $options);
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
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
            return strtr($value, $replacements);
        }

        return $value;
    }
}
