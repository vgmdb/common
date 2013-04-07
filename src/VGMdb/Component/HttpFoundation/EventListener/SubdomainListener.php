<?php

namespace VGMdb\Component\HttpFoundation\EventListener;

use VGMdb\Application;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets request attributes and format priorities based on a matching subdomain.
 *
 * @author Gigablah <gigablah@vgmdb.net>
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

        $subdomain = $this->getSubdomain($request->getHost());
        $this->app['request_context']->setSubdomain($subdomain);

        if ($subdomain === 'api') {
            $this->app['request.format.priorities'] = array('json');
            $this->app['request.format.fallback'] = 'json';
        }

        if ($subdomain === 'm') {
            $this->app['request_context']->setClient('mobile');
        }
    }

    /**
     * Gets the subdomain from the host.
     *
     * @param string $host Host obtained from the Request object.
     *
     * @return string Extracted subdomain.
     */
    protected function getSubdomain($host)
    {
        if (preg_match('#^(?P<subdomain>.*).' . $this->app['app.options']['domains']['web'] . '$#s', $host, $matches)) {
            return $matches['subdomain'];
        }

        return null;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', Application::EARLY_EVENT)),
        );
    }
}
