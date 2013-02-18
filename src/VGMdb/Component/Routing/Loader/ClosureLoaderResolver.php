<?php

namespace VGMdb\Component\Routing\Loader;

use Symfony\Component\Config\Loader\LoaderResolverInterface;

/**
 * ClosureLoaderResolver resolves a loader using a Closure.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ClosureLoaderResolver implements LoaderResolverInterface
{
    protected $closure;

    /**
     * Constructor.
     *
     * @param \Closure $closure A closure that returns a loader.
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($resource, $type = null)
    {
        return call_user_func($this->closure);
    }
}
