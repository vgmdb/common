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
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Thrift\Exception\TApplicationException;
use Thrift\Type\TMessageType;
use Psr\Log\LoggerInterface;

/**
 * Custom exception listener.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ExceptionListener implements EventSubscriberInterface
{
    protected $debug;
    protected $logger;

    public function __construct($debug, LoggerInterface $logger = null)
    {
        $this->debug = $debug;
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        static $handling;

        if (true === $handling) {
            return false;
        }

        $handling = true;

        $exception = $event->getException();
        $format = $event->getRequest()->getRequestFormat();

        $this->logException($exception, sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine()));

        if ($exception instanceof AuthenticationException) {
            $authType = 'html' === $format ? 'Basic' : 'OAuth';
            $exception = new UnauthorizedHttpException(sprintf('%s realm="%s"', $authType, $event->getRequest()->getSchemeAndHttpHost()), 'Authentication credentials missing or incorrect.', $exception);
        }

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

        $response = null;

        switch ($format) {
            case 'json':
            case 'js':
                $handler = new ApiExceptionHandler($this->debug);
                $data = $handler->createResponse($exception);
                $response = new JsonResponse($data, $code, $headers);
                break;
            case 'xml':
                $handler = new ApiExceptionHandler($this->debug);
                $data = $handler->createResponse($exception);
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
                if (!$this->debug && HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
                    $subRequest = $event->getRequest()->duplicate(null, null, array(
                        'exception' => FlattenException::create($exception)
                    ), null, null, array(
                        'REQUEST_URI' => sprintf('/error/%d', $code),
                        'SERVER_NAME' => $event->getRequest()->getHost()
                    ));
                    $subRequest->setMethod('GET');

                    try {
                        $response = $event->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
                    } catch (\Exception $e) {
                        $this->logException($exception, sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage()), false);
                        $response = null;
                    }
                }
                if (null === $response) {
                    $handler = new ExceptionHandler($this->debug);
                    $response = $handler->createResponse($exception);
                }
                break;
        }

        $event->setResponse($response);

        $handling = false;
    }

    /**
     * Logs an exception.
     *
     * @param \Exception $exception The original \Exception instance
     * @param string     $message   The error message to log
     * @param Boolean    $original  False when the handling of the exception thrown another exception
     */
    protected function logException(\Exception $exception, $message, $original = true)
    {
        $isCritical = !$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500;
        if (null !== $this->logger) {
            if ($isCritical) {
                $this->logger->critical($message);
            } else {
                $this->logger->error($message);
            }
        } elseif (!$original || $isCritical) {
            error_log($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -128),
        );
    }
}
