<?php

namespace VGMdb\Component\Serializer\EventDispatcher;

use Silex\Application;
use JMS\Serializer\EventDispatcher\EventDispatcher;

class LazyEventDispatcher extends EventDispatcher
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    protected function initializeListeners($eventName, $loweredClass, $format)
    {
        $listeners = parent::initializeListeners($eventName, $loweredClass, $format);

        foreach ($listeners as &$listener) {
            if (!is_array($listener) || !is_string($listener[0])) {
                continue;
            }

            if (!isset($this->app[$listener[0]])) {
                continue;
            }

            $listener[0] = $this->app[$listener[0]];
        }

        return $listeners;
    }
}
