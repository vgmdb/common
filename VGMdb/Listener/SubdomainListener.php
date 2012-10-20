<?php

namespace VGMdb\Listener;

use VGMdb\Application;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @brief       Sets request attributes and format priorities based on a matching subdomain.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class SubdomainListener implements EventSubscriberInterface
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $subdomain = $request->getSubdomain();

        if ($subdomain == 'api') {
            $this->app['request.format.priorities'] = array('json', 'xml');
            $this->app['request.format.fallback'] = 'json';
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 512)),
        );
    }
}