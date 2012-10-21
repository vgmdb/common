<?php

namespace VGMdb;

use VGMdb\Controller;
use Silex\ControllerCollection as BaseControllerCollection;

class ControllerCollection extends BaseControllerCollection
{
    /**
     * {@inheritDoc}
     */
    public function match($pattern, $to)
    {
        $route = clone $this->defaultRoute;
        $route->setPattern($pattern);
        $route->setDefault('_controller', $to);

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