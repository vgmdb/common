<?php

namespace VGMdb\Component\HttpKernel\EventListener;

use Silex\EventListener\MiddlewareListener as BaseMiddlewareListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manages the route middlewares.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MiddlewareListener extends BaseMiddlewareListener
{
    /**
     * Runs before filters.
     *
     * @param GetResponseEvent $event The event to handle
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        if (!$route = $this->app['routes']->get($routeName)) {
            return;
        }

        foreach ((array) $route->getOption('_before_middlewares') as $controller) {
            $midRequest = clone $request;
            $midRequest->attributes->set('_controller', $controller);
            $controller = $this->app['resolver']->getController($midRequest);

            $response = call_user_func($controller, $request, $this->app);

            if ($response instanceof Response) {
                $event->setResponse($response);
            } elseif (null !== $response) {
                throw new \RuntimeException(sprintf('A before middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
            }
        }
    }

    /**
     * Runs after filters.
     *
     * @param FilterResponseEvent $event The event to handle
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        if (!$route = $this->app['routes']->get($routeName)) {
            return;
        }

        foreach ((array) $route->getOption('_after_middlewares') as $controller) {
            $midRequest = clone $request;
            $midRequest->attributes->set('_controller', $controller);
            $controller = $this->app['resolver']->getController($midRequest);

            $response = call_user_func($controller, $request, $event->getResponse(), $this->app);

            if ($response instanceof Response) {
                $event->setResponse($response);
            } elseif (null !== $response) {
                throw new \RuntimeException(sprintf('An after middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
            }
        }
    }
}
