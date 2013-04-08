<?php

namespace VGMdb\Component\Locale\EventListener;

use VGMdb\Component\Routing\RequestContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets the timezone based on the locale region.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TimezoneListener implements EventSubscriberInterface
{
    protected $context;
    protected $timezoneMap;

    public function __construct(RequestContext $context, array $timezoneMap = array())
    {
        $this->context = $context;
        $this->timezoneMap = $timezoneMap;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $region = $this->context->getRegion();
        if (isset($this->timezoneMap[$region])) {
            date_default_timezone_set($this->timezoneMap[$region]);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the LocaleListener and LocaleMappingListener
            KernelEvents::REQUEST => array(array('onKernelRequest', 14)),
        );
    }
}
