<?php

namespace VGMdb\Component\Routing\Matcher;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * RedirectableUrlMatcher that wraps a compiled route cache.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RedirectableProxyUrlMatcher implements UrlMatcherInterface, RedirectableUrlMatcherInterface
{
    protected $matcher;

    public function __construct(UrlMatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->matcher->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->matcher->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        try {
            $parameters = $this->matcher->match($pathinfo);
        } catch (ResourceNotFoundException $e) {
            if ('/' !== substr($pathinfo, -1) || !in_array($this->matcher->getContext()->getMethod(), array('HEAD', 'GET'))) {
                throw $e;
            }

            try {
                $this->matcher->match(rtrim($pathinfo, '/'));

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
            '_controller' => 'VGMdb\\Component\\HttpKernel\\Controller\\RedirectController::urlRedirectAction',
            'path'        => $path,
            'permanent'   => true,
            'scheme'      => $scheme,
            'httpPort'    => $this->matcher->getContext()->getHttpPort(),
            'httpsPort'   => $this->matcher->getContext()->getHttpsPort(),
            '_route'      => $route,
        );
    }
}
