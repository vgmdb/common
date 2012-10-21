<?php

namespace VGMdb\Listener;

use VGMdb\Component\HttpKernel\Debug\ExceptionHandler;
use VGMdb\Component\HttpFoundation\Response;
use VGMdb\Component\HttpFoundation\JsonResponse;
use VGMdb\Component\HttpFoundation\XmlResponse;
use VGMdb\Component\HttpFoundation\BeaconResponse;
use Silex\SilexEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @brief       Custom exception listener.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ExceptionListener implements EventSubscriberInterface
{
    public function onSilexError(GetResponseForExceptionEvent $event)
    {
        $app = $event->getKernel();
        $handler = new ExceptionHandler($app['debug']);
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
        return array(SilexEvents::ERROR => array('onSilexError', -255));
    }
}
