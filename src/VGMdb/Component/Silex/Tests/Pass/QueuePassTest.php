<?php

namespace VGMdb\Component\Silex\Tests\Pass;

use VGMdb\Component\Silex\Loader\Pass\QueuePass;

/**
 * QueuePass test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class QueuePassTest extends \PHPUnit_Framework_TestCase
{
    private $queuePass;

    protected function setUp()
    {
        $this->queuePass = new QueuePass();
    }

    public function testProcess_givenEmptyConfig_returnsEmpty()
    {
        $config = $this->queuePass->process(array(
            'foo' => 'bar'
        ));

        $this->assertEquals(array(
            'foo' => 'bar'
        ), $config);
    }

    public function testProcess_stripsQueueConfig()
    {
        $config = $this->queuePass->process(array(
            'services' => array(),
            'app.queue' => array(
                'foo' => 'bar'
            ),
            'app.queues' => array(
                'foo' => 'bar'
            )
        ));

        $this->assertEquals(array(
            'services' => array()
        ), $config);
    }
}
