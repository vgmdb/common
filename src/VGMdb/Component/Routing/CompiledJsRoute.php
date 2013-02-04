<?php

namespace VGMdb\Component\Routing;

use Symfony\Component\Routing\CompiledRoute;

/**
 * CompiledJsRoutes are returned by the JsRouteCompiler class.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CompiledJsRoute extends CompiledRoute
{
    private $jsroute;

    /**
     * Constructor.
     *
     * @param string $jsRoute
     */
    public function __construct($jsRoute)
    {
        $this->jsRoute = $jsRoute;
    }

    /**
     * Returns the Javascript route.
     *
     * @return string
     */
    public function getJsRoute()
    {
        return $this->jsRoute;
    }
}
