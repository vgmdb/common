<?php

/*
 * This file was originally part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */

namespace VGMdb\Component\Routing\EventListener;

use VGMdb\Component\Routing\Loader\AllowedMethodsLoaderInterface;
use VGMdb\Component\Translation\Routing\TranslationRouteLoader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener to append Allow-ed methods for a given route/resource
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
class AllowedMethodsListener implements EventSubscriberInterface
{
    /**
     * @var AllowedMethodsLoaderInterface
     */
    private $loader;

    /**
     * Constructor.
     *
     * @param AllowedMethodsLoaderInterface $loader
     */
    public function __construct(AllowedMethodsLoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ('OPTIONS' === $event->getRequest()->getMethod()) {
            $response = new Response();
            $response->headers->set('Content-Length', 0);

            $event->setResponse($response);
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $allowedMethods = $this->loader->getAllowedMethods();

        $route = $event->getRequest()->attributes->get(
            '_original_route',
            $event->getRequest()->attributes->get('_route')
        );

        if (isset($allowedMethods[$route])) {
            $response = $event->getResponse();
            $response->headers->set('Allow', implode(', ', $allowedMethods[$route]));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST  => array(array('onKernelRequest', 16)),
            KernelEvents::RESPONSE => array(array('onKernelResponse', 0)),
        );
    }
}
