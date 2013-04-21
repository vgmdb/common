<?php

namespace VGMdb\Component\Config\CacheWarmer;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * Generates the configuration cache classes.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ConfigCacheWarmer implements CacheWarmerInterface
{
    protected $loader;
    protected $subDir;

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader A Loader instance
     * @param string          $subDir Subdirectory
     */
    public function __construct(LoaderInterface $loader, $subDir = null)
    {
        $this->loader = $loader;
        $this->subDir = $subDir;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        if ($this->loader instanceof WarmableInterface) {
            $this->loader->warmUp($cacheDir . '/' . $this->subDir);
        }
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * @return Boolean always true
     */
    public function isOptional()
    {
        return true;
    }
}
