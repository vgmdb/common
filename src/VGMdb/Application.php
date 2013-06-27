<?php

namespace VGMdb;

use VGMdb\Component\HttpFoundation\Request;
use VGMdb\Component\HttpFoundation\Response;
use VGMdb\Component\HttpKernel\Controller\ControllerCollection;
use VGMdb\Component\HttpKernel\Controller\ControllerNameParser;
use VGMdb\Component\HttpKernel\Controller\ControllerResolver;
use VGMdb\Component\HttpKernel\EventListener\ExceptionListener;
use VGMdb\Component\HttpKernel\EventListener\ExceptionListenerWrapper;
use VGMdb\Component\HttpKernel\EventListener\MiddlewareListener;
use VGMdb\Component\Routing\RequestContext;
use VGMdb\Component\Routing\Matcher\RedirectableUrlMatcher;
use VGMdb\Component\Silex\ResourceProviderInterface;
use VGMdb\Component\Silex\ResourceLocator;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;
use Silex\LazyUrlMatcher;
use Silex\EventListener\LocaleListener;
use Silex\EventListener\ConverterListener;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\RouteCollection;

/**
 * The VGMdb application class. Extends the Silex framework with custom methods.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Application extends BaseApplication
{
    public static $isBooting = false;
    protected $readonly = array();

    /**
     * Constructor.
     */
    public function __construct(array $values = array())
    {
        // we don't pass $values into the parent constructor; we'll handle it ourselves
        parent::__construct();

        self::$isBooting = false;

        $app = $this;

        $this['name'] = '';

        // replace the default exception handler
        $this['exception_handler'] = $this->share(function ($app) {
            return new ExceptionListener($app['debug'], $app['logger']);
        });

        $app['resource_locator'] = $app->share(function ($app) {
            $providers = array();
            foreach ($app->getProviders() as $provider) {
                if ($provider instanceof ResourceProviderInterface && $provider->isActive()) {
                    $providers[] = $provider;
                }
            }

            $locator = new ResourceLocator();
            $locator->initialize($providers);

            return $locator;
        });

        // replace the controller factory
        $this['controllers_factory'] = function () use ($app) {
            return new ControllerCollection($app['route_factory'], $app['debug']);
        };

        $this['controller_parser'] = $this->share(function ($app) {
            return new ControllerNameParser($app['resource_locator']);
        });

        // replace the controller resolver
        $this['resolver'] = $this->share(function ($app) {
            $resolver = new ControllerResolver($app, $app['controller_parser'], $app['logger']);
            $resolver->setEmptyController($app['resolver.empty_controller']);

            return $resolver;
        });

        $this['resolver.empty_controller'] = $this->protect(function (Request $request) {
            return $request->attributes->all();
        });

        // replace the request context
        $this['request_context'] = $this->share(function ($app) {
            $context = new RequestContext(null, null, null, null, $app['request.http_port'], $app['request.https_port']);
            if (class_exists('Mobile_Detect')) {
                $context->setMobileDetector(new \Mobile_Detect());
            }
            $context->setAppName($app['name']);
            $context->setEnvironment($app['env']);
            $context->setDebug($app['debug']);

            return $context;
        });

        // replace the redirectable url matcher
        $this['url_matcher'] = $this->share(function ($app) {
            return new RedirectableUrlMatcher($app['routes'], $app['request_context']);
        });

        $this['dispatcher'] = $this->share(function () use ($app) {
            $dispatcher = new $app['dispatcher_class']();

            $urlMatcher = new LazyUrlMatcher(function () use ($app) {
                return $app['url_matcher'];
            });
            $dispatcher->addSubscriber(new RouterListener($urlMatcher, $app['request_context'], $app['logger']));
            $dispatcher->addSubscriber(new LocaleListener($app, $urlMatcher));
            if (isset($app['exception_handler'])) {
                $dispatcher->addSubscriber($app['exception_handler']);
            }
            $dispatcher->addSubscriber(new ResponseListener($app['charset']));
            $dispatcher->addSubscriber(new MiddlewareListener($app));
            $dispatcher->addSubscriber(new ConverterListener($app['routes']));

            return $dispatcher;
        });

        $values = array_replace(array(
            'debug' => false,
            'env' => 'prod',
            'name' => 'app',
            'base_dir' => getcwd(),
            'cache_dir' => getcwd() . '/cache',
            'log_dir' => getcwd() . '/logs'
        ), $values);

        foreach ($values as $key => $value) {
            $this->readonly($key, $value);
        }
    }

    /**
     * Returns array of Service Providers.
     *
     * @return ServiceProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
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
    public function mount($prefix, $controllers)
    {
        if ($controllers instanceof ControllerProviderInterface) {
            $controllers = $controllers->connect($this);
        }

        if ($controllers instanceof RouteCollection) {
            $controllers->addPrefix($prefix);
        } elseif ($controllers instanceof ControllerCollection) {
            $controllers = $controllers->flush($prefix);
        }

        if (!$controllers instanceof RouteCollection) {
            throw new \LogicException('The "mount" method takes either a RouteCollection, ControllerCollection or ControllerProviderInterface instance.');
        }

        $this['routes']->addCollection($controllers);
    }

    /**
     * {@inheritdoc}
     */
    public static function share(\Closure $callable)
    {
        return function ($c) use ($callable) {
            static $object;

            if (!Application::$isBooting) {
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
    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        parent::register($provider, $values);

        if ($provider instanceof ResourceProviderInterface && $provider->isAutoload()) {
            $provider->load($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        self::$isBooting = true;

        parent::boot();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($id, $value)
    {
        if (isset($this->readonly[$id]) && parent::offsetExists($id)) {
            throw new \RuntimeException(sprintf('Identifier "%s" is readonly.', $id));
        }

        parent::offsetSet($id, $value);
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
