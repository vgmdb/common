<?php

/*
 * This file was originally part of the Nelmio SecurityBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 */

namespace VGMdb\Component\Security\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Appends the X-Frame-Options header which prevents clickjacking in compliant browsers.
 *
 * @link https://developer.mozilla.org/en-US/docs/HTTP/X-Frame-Options
 */
class FrameOptionsListener implements EventSubscriberInterface
{
    private $paths;

    public function __construct($paths)
    {
        $this->paths = $paths;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if ($event->getRequest()->getRequestFormat() !== 'html') {
            return;
        }

        $response = $event->getResponse();
        $currentPath = $event->getRequest()->getPathInfo() ?: '/';

        foreach ($this->paths as $path => $policy) {
            if (preg_match('#'.$path.'#i', $currentPath)) {
                if ('ALLOW' === $policy) {
                    $response->headers->remove('X-Frame-Options');
                } else {
                    $response->headers->set('X-Frame-Options', $policy);
                }

                return;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array(array('onKernelResponse', 0)),
        );
    }
}
