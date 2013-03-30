<?php

/*
 * This file was originally part of the Nelmio SecurityBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 */

namespace VGMdb\Component\Security\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds HTTP Strict Transport Security (HSTS) header to applicable responses.
 * This ensures that compliant browsers enforce SSL connections for a particular domain or subdomain.
 *
 * @link https://www.owasp.org/index.php/HTTP_Strict_Transport_Security
 */
class TransportSecurityListener implements EventSubscriberInterface
{
    private $hstsMaxAge;
    private $hstsSubdomains;

    public function __construct($hstsMaxAge, $hstsSubdomains)
    {
        $this->hstsMaxAge = $hstsMaxAge;
        $this->hstsSubdomains = $hstsSubdomains;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        // skip SSL and non-GET/HEAD requests
        if ($request->isSecure() || !$request->isMethodSafe()) {
            return;
        }

        if (is_array($this->hstsSubdomains) && !in_array($request->getSubdomain(), $this->hstsSubdomains)) {
            return;
        }

        $event->setResponse(new RedirectResponse('https://'.substr($request->getUri(), 7), 301));
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (is_array($this->hstsSubdomains) && !in_array($event->getRequest()->getSubdomain(), $this->hstsSubdomains)) {
            return;
        }

        $response = $event->getResponse();

        if (!$response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', 'max-age='.$this->hstsMaxAge.(true === $this->hstsSubdomains ? '; includeSubDomains' : ''));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 0)),
            KernelEvents::RESPONSE => array(array('onKernelResponse', 0)),
        );
    }
}
