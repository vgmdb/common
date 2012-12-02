<?php

namespace VGMdb\Component\Serializer\Handler;

use Silex\Application;
use JMS\Serializer\Handler\HandlerRegistry;

class LazyHandlerRegistry extends HandlerRegistry
{
    private $app;
    private $initializedHandlers = array();

    public function __construct(Application $app, array $handlers = array())
    {
        parent::__construct($handlers);
        $this->app = $app;
    }

    public function registerHandler($direction, $typeName, $format, $handler)
    {
        parent::registerHandler($direction, $typeName, $format, $handler);
        unset($this->initializedHandlers[$direction][$typeName][$format]);
    }

    public function getHandler($direction, $typeName, $format)
    {
        if (isset($this->initializedHandlers[$direction][$typeName][$format])) {
            return $this->initializedHandlers[$direction][$typeName][$format];
        }

        if (!isset($this->handlers[$direction][$typeName][$format])) {
            return null;
        }

        $handler = $this->handlers[$direction][$typeName][$format];
        if (is_array($handler) && is_string($handler[0]) && isset($this->app[$handler[0]])) {
            $handler[0] = $this->app[$handler[0]];
        }

        return $this->initializedHandlers[$direction][$typeName][$format] = $handler;
    }
}
