<?php

namespace VGMdb\Component\Mustache\Loader;

use VGMdb\Component\Silex\ResourceLocatorInterface;

/**
 * Loader implementation which supports resource locators.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class FilesystemLoader extends \Mustache_Loader_FilesystemLoader
{
    protected $locator;

    /**
     * Constructor.
     *
     * @param ResourceLocatorInterface $locator Resource locator instance.
     * @param string                   $baseDir Base directory containing Mustache template files.
     * @param array                    $options Array of Loader options (default: array())
     */
    public function __construct(ResourceLocatorInterface $locator, $baseDir, array $options = array())
    {
        $this->locator = $locator;

        parent::__construct($baseDir, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFileName($name)
    {
        if ('@' === $name[0]) {
            $name = implode('/Resources/views/', explode('/', $name, 2));
            if (substr($name, 0 - strlen($this->extension)) !== $this->extension) {
                $name .= $this->extension;
            }

            return $this->locator->locateResource($name);
        }

        return parent::getFileName($name);
    }
}
