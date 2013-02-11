<?php

namespace VGMdb\Component\Routing\Matcher;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher as BaseRedirectableUrlMatcher;

/**
 * Changes the RedirectableUrlMatcher to remove trailing slashes.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RedirectableUrlMatcher extends BaseRedirectableUrlMatcher
{
    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        try {
            $parameters = parent::match($pathinfo);
        } catch (ResourceNotFoundException $e) {
            if ('/' !== substr($pathinfo, -1) || !in_array($this->context->getMethod(), array('HEAD', 'GET'))) {
                throw $e;
            }

            try {
                parent::match(rtrim($pathinfo, '/'));

                return $this->redirect(rtrim($pathinfo, '/'), null);
            } catch (ResourceNotFoundException $e2) {
                throw $e;
            }
        }

        return $parameters;
    }

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
            '_controller' => 'VGMdb\\Component\\HttpKernel\\Controller\\RedirectController:urlRedirect',
            'path'        => $path,
            'permanent'   => true,
            'scheme'      => $scheme,
            'httpPort'    => $this->context->getHttpPort(),
            'httpsPort'   => $this->context->getHttpsPort(),
            '_route'      => $route,
        );
    }
}
