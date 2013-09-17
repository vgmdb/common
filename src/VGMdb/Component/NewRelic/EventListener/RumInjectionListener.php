<?php

namespace VGMdb\Component\NewRelic\EventListener;

use VGMdb\Component\NewRelic\MonitorInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Injects Real User Monitoring (RUM) Javascript tags.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RumInjectionListener implements EventSubscriberInterface
{
    protected $monitor;

    public function __construct(MonitorInterface $monitor)
    {
        $this->monitor = $monitor;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if ($event->getRequest()->getRequestFormat() !== 'html') {
            return;
        }

        $this->monitor->disableAutoRUM();

        $this->injectRumTags($event->getResponse());
    }

    /**
     * Injects the RUM header after the </title> tag and the footer before the </body> tag.
     *
     * @param Response $response A Response instance
     */
    protected function injectRumTags(Response $response)
    {
        if (function_exists('mb_stripos')) {
            $posrFunction   = 'mb_strripos';
            $substrFunction = 'mb_substr';
        } else {
            $posrFunction   = 'strripos';
            $substrFunction = 'substr';
        }

        $content = $response->getContent();

        if (false !== $pos = $posrFunction($content, '</title>')) {
            $pos += strlen('</title>');
            $header = "\n".$this->monitor->getBrowserTimingHeader()."\n";
            $content = $substrFunction($content, 0, $pos).$header.$substrFunction($content, $pos);
        }

        unset($pos);

        if (false !== $pos = $posrFunction($content, '</body>')) {
            $footer = "\n".$this->monitor->getBrowserTimingFooter()."\n";
            $content = $substrFunction($content, 0, $pos).$footer.$substrFunction($content, $pos);
        }

        $response->setContent($content);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onKernelResponse', Application::LATE_EVENT)
        );
    }
}
