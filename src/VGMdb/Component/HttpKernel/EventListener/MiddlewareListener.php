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
            $controller = $this->createController($controller);
            $ret = call_user_func($controller, $request, $this->app);
            if ($ret instanceof Response) {
                $event->setResponse($ret);

                return;
            } elseif (null !== $ret) {
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
            $controller = $this->createController($controller);
            $response = call_user_func($controller, $request, $event->getResponse(), $this->app);
            if ($response instanceof Response) {
                $event->setResponse($response);
            } elseif (null !== $response) {
                throw new \RuntimeException(sprintf('An after middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
            }
        }
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string  $controller A Controller string
     * @param Request $request    The request object
     *
     * @return mixed A PHP callable
     */
    protected function createController($controller, $request = null)
    {
        if (false === strpos($controller, ':')) {
            $action = $request ? $request->attributes->get('_action') : '';
            $action = $action ?: 'index';
            list($class, $method) = array($controller, $action . 'Action');
        } else {
            list($class, $method) = explode(':', $controller, 2);
            $method .= 'Action';
        }

        if (false !== strpos($class, '\\')) {
            $class = $class;
        } elseif (isset($this->app[$class])) {
            $class = $this->app[$class];
        } elseif (isset($this->app['namespace'])) {
            $class = $this->app['namespace'] . '\\Controllers\\' . $class;
        }

        if ($class instanceof \Closure) {
            return $class;
        }

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $controller = new $class($this->app);

        return array($controller, $method);
    }
}
