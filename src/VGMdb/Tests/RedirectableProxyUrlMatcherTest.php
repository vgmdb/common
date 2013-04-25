<?php

namespace VGMdb\Tests;

use VGMdb\Component\Routing\Matcher\RedirectableProxyUrlMatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

/**
 * RedirectableProxyUrlMatcher test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RedirectableProxyUrlMatcherTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {}

    protected function tearDown() {}

    public function testRedirectWhenPathEndsWithSlash()
    {
        $routes = new RouteCollection();
        $routes->add('foo', new Route('/foo'));
        $context = new RequestContext();
        $context->setMethod('GET');
        $matcher = new RedirectableProxyUrlMatcher(new UrlMatcher($routes, $context));

        $expected = array(
            '_controller' => 'VGMdb\\Component\\HttpKernel\\Controller\\RedirectController::urlRedirectAction',
            'path'        => '/foo',
            'permanent'   => true,
            'scheme'      => null,
            'httpPort'    => 80,
            'httpsPort'   => 443,
            '_route'      => null,
        );
        $actual = $matcher->match('/foo/');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testRedirectWhenPathEndsWithSlashForNonSafeMethod()
    {
        $routes = new RouteCollection();
        $routes->add('foo', new Route('/foo'));
        $context = new RequestContext();
        $context->setMethod('POST');
        $matcher = new RedirectableProxyUrlMatcher(new UrlMatcher($routes, $context));
        $matcher->match('/foo/');
    }
}
