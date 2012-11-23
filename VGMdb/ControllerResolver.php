<?php

namespace VGMdb;

use Silex\ControllerResolver as BaseControllerResolver;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attaches the application context to controllers.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ControllerResolver extends BaseControllerResolver
{
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

        if (is_array($controller) || (is_object($controller) && method_exists($controller, '__invoke'))) {
            return $controller;
        }

        if (false === strpos($controller, ':')) {
            if (method_exists($controller, '__invoke')) {
                return new $controller;
            } elseif (function_exists($controller)) {
                return $controller;
            }
        }

        list($controller, $method) = $this->createController($controller, $request);

        if (!method_exists($controller, $method)) {
            throw new \InvalidArgumentException(sprintf('Method "%s::%s" does not exist.', get_class($controller), $method));
        }

        return array($controller, $method);
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
        if (false === strpos($controller, '::')) {
            $action = $request ? $request->attributes->get('_action') : '';
            $action = $action ?: 'index';
            list($class, $method) = array($controller, $action . 'Action');
        } else {
            list($class, $method) = explode('::', $controller, 2);
            $method .= 'Action';
        }

        if (isset($this->app['controller.namespace'])) {
            $class = $this->app['controller.namespace'] . '\\' . $class;
        }

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
