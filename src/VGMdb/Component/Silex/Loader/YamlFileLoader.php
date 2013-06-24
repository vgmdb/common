<?php

namespace VGMdb\Component\Silex\Loader;

use VGMdb\Component\Silex\ResourceProviderInterface;
use Silex\Application;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;

/**
 * YamlFileLoader loads YAML files service definitions.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class YamlFileLoader extends FileLoader
{
    /**
     * Loads and parses YAML configuration.
     *
     * @param mixed  $file The resource
     * @param string $type The resource type
     *
     * @return array
     */
    public function load($file, $type = null)
    {
        try {
            $configs = $this->loadConfig($file, $type);
        } catch (\Exception $e) {
            return null;
        }

        unset($configs['imports']);

        $this->parseParameters($configs);
        unset($configs['parameters']);

        $configs = $this->doReplacements($configs, $this->replacements);
        $configs = $this->process($configs);
        $configs = $this->loadFromExtensions($configs);

        // replace placeholders from extensions
        $configs = $this->doReplacements($configs, $this->replacements);

        return $configs;
    }

    /**
     * Loads a YAML file and its imports.
     *
     * @param mixed  $file The resource
     * @param string $type The resource type
     *
     * @return array
     */
    public function loadConfig($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $content = $this->loadFile($path);

        $this->resources[] = new FileResource($path);

        // empty file
        if (null === $content) {
            return;
        }

        // imports
        $configs = (array) $this->parseImports($content, $file);

        $ret = array();
        $configs[] = $content;
        foreach ($configs as $config) {
            $ret = array_replace_recursive($ret, (array) $config);
        }

        return $ret;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return true;
    }

    /**
     * Loads the config into the container.
     *
     * @param array $content
     */
    public function apply($content = array())
    {
        // services
        $this->parseDefinitions($content);

        unset($content['imports']);
        unset($content['parameters']);
        unset($content['services']);

        $this->replaceConfig($this->container, $content, $this->replacements);
    }

    /**
     * Parses all imports
     *
     * @param array  $content
     * @param string $file
     */
    protected function parseImports($content, $file)
    {
        if (!isset($content['imports'])) {
            return;
        }

        $ret = array();
        foreach ($content['imports'] as $import) {
            $resource = $this->doReplacements($import['resource'], $this->replacements);
            //$this->setCurrentDir(dirname($file));
            $ret[] = $this->import($resource, null, isset($import['ignore_errors']) ? (Boolean) $import['ignore_errors'] : false, $file);
        }

        return $ret;
    }

    /**
     * Parses parameters
     *
     * @param array $content
     */
    protected function parseParameters($content)
    {
        if (!isset($content['parameters'])) {
            return;
        }

        foreach ($content['parameters'] as $key => $value) {
            $this->options['parameters'][$key] = $value;
        }

        foreach ($this->options['parameters'] as $key => $value) {
            $this->replacements['%' . $key . '%'] = $value;
        }
    }

    /**
     * Loads from Extensions
     *
     * @param array $content
     *
     * @return array
     */
    protected function loadFromExtensions($content)
    {
        if (!isset($content['services'])) {
            return $content;
        }

        foreach ($content['services'] as $id => $service) {
            $config = (array) $this->loadFromExtension($service);
            $content = array_replace_recursive($config, $content);
        }

        return $content;
    }

    /**
     * Loads from an Extension
     *
     * @param array $service
     *
     * @return array
     */
    protected function loadFromExtension($service)
    {
        if (!isset($service['provider'])) {
            return;
        }

        $class = $service['provider'];
        $config = array();
        $provider = new $class();

        if ($provider instanceof ResourceProviderInterface) {
            $config = $provider->load($this->container);
        }

        return $config;
    }

    /**
     * Parses definitions
     *
     * @param array $content
     */
    protected function parseDefinitions($content)
    {
        if (!isset($content['services'])) {
            return;
        }

        foreach ($content['services'] as $id => $service) {
            $this->parseDefinition($id, $service);
        }
    }

    /**
     * Parses a definition.
     *
     * @param string $id
     * @param array  $service
     *
     * @throws InvalidArgumentException When tags are invalid
     */
    protected function parseDefinition($id, $service)
    {
        $class = null;

        if (isset($service['provider'])) {
            $class = $service['provider'];
            unset($service['provider']);
        }

        $parameters = array();
        foreach ($service as $key => $value) {
            $parameters[$id . '.' . $key] = $this->doReplacements($value, $this->replacements);
        }

        if (null !== $class && $this->container instanceof Application) {
            $this->container->register(new $class(), $parameters);
        } else {
            $this->replaceConfig($this->container, $parameters, array());
        }
    }

    /**
     * Loads a YAML file.
     *
     * @param string $file
     *
     * @return array The file content
     */
    protected function loadFile($file)
    {
        return $this->validate(Yaml::parse($file), $file);
    }

    /**
     * Validates a YAML file.
     *
     * @param mixed  $content
     * @param string $file
     *
     * @return array
     *
     * @throws InvalidArgumentException When service file is not valid
     */
    private function validate($content, $file)
    {
        if (null === $content) {
            return $content;
        }

        if (!is_array($content)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
        }

        return $content;
    }
}
