<?php

namespace VGMdb\Component\HttpKernel\EventListener;

use VGMdb\TraceableApplication;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Interrupts the output if a container trace is detected.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ContainerTraceListener implements EventSubscriberInterface
{
    protected $app;

    public function __construct(TraceableApplication $app)
    {
        $this->app = $app;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if ($trace = $this->app->getTrace()) {
            throw $trace;
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', -8)),
        );
    }
}
