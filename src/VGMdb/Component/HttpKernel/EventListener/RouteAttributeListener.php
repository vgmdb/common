<?php

namespace VGMdb\Component\HttpKernel\EventListener;

use VGMdb\Component\Routing\RequestContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

/**
 * Replaces placeholders in route attributes based on request context.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RouteAttributeListener implements EventSubscriberInterface
{
    private $context;
    private $logger;

    /**
     * Constructor.
     *
     * @param RequestContext       $context The RequestContext
     * @param LoggerInterface|null $logger  The logger
     */
    public function __construct(RequestContext $context, LoggerInterface $logger = null)
    {
        $this->context = $context;
        $this->logger = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->attributes->all();

        $replacements = array(
            '%app%' => $this->context->getAppName(),
            '%client%' => 'm' === $this->context->getSubdomain()
                ? 'mobile'
                : ($this->context->isMobile() ? 'mobile' : 'web')
        );

        $request->attributes->replace($this->doReplacements($attributes, $replacements));
    }

    protected function doReplacements($value, $replacements)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->doReplacements($val, $replacements);
            }

            return $value;
        }

        if (is_string($value)) {
            return strtr($value, $replacements);
        }

        return $value;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 12)),
        );
    }
}
