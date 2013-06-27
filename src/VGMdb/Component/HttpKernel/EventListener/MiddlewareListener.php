<?php

namespace VGMdb\Component\HttpKernel\EventListener;

use Silex\EventListener\MiddlewareListener as BaseMiddlewareListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
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
        $this->runMiddlewares($event, 'before');
    }

    /**
     * Runs after filters.
     *
     * @param FilterResponseEvent $event The event to handle
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $this->runMiddlewares($event, 'after');
    }

    /**
     * Runs finish filters.
     *
     * @param PostResponseEvent $event The event to handle
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $this->runMiddlewares($event, 'finish');
    }

    /**
     * Generic middleware execution.
     *
     * @param mixed  $event          The event to handle
     * @param string $middlewareType The type of middleware
     */
    protected function runMiddlewares($event, $middlewareType)
    {
        $routeName = $event->getRequest()->attributes->get('_route');
        if (!$route = $this->app['routes']->get($routeName)) {
            return;
        }

        $midRequest = clone $event->getRequest();
        foreach ((array) $route->getOption(sprintf('_%s_middlewares', $middlewareType)) as $controller) {
            $midRequest->attributes->set('_controller', $controller);
            $controller = $this->app['resolver']->getController($midRequest);

            if ($event instanceof GetResponseEvent) {
                $response = call_user_func($controller, $event->getRequest(), $this->app);
            } else {
                $response = call_user_func($controller, $event->getRequest(), $event->getResponse(), $this->app);
            }

            if ($response instanceof Response) {
                $event->setResponse($response);
            } elseif (null !== $response) {
                throw new \RuntimeException(sprintf(
                    'One of the "%s" middlewares for route "%s" returned an invalid response value. Must return null or an instance of Response.',
                    $middlewareType,
                    $routeName
                ));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // this must be executed after the late events defined with before() (and their priority is 512)
            KernelEvents::REQUEST   => array('onKernelRequest', -1024),
            KernelEvents::RESPONSE  => array('onKernelResponse', 128),
            KernelEvents::TERMINATE => array('onKernelTerminate', 0),
        );
    }
}
