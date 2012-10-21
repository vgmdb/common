<?php

namespace VGMdb\Provider;

use VGMdb\Component\HttpFoundation\Response;
use VGMdb\Component\HttpFoundation\JsonResponse;
use VGMdb\Component\HttpFoundation\XmlResponse;
use VGMdb\Component\HttpFoundation\BeaconResponse;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\Rest\Util\Codes;
use FOS\Rest\Util\FormatNegotiator;

/**
 * @brief       Handles Accept header format and version negotiation.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class FormatNegotiatorProvider implements ServiceProviderInterface
{
    private $app;

    public function register(Application $app)
    {
        $this->app = $app;

        $app['request.format.negotiator'] = $app->share(function () use ($app) {
            return new FormatNegotiator();
        });
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
                throw new HttpException(
                    Codes::HTTP_NOT_ACCEPTABLE,
                    'No matching accepted Request format could be determined.'
                );
            }

            return;
        }

        $request->setRequestFormat($format);

        $versions = $request->splitHttpAcceptHeader(
            $request->headers->get('Accept'),
            'version',
            \VGMdb\Application::VERSION
        );
        foreach ($versions as $mimetype => $version) {
            if ($request->getFormat($mimetype) === $format) {
                $request->setRequestVersion($version);
                break;
            }
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

    public function boot(Application $app)
    {
        $app['dispatcher']->addListener(KernelEvents::REQUEST, array($this, 'onKernelRequest'), 255);
        $app['dispatcher']->addListener(KernelEvents::VIEW, array($this, 'onKernelView'), -5);
    }
}
