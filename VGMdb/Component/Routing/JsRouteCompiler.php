<?php

namespace VGMdb\Component\Routing;

use VGMdb\Component\Routing\CompiledJsRoute;
use Symfony\Component\Routing\RouteCompilerInterface;
use Symfony\Component\Routing\Route;

/**
 * JsRouteCompiler compiles Route instances to CompiledJsRoute instances.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class JsRouteCompiler implements RouteCompilerInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws \LogicException If a variable is referenced more than once
     */
    public function compile(Route $route)
    {
        $pattern = $route->getPattern();
        $tag = '#\{(\w+)\}#';
        $replacement = ':$1';
        $jsroute = preg_replace($tag, $replacement, $pattern);

        return new CompiledJsRoute($jsroute);
    }
}
