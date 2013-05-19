<?php

/*
 * This code was originally part of the Symfony2 FrameworkBundle.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */

namespace VGMdb\Component\Routing\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Generates the router matcher and generator classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RouterCacheWarmer implements CacheWarmerInterface
{
    protected $router;
    protected $subDir;

    /**
     * Constructor.
     *
     * @param RouterInterface $router A Router instance
     * @param string          $subDir Subdirectory
     */
    public function __construct(RouterInterface $router, $subDir = null)
    {
        $this->router = $router;
        $this->subDir = $subDir;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        if ($this->router instanceof WarmableInterface) {
            $this->router->warmUp($cacheDir . '/' . $this->subDir);
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
