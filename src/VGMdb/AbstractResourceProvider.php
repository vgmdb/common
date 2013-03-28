<?php

namespace VGMdb;

/**
 * Base implementation for resource providers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractResourceProvider
{
    protected $name;
    protected $reflected;

    /**
     * Builds the resource.
     *
     * It is only ever called once when the cache is empty.
     */
    public function build()
    {
    }

    /**
     * Returns the resource name (the namespace segment).
     *
     * @return string The resource name
     */
    final public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $name = $this->getNamespace();
        $pos = strrpos($name, '\\');

        return $this->name = false === $pos ? $name :  substr($name, $pos + 1);
    }

    /**
     * Gets the resource namespace.
     *
     * @return string The resource namespace
     */
    public function getNamespace()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return $this->reflected->getNamespaceName();
    }

    /**
     * Gets the resource directory path.
     *
     * @return string The resource absolute path
     */
    public function getPath()
    {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }

        return dirname($this->reflected->getFileName());
    }
}
