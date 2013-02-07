<?php

namespace VGMdb\Component\HttpKernel\Controller;

use Silex\ControllerCollection as BaseControllerCollection;
use Silex\Route;

/**
 * Overrides the default Controller class, and adds support for PATCH.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ControllerCollection extends BaseControllerCollection
{
    protected $debug;

    /**
     * {@inheritDoc}
     */
    public function __construct(Route $defaultRoute, $debug = false)
    {
        $this->debug = (Boolean) $debug;

        parent::__construct($defaultRoute);
    }

    /**
     * {@inheritDoc}
     */
    public function match($pattern, $to)
    {
        $route = clone $this->defaultRoute;
        $route->setPattern($pattern);
        $route->setDefault('_controller', $to);
        $route->setDefault('_debug', $this->debug);

        $this->controllers[] = $controller = new Controller($route);

        return $controller;
    }

    /**
     * Maps a PATCH request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param mixed  $to      Callback that returns the response when matched
     *
     * @return Controller
     */
    public function patch($pattern, $to)
    {
        return $this->match($pattern, $to)->method('PATCH');
    }
}
