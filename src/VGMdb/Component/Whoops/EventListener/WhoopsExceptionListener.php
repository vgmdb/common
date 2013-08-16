<?php

namespace VGMdb\Component\Whoops\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Silex\Application;
use Whoops\Run;

/**
 * Exception listener that calls the filp/whoops library.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class WhoopsExceptionListener implements EventSubscriberInterface
{
    protected $app;
    protected $whoops;

    /**
     * Constructor.
     *
     * @param Application $app    An Application instance
     * @param Run         $whoops A Whoops Run instance
     */
    public function __construct(Application $app, Run $whoops)
    {
        $this->app = $app;
        $this->whoops = $whoops;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $this->whoops->writeToOutput(false);
        $response = call_user_func(array($this->whoops, Run::EXCEPTION_HANDLER), $exception);

        $this->ensureResponse($response, $event);
    }

    protected function ensureResponse($response, GetResponseForExceptionEvent $event)
    {
        if (!$response instanceof Response) {
            $response = new Response($response);
        }

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -127),
        );
    }
}
