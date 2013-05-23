<?php

namespace VGMdb\Component\Silex\Tests;

use VGMdb\TraceableApplication;

/**
 * TraceableApplication test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TraceableApplicationTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    protected function setUp()
    {
        $this->app = new TraceableApplication();
    }

    protected function tearDown() {}


    public function testApplicationRecordsStartTime()
    {
        $actual = $this->app->getStartTime();
        $this->assertInternalType('float', $actual);
        $this->assertGreaterThan(0, $actual);
    }
}
