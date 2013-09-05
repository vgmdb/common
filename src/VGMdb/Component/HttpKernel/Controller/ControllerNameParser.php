<?php

namespace VGMdb\Component\HttpKernel\Controller;

use VGMdb\Component\Silex\ResourceLocatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * ControllerNameParser converts controller from the short notation a:b:c
 * (BlogService:Api/Post:index) to a fully-qualified class::method string
 * (BlogService\Controller\Api\PostController::indexAction).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ControllerNameParser
{
    protected $locator;
    protected $request;

    /**
     * Constructor.
     *
     * @param ResourceLocatorInterface $locator A ResourceLocator instance
     */
    public function __construct(ResourceLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Sets the current request.
     *
     * @param Request $request A Request instance
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Converts a short notation a:b:c to a class::method.
     *
     * @param string $controller A short notation controller (a:b:c)
     *
     * @return string A string with class::method
     *
     * @throws \InvalidArgumentException when the specified provider is not enabled
     *                                   or the controller cannot be found
     */
    public function parse($controller)
    {
        if (3 != count($parts = explode(':', $controller))) {
            throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid a:b:c controller string.', $controller));
        }

        list($provider, $controller, $action) = $parts;
        $controller = str_replace('/', '\\', $controller);
        $providers = array();

        // this throws an exception if there is no such provider
        foreach ($this->locator->getProvider($provider, false) as $prov) {
            $try = $prov->getNamespace().'\\Controller\\'.$controller.'Controller';
            if (class_exists($try)) {
                if (null !== $this->request) {
                    $this->request->attributes->set('_provider', $prov->getName());
                }

                return $try.'::'.$action.'Action';
            }

            $providers[] = $prov->getName();
            $msg = sprintf('Unable to find controller "%s:%s" - class "%s" does not exist.', $provider, $controller, $try);
        }

        if (count($providers) > 1) {
            $msg = sprintf('Unable to find controller "%s:%s" in providers %s.', $provider, $controller, implode(', ', $providers));
        }

        throw new \InvalidArgumentException($msg);
    }
}
