<?php

namespace VGMdb\Listener;

use VGMdb\Application;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @brief       Sets request _format based on a matching extension in the URL.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ExtensionListener implements EventSubscriberInterface
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $uri = $request->getRequestUri();

        if (strpos($uri, '.') === false) {
            return;
        }

        $formats = $this->app['request.format.extensions'];
        if (!is_array($formats)) {
            throw new \InvalidArgumentException('No valid file extensions specified.');
        }

        $querystring = '';
        if ($pos = strpos($uri, '?')) {
            $querystring = substr($uri, $pos);
            $uri = substr($uri, 0, $pos);
        }
        $segments = explode('.', $uri);
        $format = array_pop($segments);

        if (in_array($format, $formats)) {
            $uri = implode('.', $segments) . $querystring;
            $request->query->set('_format', $format);
            $request->setRequestUri($uri);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 255)),
        );
    }
}