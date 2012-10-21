<?php

namespace VGMdb;

use VGMdb\Listener\ExceptionListener;
use VGMdb\Listener\SubdomainListener;
use VGMdb\Listener\ExtensionListener;
use VGMdb\Component\Validator\Constraints\JsonpCallback;
use VGMdb\Component\HttpFoundation\Request;
use VGMdb\Component\HttpFoundation\Response;
use VGMdb\Component\HttpFoundation\JsonResponse;
use VGMdb\Component\HttpFoundation\BeaconResponse;
use VGMdb\ViewInterface;
use Silex\Application as BaseApplication;
use Silex\SilexEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * @brief       The VGMdb application class. Extends the Silex framework with custom methods.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class Application extends BaseApplication
{
    const VERSION = '1.0';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $app = $this;

        // replace the default exception handler
        $this['exception_handler'] = $this->share(function () {
            return new ExceptionListener();
        });

        // subdomain handler listens to onKernelRequest
        $this['dispatcher']->addSubscriber(new SubdomainListener($app));

        // extension handler listens to onKernelRequest
        $this['request.format.extensions'] = array('json', 'xml', 'gif');
        $this['dispatcher']->addSubscriber(new ExtensionListener($app));

        // replace the controller factory with our own implementation
        $this['controllers_factory'] = function () use ($app) {
            $controllers = new ControllerCollection($app['route_factory']);

            // Handle JSON request body or image beacon requests
            $controllers->before(function (Request $request) {
                if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                    $data = json_decode($request->getContent(), true);
                    $request->request->replace(is_array($data) ? $data : array());
                }
                if ($request->getRequestFormat() === 'gif') {
                    // Short circuits the controller
                    // All beacon handling code must be put in the after() or finish() filter
                    return new BeaconResponse();
                }
            });

            $controllers->after(function (Request $request, $response) use ($app) {
                if ($response instanceof JsonResponse) {
                    $callback = $request->query->get('callback');
                    if ($callback && isset($app['validator'])) {
                        $errors = $app['validator']->validateValue($callback, new JsonpCallback());
                        if (count($errors)) {
                            $app->abort(400, 'Invalid JSONP callback.');
                        }
                        $response->setCallback($callback);
                    }
                }
            });

            return $controllers;
        };
    }

    /**
     * Sets the layout to wrap the controller view with.
     *
     * @param ViewInterface $view View object.
     *
     * @return Controller
     */
    public function layout(ViewInterface $view)
    {
        return $this['controllers']->value('_layout', $view);
    }

    /**
     * Maps a PATCH request to a callable.
     *
     * @param string $pattern Matched route pattern
     * @param mixed  $to      Callback that returns the response when matched
     *
     * @return Controller
     */
    public function patch($pattern, $to)
    {
        return $this['controllers']->patch($pattern, $to);
    }

    /**
     * Custom error handler.
     *
     * @param mixed   $callback Error handler callback, takes an Exception argument
     * @param integer $priority The higher this value, the earlier an event
     *                          listener will be triggered in the chain (defaults to 0)
     */
    public function error($callback, $priority = 0)
    {
        $this['dispatcher']->addListener(SilexEvents::ERROR, function (GetResponseForExceptionEvent $event) use ($callback) {
            $exception = $event->getException();
            $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

            if (is_array($callback)) {
                $callbackReflection = new \ReflectionMethod($callback[0], $callback[1]);
            } elseif (is_object($callback) && !$callback instanceof \Closure) {
                $callbackReflection = new \ReflectionObject($callback);
                $callbackReflection = $callbackReflection->getMethod('__invoke');
            } else {
                $callbackReflection = new \ReflectionFunction($callback);
            }

            if ($callbackReflection->getNumberOfParameters() > 0) {
                $parameters = $callbackReflection->getParameters();
                $expectedParameter = $parameters[0];
                if ($expectedParameter->getClass() && $expectedParameter->getClass()->isInstance($event)) {
                    $result = call_user_func($callback, $event, $code);
                } elseif ($expectedParameter->getClass() && !$expectedParameter->getClass()->isInstance($exception)) {
                    return;
                } else {
                    $result = call_user_func($callback, $exception, $code);
                }
            }

            if (null !== $result) {
                $response = $result instanceof Response ? $result : new Response($result);

                $event->setResponse($response);
            }
        }, $priority);
    }
}