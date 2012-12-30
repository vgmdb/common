<?php

namespace VGMdb;

use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher as BaseRedirectableUrlMatcher;

/**
 * Implements the RedirectableUrlMatcherInterface for Silex.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RedirectableUrlMatcher extends BaseRedirectableUrlMatcher
{
    /**
     * Redirects the user to another URL.
     *
     * @param string $path   The path info to redirect to.
     * @param string $route  The route that matched
     * @param string $scheme The URL scheme (null to keep the current one)
     *
     * @return array An array of parameters
     */
    public function redirect($path, $route, $scheme = null)
    {
        return array(
            '_controller' => 'VGMdb\\RedirectController::urlRedirect',
            'path'        => $path,
            'permanent'   => true,
            'scheme'      => $scheme,
            'httpPort'    => $this->context->getHttpPort(),
            'httpsPort'   => $this->context->getHttpsPort(),
            '_route'      => $route,
        );
    }
}
