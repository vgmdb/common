<?php

namespace VGMdb\Component\Silex\Loader;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;

/**
 * YamlFileLoader loads YAML files service definitions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class YamlFileLoader extends FileLoader
{
    protected $replacements = array();

    /**
     * Loads a Yaml file.
     *
     * @param mixed  $file The resource
     * @param string $type The resource type
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $content = $this->loadFile($path);

        //$this->container->addResource(new FileResource($path));

        // empty file
        if (null === $content) {
            return;
        }

        // imports
        $this->parseImports($content, $file);

        // parameters
        $this->parseParameters($content);

        // services
        $this->parseDefinitions($content, $file);

        // extensions
        $this->loadFromExtensions($content);
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
     * Parses all imports
     *
     * @param array  $content
     * @param string $file
     */
    private function parseImports($content, $file)
    {
        if (!isset($content['imports'])) {
            return;
        }

        foreach ($content['imports'] as $import) {
            $resource = $this->doReplacements($import['resource'], $this->replacements);
            //$this->setCurrentDir(dirname($file));
            $this->import($resource, null, isset($import['ignore_errors']) ? (Boolean) $import['ignore_errors'] : false, $file);
        }
    }

    /**
     * Parses parameters
     *
     * @param array $content
     */
    private function parseParameters($content)
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
     * Parses definitions
     *
     * @param array  $content
     * @param string $file
     */
    private function parseDefinitions($content, $file)
    {
        if (!isset($content['services'])) {
            return;
        }

        foreach ($content['services'] as $id => $service) {
            $this->parseDefinition($id, $service, $file);
        }
    }

    /**
     * Parses a definition.
     *
     * @param string $id
     * @param array  $service
     * @param string $file
     *
     * @throws InvalidArgumentException When tags are invalid
     */
    private function parseDefinition($id, $service, $file)
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

        if (null !== $class) {
            $this->app->register(new $class(), $parameters);
        } else {
            $this->replaceConfig($this->app, $parameters, array());
        }
    }

    /**
     * Loads from Extensions
     *
     * @param array $content
     */
    private function loadFromExtensions($content)
    {
        unset($content['imports']);
        unset($content['parameters']);
        unset($content['services']);

        $this->replaceConfig($this->app, $content, $this->replacements);
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
