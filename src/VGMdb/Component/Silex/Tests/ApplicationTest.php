<?php

namespace VGMdb\Component\Silex\Tests;

use VGMdb\Component\Silex\Application;

/**
 * Application test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    protected function setUp()
    {
        $this->app = new Application();
    }

    protected function tearDown() {}

    /**
     * @expectedException \RuntimeException
     */
    public function testApplicationInstantiateServiceBeforeBootShouldThrowException()
    {
        $dispatcher = $this->app['dispatcher'];
    }

    public function testApplicationOverridesExceptionListenerClass()
    {
        $this->app->boot();

        $expected = 'VGMdb\\Component\\HttpKernel\\EventListener\\ExceptionListener';
        $actual = get_class($this->app['exception_handler']);
        $this->assertSame($expected, $actual);
    }

    public function testApplicationOverridesControllerCollectionClass()
    {
        $this->app->boot();

        $expected = 'VGMdb\\Component\\HttpKernel\\Controller\\ControllerCollection';
        $actual = get_class($this->app['controllers_factory']);
        $this->assertSame($expected, $actual);
    }

    public function testApplicationOverridesControllerResolverClass()
    {
        $this->app->boot();

        $expected = 'VGMdb\\Component\\HttpKernel\\Controller\\ControllerResolver';
        $actual = get_class($this->app['resolver']);
        $this->assertSame($expected, $actual);
    }

    public function testApplicationOverridesRequestContextClass()
    {
        $this->app->boot();

        $expected = 'VGMdb\\Component\\Routing\\RequestContext';
        $actual = get_class($this->app['request_context']);
        $this->assertSame($expected, $actual);
    }

    public function testApplicationOverridesUrlMatcherClass()
    {
        $this->app->boot();

        $expected = 'VGMdb\\Component\\Routing\\Matcher\\RedirectableUrlMatcher';
        $actual = get_class($this->app['url_matcher']);
        $this->assertSame($expected, $actual);
    }

    public function testApplicationSupportsLayout()
    {
        $this->app->boot();

        $view = $this->getMockBuilder('VGMdb\\Component\\View\\View')->disableOriginalConstructor()->getMock();
        $this->app->layout($view);
        $controller = $this->app->match('/foo', function () {});
        $expected = $view;
        $actual = $controller->getRoute()->getDefault('_layout');
        $this->assertSame($expected, $actual);
    }

    public function testApplicationSupportsPatch()
    {
        $this->app->boot();

        $controller = $this->app->patch('/foo', function () {});
        $expected = 'PATCH';
        $actual = $controller->getRoute()->getRequirement('_method');
        $this->assertSame($expected, $actual);
    }

    public function testApplicationSupportsReadonly()
    {
        $this->app->readonly('foo', 'bar');
        $expected = 'bar';
        $actual = $this->app['foo'];
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testApplicationSetReadonlyValueShouldThrowException()
    {
        $this->app->readonly('foo', 'bar');
        $this->app['foo'] = 'baz';
    }

    public function testApplicationMountsRouteCollection()
    {
        $this->app->boot();

        $route = $this->getMockBuilder('Silex\\Route')
                      ->disableOriginalConstructor()
                      ->getMock();

        $routeCollection = $this->getMockBuilder('Symfony\\Component\\Routing\\RouteCollection')
                                ->disableOriginalConstructor()
                                ->getMock();

        $routeCollection->expects($this->any())
                        ->method('all')
                        ->will($this->returnValue(array('foo' => $route)));

        $routeCollection->expects($this->any())
                        ->method('getResources')
                        ->will($this->returnValue(array()));

        $this->app->mount('', $routeCollection);

        $routes = $this->app['routes'];
        $this->assertInstanceOf('Symfony\\Component\\Routing\\RouteCollection', $routes);
        $this->assertSame(1, count($routes->all()));
    }
}
