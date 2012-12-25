<?php

namespace VGMdb;

use VGMdb\Component\HttpFoundation\Request;
use VGMdb\Component\HttpFoundation\Response;
use VGMdb\Component\HttpKernel\EventListener\ExceptionListener;
use VGMdb\Component\View\ViewInterface;
use VGMdb\ControllerCollection;
use VGMdb\ControllerResolver;
use VGMdb\ExceptionListenerWrapper;
use Silex\Application as BaseApplication;
use Silex\ControllerProviderInterface;
use Silex\LazyUrlMatcher;
use Silex\EventListener\LocaleListener;
use Silex\EventListener\MiddlewareListener;
use Silex\EventListener\ConverterListener;
use Silex\EventListener\StringToResponseListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;

/**
 * The VGMdb application class. Extends the Silex framework with custom methods.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Application extends BaseApplication
{
    private $readonly;
    private $booting = false;
    protected $logger;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->readonly = array();

        parent::__construct();

        $app = $this;

        // replace the default exception handler
        $this['exception_handler'] = $this->share(function () use ($app) {
            return new ExceptionListener($app['debug']);
        });

        $this['request.format.extensions'] = array('json', 'xml', 'gif');

        // replace the controller factory with our own implementation
        $this['controllers_factory'] = function () use ($app) {
            $controllers = new ControllerCollection($app['route_factory'], $app['debug']);

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
     * Sets a readonly value.
     *
     * @param string $id    The unique identifier
     * @param mixed  $value The value to protect
     */
    public function readonly($id, $value)
    {
        $this[$id] = $value;

        $this->readonly[$id] = true;
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

    /**
     * {@inheritdoc}
     */
    public function share(\Closure $callable)
    {
        $booting = &$this->booting;
        return function ($c) use ($callable, &$booting) {
            static $object;

            if (!$booting) {
                throw new \ErrorException('Cannot instantiate service before application is booted.');
            }

            if (is_null($object)) {
                $object = $callable($c);
            }

            return $object;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->booting = true;

        parent::boot();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($id, $value)
    {
        if (array_key_exists($id, $this->readonly) && parent::offsetExists($id)) {
            throw new \RuntimeException(sprintf('Identifier "%s" is readonly.', $id));
        }

        parent::offsetSet($id, $value);
    }
}
