<?php

namespace VGMdb\Component\Swiftmailer\EventListener;

use VGMdb\Application;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sends emails for the memory spool upon application termination.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class EmailSenderListener implements EventSubscriberInterface
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (!$this->app['mailer.initialized']) {
            return;
        }

        $transport = $this->app['mailer']->getTransport();
        if (!$transport instanceof \Swift_Transport_SpoolTransport) {
            return;
        }

        $spool = $transport->getSpool();
        if (!$spool instanceof \Swift_MemorySpool) {
            return;
        }

        $spool->flushQueue($this->app['swiftmailer.transport']);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::TERMINATE => 'onKernelTerminate'
        );
    }
}
