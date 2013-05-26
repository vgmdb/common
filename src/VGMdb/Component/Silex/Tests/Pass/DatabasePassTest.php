<?php

namespace VGMdb\Component\Silex\Tests\Pass;

use VGMdb\Component\Silex\Loader\Pass\DatabasePass;

/**
 * DatabasePass test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DatabasePassTest extends \PHPUnit_Framework_TestCase
{
    private $databasePass;

    protected function setUp()
    {
        $this->databasePass = new DatabasePass();
    }

    public function testProcess_givenEmptyConfig_returnsEmpty()
    {
        $config = $this->databasePass->process(array(
            'foo' => 'bar'
        ));

        $this->assertEquals(array(
            'foo' => 'bar'
        ), $config);
    }

    public function testProcess_stripsDatabaseConfig()
    {
        $config = $this->databasePass->process(array(
            'services' => array(),
            'app.database' => array(
                'foo' => 'bar'
            ),
            'app.databases' => array(
                'foo' => 'bar'
            )
        ));

        $this->assertEquals(array(
            'services' => array()
        ), $config);
    }
}
