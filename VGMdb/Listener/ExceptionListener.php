<?php

namespace VGMdb\Listener;

use VGMdb\Application;
use VGMdb\Component\HttpKernel\Debug\ExceptionHandler;
use VGMdb\Component\HttpFoundation\Response;
use VGMdb\Component\HttpFoundation\JsonResponse;
use VGMdb\Component\HttpFoundation\XmlResponse;
use VGMdb\Component\HttpFoundation\BeaconResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Custom exception listener.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ExceptionListener implements EventSubscriberInterface
{
    protected $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $app = $event->getKernel();
        $handler = new ExceptionHandler($this->debug);
        list($title, $html, $code, $headers) = $handler->createResponse($event->getException());

        switch ($format = $event->getRequest()->getRequestFormat()) {
            case 'json':
            case 'js':
                $response = new JsonResponse(array('error' => $code, 'message' => $title), $code, $headers);
                break;
            case 'xml':
                $response = new XmlResponse(array('error' => $code, 'message' => $title), $code, $headers);
                break;
            case 'gif':
            case 'png':
            case 'jpg':
                $response = new BeaconResponse($format, $code, $headers);
                break;
            default:
                $response = new Response($html, $code, $headers);
                break;
        }

        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::EXCEPTION => array('onKernelException', Application::EARLY_EVENT));
    }
}
