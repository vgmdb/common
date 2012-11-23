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
use VGMdb\Component\View\ViewInterface;
use VGMdb\ControllerResolver;
use VGMdb\ExceptionListenerWrapper;
use Silex\Application as BaseApplication;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * The VGMdb application class. Extends the Silex framework with custom methods.
 *
 * @author Gigablah <gigablah@vgmdb.net>
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
        $this['exception_handler'] = $this->share(function () use ($app) {
            return new ExceptionListener($app['debug']);
        });

        $this['dispatcher'] = $this->share($this->extend('dispatcher', function ($dispatcher) use ($app) {
            // subdomain handler listens to onKernelRequest
            $dispatcher->addSubscriber(new SubdomainListener($app));
            // extension handler listens to onKernelRequest
            $dispatcher->addSubscriber(new ExtensionListener($app));

            return $dispatcher;
        }));

        $this['request.format.extensions'] = array('json', 'xml', 'gif');

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

        // replace the controller resolver
        $this['resolver'] = $this->share(function () use ($app) {
            return new ControllerResolver($app, $app['logger']);
        });
    }

    /**
     * Sets the layout to wrap the controller view with.
     *
     * @param string $layout Layout name.
     *
     * @return Controller
     */
    public function layout($layout)
    {
        return $this['controllers']->value('_layout', $layout);
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
     * {@inheritdoc}
     */
    public function error($callback, $priority = -8)
    {
        $this['dispatcher']->addListener(KernelEvents::EXCEPTION, new ExceptionListenerWrapper($this, $callback), $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function mount($prefix, $app)
    {
        if ($app instanceof ControllerProviderInterface) {
            $app = $app->connect($this);
        }

        if ($app instanceof ControllerCollection) {
            $app = $app->flush($prefix);
        }

        if (!$app instanceof RouteCollection) {
            throw new \LogicException('The "mount" method takes either a RouteCollection, ControllerCollection or ControllerProviderInterface instance.');
        }

        $this['routes']->addCollection($app, $prefix);
    }
}
