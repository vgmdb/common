<?php

namespace VGMdb\Component\HttpKernel\EventListener;

use VGMdb\Component\Routing\RequestContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Maps the locale to complete language and region code, and loads additional configuration if found.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LocaleMappingListener implements EventSubscriberInterface
{
    protected $context;
    protected $localeMap;

    public function __construct(RequestContext $context, array $localeMap = array())
    {
        $this->context = $context;
        $this->localeMap = $localeMap;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $locale = $event->getRequest()->getLocale();
        if (isset($this->localeMap[$locale])) {
            $locale = $this->localeMap[$locale];
        }

        $this->context->setLocale($locale);
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the LocaleListener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}
