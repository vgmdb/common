<?php

namespace VGMdb\Component\HttpKernel\Controller;

use Silex\Application;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

/**
 * Allows the controller resolver to recognize custom verbs and methods.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ControllerResolver extends BaseControllerResolver
{
    protected $app;
    protected $parser;
    protected $logger;
    protected $emptyController;

    /**
     * Constructor.
     *
     * @param Application          $app    An Application instance
     * @param ControllerNameParser $parser A ControllerNameParser instance
     * @param LoggerInterface      $logger A LoggerInterface instance
     */
    public function __construct(Application $app, ControllerNameParser $parser, LoggerInterface $logger = null)
    {
        $this->app = $app;
        $this->parser = $parser;
        $this->logger = $logger;
        $this->emptyController = null;

        parent::__construct($logger);
    }

    /**
     * Adds Application as a valid argument for controllers.
     *
     * @param Request $request
     * @param mixed   $controller
     * @param array   $parameters
     *
     * @return array
     */
    protected function doGetArguments(Request $request, $controller, array $parameters)
    {
        foreach ($parameters as $param) {
            if ($param->getClass() && $param->getClass()->isInstance($this->app)) {
                $request->attributes->set($param->getName(), $this->app);

                break;
            }
        }

        return parent::doGetArguments($request, $controller, $parameters);
    }

    /**
     * Set the controller to be used if none is specified in the route definition.
     *
     * @param mixed $controller
     */
    public function setEmptyController($controller)
    {
        $this->emptyController = $controller;
    }

    /**
     * {@inheritDoc}
     */
    public function getController(Request $request)
    {
        if (!$controller = $request->attributes->get('_controller')) {
            $controller = $this->emptyController;
        }

        if (!$controller) {
            if (null !== $this->logger) {
                $this->logger->warn('Unable to look for the controller as the "_controller" parameter is missing');
            }

            return false;
        }

        if (is_array($controller) || $controller instanceof \Closure || (is_object($controller) && method_exists($controller, '__invoke'))) {
            return $controller;
        }

        if (false === strpos($controller, ':')) {
            if (method_exists($controller, '__invoke')) {
                return new $controller;
            } elseif (function_exists($controller)) {
                return $controller;
            }
        }

        $this->parser->setRequest($request);
        $callable = $this->createController($controller);

        if ($callable instanceof \Closure) {
            return $callable;
        }

        list($controller, $method) = $callable;

        $verbMethod = strtolower($request->getMethod()) . ucfirst($method);
        if (method_exists($controller, $verbMethod)) {
            return array($controller, $verbMethod);
        }
        if (method_exists($controller, $method)) {
            return array($controller, $method);
        }

        throw new \InvalidArgumentException(
            sprintf('Could not resolve "%s:%s" or "%s:%s".',
                get_class($controller),
                $verbMethod,
                get_class($controller),
                $method
            )
        );
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string $controller A Controller string
     *
     * @return mixed A PHP callable
     *
     * @throws \LogicException           When the name could not be parsed
     * @throws \InvalidArgumentException When the controller class does not exist
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            $count = substr_count($controller, ':');
            if (2 == $count) {
                // controller in the a:b:c notation then
                $controller = $this->parser->parse($controller);
            } elseif (1 == $count) {
                // controller in the service:method notation
                list($service, $method) = explode(':', $controller, 2);

                if (isset($this->app[$service])) {
                    $controller = $this->app[$service];
                    if (is_string($controller)) {
                        $controller = new $controller();
                    }

                    return array($this->initializeController($controller), $method.'Action');
                }

                throw new \LogicException(sprintf('Unable to parse the controller service "%s".', $service));
            } else {
                if (isset($this->app[$controller])) {
                    $controller = $this->app[$controller];

                    if ($controller instanceof \Closure) {
                        return $controller;
                    }
                }

                throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
            }
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $controller = new $class();

        return array($this->initializeController($controller), $method);
    }

    /**
     * Initializes a controller object based on its traits.
     *
     * @param object $controller A Controller instance
     *
     * @return object
     */
    protected function initializeController($controller)
    {
        $traits = array();
        $class = get_class($controller);
        do {
            $traits = array_merge(class_uses($class), $traits);
        } while ($class = get_parent_class($class));

        if (array_key_exists('VGMdb\\Component\\HttpKernel\\Controller\\Traits\\ContainerAwareTrait', $traits)) {
            $controller->setContainer($this->app);

            return $controller;
        }

        return $controller;
    }
}
