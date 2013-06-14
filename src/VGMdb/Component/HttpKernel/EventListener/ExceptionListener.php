<?php

namespace VGMdb\Component\HttpKernel\EventListener;

use VGMdb\Application;
use VGMdb\Component\HttpKernel\Debug\ExceptionHandler;
use VGMdb\Component\HttpKernel\Debug\ApiExceptionHandler;
use VGMdb\Component\HttpFoundation\Response;
use VGMdb\Component\HttpFoundation\JsonResponse;
use VGMdb\Component\HttpFoundation\XmlResponse;
use VGMdb\Component\HttpFoundation\BeaconResponse;
use VGMdb\Component\Thrift\ThriftResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Thrift\Exception\TApplicationException;
use Thrift\Type\TMessageType;

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
        $exception = $event->getException();

        if ($exception instanceof HttpExceptionInterface) {
            $code = $exception->getStatusCode();
            $headers = $exception->getHeaders();
        } else {
            $code = 500;
            $headers = array();
        }

        if ($code === 404) {
            $title = 'Sorry, the page you are looking for could not be found.';
        } else {
            $title = 'Whoops, looks like something went wrong.';
        }

        switch ($format = $event->getRequest()->getRequestFormat()) {
            case 'json':
            case 'js':
                $handler = new ApiExceptionHandler($this->debug);
                $data = $handler->createResponse($event->getException());
                $response = new JsonResponse($data, $code, $headers);
                break;
            case 'xml':
                $handler = new ApiExceptionHandler($this->debug);
                $data = $handler->createResponse($event->getException());
                $response = new XmlResponse($data, $code, $headers);
                break;
            case 'gif':
            case 'png':
            case 'jpg':
                $response = new BeaconResponse($format, $code, $headers);
                break;
            case 'thrift':
                $response = new ThriftResponse(function ($input, $output) use ($exception) {
                    $ex = new TApplicationException($exception->getMessage(), TApplicationException::INTERNAL_ERROR);
                    $output->writeMessageBegin(null, TMessageType::EXCEPTION, 0);
                    $ex->write($output);
                    $output->writeMessageEnd();
                    $output->getTransport()->flush();
                }, $code, $headers);
                break;
            default:
                $handler = new ExceptionHandler($this->debug);
                $response = $handler->createResponse($event->getException());
                break;
        }

        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array(array('onKernelException', Application::LATE_EVENT))
        );
    }
}
