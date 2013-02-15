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

    /**
     * Constructor.
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        if ($this->loader instanceof WarmableInterface) {
            $this->loader->warmUp($cacheDir);
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
