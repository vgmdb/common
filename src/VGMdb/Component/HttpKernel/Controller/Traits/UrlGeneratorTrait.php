<?php

namespace VGMdb\Component\HttpKernel\Controller\Traits;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * UrlGenerator trait.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
trait UrlGeneratorTrait
{
    /**
     * Generates a URL from the given parameters.
     *
     * @param string         $route         The name of the route
     * @param mixed          $parameters    An array of parameters
     * @param Boolean|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    public function url($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->app['router']->generate($route, $parameters, $referenceType);
    }
}
