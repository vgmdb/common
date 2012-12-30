<?php

namespace VGMdb\Component\HttpFoundation\EventListener;

use VGMdb\Application;
use VGMdb\Component\HttpFoundation\Response;
use VGMdb\Component\HttpFoundation\JsonResponse;
use VGMdb\Component\HttpFoundation\XmlResponse;
use VGMdb\Component\HttpFoundation\BeaconResponse;
use VGMdb\Component\Validator\Constraints\JsonpCallback;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles request body, response and callback for certain formats.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RequestFormatListener implements EventSubscriberInterface
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $format = null;

        if (!empty($this->app['request.format.priorities'])) {
            $format = $this->app['request.format.negotiator']->getBestFormat(
                $request,
                $this->app['request.format.priorities'],
                $this->app['request.format.prefer_extension']
            );
        }
        if ($format === null) {
            $format = $this->app['request.format.fallback'];
        }
        if ($format === null) {
            if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
                throw new HttpException(406, 'No matching accepted Request format could be determined.');
            }

            return;
        }

        $request->setRequestFormat($format);

        $version = $this->app['request.format.negotiator']->getVersionForFormat(
            $request,
            $format,
            $this->app['request.format.default_version']
        );

        $request->setRequestVersion($version);

        // decode JSON request body
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json') && is_string($request->getContent())) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }

        if ($request->getRequestFormat() === 'gif') {
            // Short circuits the controller
            // All beacon handling code must be in after() or finish()
            $event->setResponse(new BeaconResponse());
        }
    }

    /**
     * Intercepts responses and formats it in the appropriate format.
     *
     * @param GetResponseForControllerResultEvent $event The event to handle
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getControllerResult();

        if (!($response instanceof Response)) {
            switch ($format = $request->getRequestFormat()) {
                case 'json':
                case 'js':
                    $response = new JsonResponse($response);
                    break;
                case 'xml':
                    $response = new XmlResponse($response);
                    break;
                case 'gif':
                case 'png':
                case 'jpg':
                    $response = new BeaconResponse($format);
                    break;
                default:
                    $response = new Response($response);
                    break;
            }
        }

        $event->setResponse($response);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($response instanceof JsonResponse) {
            $callback = $request->query->get('callback');
            if ($callback && isset($this->app['validator'])) {
                $errors = $this->app['validator']->validateValue($callback, new JsonpCallback());
                if (count($errors)) {
                    $this->app->abort(400, 'Invalid JSONP callback.');
                }
                $response->setCallback($callback);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST  => array(array('onKernelRequest', 128)),
            KernelEvents::VIEW     => array(array('onKernelView', -5)),
            KernelEvents::RESPONSE => array(array('onKernelResponse', -16)),
        );
    }
}
