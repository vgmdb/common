<?php

namespace VGMdb\Component\HttpKernel\Tests;

use VGMdb\Component\HttpKernel\Controller\Controller;
use Silex\Route;

/**
 * Controller test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    private $controller;

    protected function setUp()
    {
        $this->controller = new Controller(new Route());
    }

    protected function tearDown() {}

    public function testControllerSupportsLayout()
    {
        $view = $this->getMockBuilder('VGMdb\\Component\\View\\View')->disableOriginalConstructor()->getMock();
        $this->controller->layout($view);
        $expected = $view;
        $actual = $this->controller->getRoute()->getDefault('_layout');
        $this->assertSame($expected, $actual);
    }
}
