<?php

namespace VGMdb\Component\User\Tests;

use VGMdb\Component\User\Util\EmailCanonicalizer;

/**
 * Canonicalizer test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class CanonicalizerTest extends \PHPUnit_Framework_TestCase
{
    private $canonicalizer;

    protected function setUp()
    {
        $this->canonicalizer = new EmailCanonicalizer();
    }

    protected function tearDown() {}

    public function testCanonicalizeEmailWithPlusSign()
    {
        $input = 'BigBlah+test+plus@email@Example.com';
        $expected = 'bigblah@example.com';
        $actual = $this->canonicalizer->canonicalize($input);
        $this->assertSame($expected, $actual);
    }
}
