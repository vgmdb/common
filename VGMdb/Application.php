<?php

namespace VGMdb;

use VGMdb\Component\HttpFoundation\Request;
use VGMdb\Component\HttpFoundation\Response;
use VGMdb\Component\HttpKernel\EventListener\ExceptionListener;
use VGMdb\Component\Routing\RequestContext;
use VGMdb\Component\View\ViewInterface;
use VGMdb\ControllerCollection;
use VGMdb\ControllerResolver;
use VGMdb\ExceptionListenerWrapper;
use VGMdb\RedirectableUrlMatcher;
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
    private $booting;
    private $booted;
    private $readonly;
    private $bootlog;
    private $stopwatch;
    private $startTime;

    /**
     * Constructor.
     */
    public function __construct(array $values = array())
    {
        $this->booting = false;
        $this->booted = false;
        $this->readonly = array();
        $this->bootlog = array();
        $this->startTime = microtime(true);

        // we don't pass $values into the parent constructor; we'll handle it ourselves
        parent::__construct();

        $app = $this;

        $this['name'] = '';

        // replace the default exception handler
        $this['exception_handler'] = $this->share(function ($app) {
            return new ExceptionListener($app['debug']);
        });

        // replace the controller factory
        $this['controllers_factory'] = function () use ($app) {
            return new ControllerCollection($app['route_factory'], $app['debug']);
        };

        // replace the controller resolver
        $this['resolver'] = $this->share(function ($app) {
            return new ControllerResolver($app, $app['logger']);
        });

        // replace the request context
        $this['request_context'] = $this->share(function ($app) {
            $context = new RequestContext(null, null, null, null, $app['request.http_port'], $app['request.https_port']);
            if (class_exists('Mobile_Detect')) {
                $context->setMobileDetector(new \Mobile_Detect());
            }
            $context->setAppName($app['name']);

            return $context;
        });

        // replace the redirectable url matcher
        $this['url_matcher'] = $this->share(function ($app) {
            return new RedirectableUrlMatcher($app['routes'], $app['request_context']);
        });

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
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
     * Returns the request start time.
     *
     * @return integer
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Returns the service boot log.
     *
     * @return array
     */
    public function getBootlog()
    {
        return $this->bootlog;
    }

    /**
     * Stop tracing service access.
     */
    public function stopTrace()
    {
        $this->stopwatch = null;
    }

    /**
     * {@inheritdoc}
     */
    public function mount($prefix, $controllers)
    {
        if ($controllers instanceof ControllerProviderInterface) {
            $controllers = $controllers->connect($this);
        }

        if ($controllers instanceof ControllerCollection) {
            $controllers = $controllers->flush($prefix);
        }

        if (!$controllers instanceof RouteCollection) {
            throw new \LogicException('The "mount" method takes either a RouteCollection, ControllerCollection or ControllerProviderInterface instance.');
        }

        $this['routes']->addCollection($controllers, $prefix);
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
                throw new \RuntimeException('Cannot instantiate service before application is booted.');
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

        if ($this['debug'] && isset($this['debug.stopwatch'])) {
            $start = microtime(true) * 1000;
            $this->stopwatch = $this['debug.stopwatch'];
            $end = microtime(true) * 1000;
            $this->bootlog['debug.stopwatch'] = sprintf('%.0f', $end - $start);
        }

        parent::boot();

        $this->booted = true;
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

    /**
     * {@inheritdoc}
     */
    public function offsetGet($id)
    {
        $event = null;

        if (null !== $this->stopwatch) {
            $event = $this->stopwatch->start($id, 'boot');
        }

        $value = parent::offsetGet($id);

        if (null !== $event) {
            $event->stop($id);
            if (!isset($this->bootlog[$id])) {
                $this->bootlog[$id] = $event->getDuration();
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function before($callback, $priority = 0)
    {
        throw new \RuntimeException('Calling before() is not allowed. Please create a proper Listener class.');
    }

    /**
     * {@inheritdoc}
     */
    public function after($callback, $priority = 0)
    {
        throw new \RuntimeException('Calling after() is not allowed. Please create a proper Listener class.');
    }

    /**
     * {@inheritdoc}
     */
    public function finish($callback, $priority = 0)
    {
        throw new \RuntimeException('Calling finish() is not allowed. Please create a proper Listener class.');
    }

    /**
     * {@inheritdoc}
     */
    public function error($callback, $priority = -8)
    {
        throw new \RuntimeException('Calling error() is not allowed. Please create a proper Listener class.');
    }
}
