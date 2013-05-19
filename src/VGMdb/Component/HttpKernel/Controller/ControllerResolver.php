<?php

namespace VGMdb\Component\HttpKernel\Controller;

use Silex\Application;
use Silex\ControllerResolver as BaseControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

/**
 * Allows the controller resolver to recognize custom verbs and methods.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ControllerResolver extends BaseControllerResolver
{
    protected $parser;
    protected $logger;

    /**
     * Constructor.
     *
     * @param Application          $app    An Application instance
     * @param ControllerNameParser $parser A ControllerNameParser instance
     * @param LoggerInterface      $logger A LoggerInterface instance
     */
    public function __construct(Application $app, ControllerNameParser $parser, LoggerInterface $logger = null)
    {
        $this->parser = $parser;
        $this->logger = $logger;

        parent::__construct($app, $logger);
    }

    /**
     * {@inheritDoc}
     */
    public function getController(Request $request)
    {
        if (!$controller = $request->attributes->get('_controller')) {
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
                    if ($controller instanceof AbstractController) {
                        $controller->setContainer($this->app);
                    }

                    return array($controller, $method.'Action');
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
        if ($controller instanceof AbstractController) {
            $controller->setContainer($this->app);
        }

        return array($controller, $method);
    }
}
