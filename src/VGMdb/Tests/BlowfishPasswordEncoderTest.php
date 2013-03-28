<?php

namespace VGMdb\Tests;

use VGMdb\Component\User\Security\Core\Encoder\BlowfishPasswordEncoder;

/**
 * Bcrypt encoder test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class BlowfishPasswordEncoderTest extends \PHPUnit_Framework_TestCase
{
    const PASSWORD = 'password';
    const BYTES = '0123456789abcdef';
    const VALID_COST = 4;

    const SECURE_RANDOM_INTERFACE = 'Symfony\\Component\\Security\\Core\\Util\\SecureRandomInterface';

    private $secureRandom;

    protected function setUp()
    {
        $this->secureRandom = $this->getMock(self::SECURE_RANDOM_INTERFACE);

        $this->secureRandom
             ->expects($this->any())
             ->method('nextBytes')
             ->will($this->returnValue(self::BYTES));
    }

    protected function tearDown() {}

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithWorkFactorBelowRangeWillThrowException()
    {
        $encoder = new BlowfishPasswordEncoder($this->secureRandom, 3);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithWorkFactorAboveRangeWillThrowException()
    {
        $encoder = new BlowfishPasswordEncoder($this->secureRandom, 32);
    }

    public function testHashedPasswordLength()
    {
        $encoder = new BlowfishPasswordEncoder($this->secureRandom, self::VALID_COST);
        $expected = 60;
        $actual = strlen($encoder->encodePassword(self::PASSWORD, null));
        $this->assertSame($expected, $actual);
    }

    public function testHashedPasswordValidation()
    {
        $encoder = new BlowfishPasswordEncoder($this->secureRandom, self::VALID_COST);
        $hash = $encoder->encodePassword(self::PASSWORD, null);
        $this->assertTrue($encoder->isPasswordValid($hash, self::PASSWORD, null));
        $this->assertFalse($encoder->isPasswordValid($hash, 'hunter2', null));
    }

    public function testKnownPasswordValidation()
    {
        $encoder = new BlowfishPasswordEncoder($this->secureRandom, self::VALID_COST);
        $hash = (version_compare(PHP_VERSION, '5.3.7') >= 0 ? '$2y$' : '$2a$')
            . '04$ABCDEFGHIJKLMNOPQRSTU.uTmwd4KMSHxbUsG7bng8x7YdA0PM1iq';
        $this->assertTrue($encoder->isPasswordValid($hash, self::PASSWORD, null));
    }
}
