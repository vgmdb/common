<?php

namespace VGMdb\Tests;

use VGMdb\Tests\Fixtures\Controllers\TestController;
use VGMdb\Component\HttpKernel\Controller\ControllerResolver;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * ControllerResolver test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ControllerResolverTest extends \PHPUnit_Framework_TestCase
{
    private $resolver;
    private $request;

    protected function setUp()
    {
        $app = new Application(array(
            'namespace' => 'VGMdb\\Tests\\Fixtures'
        ));

        $app['controller.test'] = 'VGMdb\\Tests\\Fixtures\\Controllers\\TestController';
        $app['controller.test.object'] = $app->share(function ($app) {
            return new TestController();
        });
        $app['controller.test.closure'] = $app->protect(function () {});

        $this->resolver = new ControllerResolver($app);

        $this->request = Request::create('/');
    }

    public function testGetController_givenMissingAttribute_shouldReturnFalse()
    {
        $this->assertFalse($this->resolver->getController($this->request), '->getController() returns false when the request has no _controller attribute');
    }

    public function testGetController_givenControllerAttribute_shouldReturnCallableWithDefaultMethod()
    {
        $this->request->attributes->set('_controller', 'Test');
        $controller = $this->resolver->getController($this->request);
        $this->assertInstanceOf('VGMdb\\Tests\\Fixtures\\Controllers\\TestController', $controller[0]);
        $this->assertEquals('indexAction', $controller[1]);
    }

    public function testGetController_givenControllerAndActionAttributes_shouldReturnCallable()
    {
        $this->request->attributes->set('_controller', 'Test');
        $this->request->attributes->set('_action', 'foo');
        $controller = $this->resolver->getController($this->request);
        $this->assertInstanceOf('VGMdb\\Tests\\Fixtures\\Controllers\\TestController', $controller[0]);
        $this->assertEquals('fooAction', $controller[1]);
    }

    public function testGetController_givenControllerAttributeWithInlineMethod_shouldReturnCallable()
    {
        $this->request->attributes->set('_controller', 'Test:foo');
        $controller = $this->resolver->getController($this->request);
        $this->assertInstanceOf('VGMdb\\Tests\\Fixtures\\Controllers\\TestController', $controller[0]);
        $this->assertEquals('fooAction', $controller[1]);
    }

    public function testGetController_givenMethodExistsForGet_shouldReturnCorrectMethod()
    {
        $this->request->attributes->set('_controller', 'Test:bar');
        $controller = $this->resolver->getController($this->request);
        $this->assertEquals('getBarAction', $controller[1]);
    }

    public function testGetController_givenMethodExistsForPost_shouldReturnCorrectMethod()
    {
        $this->request->attributes->set('_controller', 'Test:baz');
        $this->request->setMethod('POST');
        $controller = $this->resolver->getController($this->request);
        $this->assertEquals('postBazAction', $controller[1]);
    }

    public function testGetController_givenMethodMissingForVerb_shouldReturnFallbackMethod()
    {
        $this->request->attributes->set('_controller', 'Test:bar');
        $this->request->setMethod('POST');
        $controller = $this->resolver->getController($this->request);
        $this->assertEquals('barAction', $controller[1]);
    }

    public function testGetController_shouldHandleOptionalSuffixes()
    {
        $this->request->attributes->set('_controller', 'TestController:fooAction');
        $controller = $this->resolver->getController($this->request);
        $this->assertInstanceOf('VGMdb\\Tests\\Fixtures\\Controllers\\TestController', $controller[0]);
        $this->assertEquals('fooAction', $controller[1]);
    }

    public function testGetController_givenFullClassAndMethod_shouldReturnCallable()
    {
        $this->request->attributes->set('_controller', 'VGMdb\\Tests\\Fixtures\\Controllers\\TestController::fooAction');
        $controller = $this->resolver->getController($this->request);
        $this->assertInstanceOf('VGMdb\\Tests\\Fixtures\\Controllers\\TestController', $controller[0]);
        $this->assertEquals('fooAction', $controller[1]);
    }

    public function testGetController_givenScalarServiceIdentifier_shouldReturnCallable()
    {
        $this->request->attributes->set('_controller', 'controller.test:foo');
        $controller = $this->resolver->getController($this->request);
        $this->assertInstanceOf('VGMdb\\Tests\\Fixtures\\Controllers\\TestController', $controller[0]);
        $this->assertEquals('fooAction', $controller[1]);
    }

    public function testGetController_givenObjectServiceIdentifier_shouldReturnCallable()
    {
        $this->request->attributes->set('_controller', 'controller.test.object:foo');
        $controller = $this->resolver->getController($this->request);
        $this->assertInstanceOf('VGMdb\\Tests\\Fixtures\\Controllers\\TestController', $controller[0]);
        $this->assertEquals('fooAction', $controller[1]);
    }

    public function testGetController_givenClosureServiceIdentifier_shouldReturnClosure()
    {
        $this->request->attributes->set('_controller', 'controller.test.closure');
        $controller = $this->resolver->getController($this->request);
        $this->assertInstanceOf('Closure', $controller);
    }

    public function testGetController_givenArray_shouldReturnCallable()
    {
        $this->request->attributes->set('_controller', array($this, 'testGetController_givenArray_shouldReturnCallable'));
        $controller = $this->resolver->getController($this->request);
        $this->assertSame($this, $controller[0]);
        $this->assertEquals('testGetController_givenArray_shouldReturnCallable', $controller[1]);
    }

    public function testGetController_givenClosure_shouldReturnClosure()
    {
        $this->request->attributes->set('_controller', $lambda = function () {});
        $controller = $this->resolver->getController($this->request);
        $this->assertSame($lambda, $controller);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetController_givenInvalidController_shouldThrowException()
    {
        $this->request->attributes->set('_controller', 'foo');
        $this->resolver->getController($this->request);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetController_givenInvalidMethod_shouldThrowException()
    {
        $this->request->attributes->set('_controller', 'Test::baz');
        $this->resolver->getController($this->request);
    }
}
