<?php

namespace VGMdb\Component\Config;

use VGMdb\Component\Silex\ResourceLocatorInterface;
use Symfony\Component\Config\FileLocator as BaseFileLocator;

/**
 * FileLocator uses a ResourceLocator to locate resources.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class FileLocator extends BaseFileLocator
{
    private $locator;
    private $path;

    /**
     * Constructor.
     *
     * @param ResourceLocatorInterface $locator A ResourceLocatorInterface instance
     * @param string                   $path    The path the global resource directory
     * @param string|array             $paths   A path or an array of paths where to look for resources
     */
    public function __construct(ResourceLocatorInterface $locator, $path = null, array $paths = array())
    {
        $this->locator = $locator;
        $this->path = $path;
        $paths[] = $path;

        parent::__construct($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null, $first = true)
    {
        if ('@' === $file[0]) {
            return $this->locator->locateResource($file, $this->path, $first);
        }

        return parent::locate($file, $currentPath, $first);
    }
}
