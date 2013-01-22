<?php

namespace VGMdb\Component\HttpKernel\EventListener;

use VGMdb\Component\Routing\RequestContext;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Maps the locale to complete language and region code.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class LocaleMappingListener implements EventSubscriberInterface
{
    protected $app;
    protected $context;
    protected $localeMap;

    public function __construct(Application $app, RequestContext $context, array $localeMap = array())
    {
        $this->app = $app;
        $this->localeMap = $localeMap;
        $this->context = $context;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $locale = $this->app['locale'];
        if (array_key_exists($locale, $this->localeMap)) {
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
