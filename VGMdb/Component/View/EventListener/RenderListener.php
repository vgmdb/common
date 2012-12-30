<?php

namespace VGMdb\Component\View\EventListener;

use VGMdb\Application;
use VGMdb\Component\View\ViewInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Renders views for HTML responses.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RenderListener implements EventSubscriberInterface
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if ($event->getRequest()->getRequestFormat() !== 'html') {
            return;
        }

        $content = $event->getResponse()->getContent();
        if (!$content instanceof ViewInterface) {
            return;
        }

        $event->getResponse()->setContent((string) $content);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', -64)),
        );
    }
}
