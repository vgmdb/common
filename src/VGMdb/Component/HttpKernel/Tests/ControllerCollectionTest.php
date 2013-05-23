<?php

namespace VGMdb\Component\HttpKernel\Tests;

use VGMdb\Component\HttpKernel\Controller\ControllerCollection;
use Silex\Route;

/**
 * ControllerCollection test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ControllerCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $controllers;

    protected function setUp()
    {
        $this->controllers = new ControllerCollection(new Route());
    }

    protected function tearDown() {}

    public function testControllerCollectionOverridesControllerClass()
    {
        $controller = $this->controllers->match('/foo', function () {});
        $expected = 'VGMdb\\Component\\HttpKernel\\Controller\\Controller';
        $actual = get_class($controller);
        $this->assertSame($expected, $actual);
    }

    public function testControllerCollectionSupportsPatch()
    {
        $controller = $this->controllers->patch('/foo', function () {});
        $expected = 'PATCH';
        $actual = $controller->getRoute()->getRequirement('_method');
        $this->assertSame($expected, $actual);
    }
}
