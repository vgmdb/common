<?php

namespace VGMdb\Component\Security\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Appends the X-Content-Type-Options and X-XSS-Protection headers.
 *
 * @link http://msdn.microsoft.com/en-us/library/dd565647(v=vs.85).aspx
 * @link http://msdn.microsoft.com/en-us/library/ie/gg622941(v=vs.85).aspx
 */
class XssProtectionListener implements EventSubscriberInterface
{
    private $mode;

    public function __construct($mode = 'block')
    {
        $this->mode = $mode;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();

        // MSIE specific header to guard against MIME content-sniffing attacks
        // This has to be set regardless of the requested format
        if (!$response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        if ($event->getRequest()->getRequestFormat() !== 'html') {
            return;
        }

        if (!$response->headers->has('X-XSS-Protection')) {
            $response->headers->set('X-XSS-Protection', '1' . ($this->mode ? '; mode=' . $this->mode : ''));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', 0)),
        );
    }
}
