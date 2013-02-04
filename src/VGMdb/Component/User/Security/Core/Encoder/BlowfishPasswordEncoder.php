<?php

namespace VGMdb\Component\User\Security\Core\Encoder;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;

/**
 * Uses the bcrypt algorithm to encode passwords. Parts referenced from Zend\Crypt\Password\Bcrypt
 *
 * @link https://gist.github.com/1053158
 * @link http://blog.ircmaxell.com/2012/12/seven-ways-to-screw-up-bcrypt.html
 * @link https://gist.github.com/3237572
 *
 * @author Marco Arment <me@marco.org>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class BlowfishPasswordEncoder extends BasePasswordEncoder
{
    private $secureRandom;
    private $work_factor;

    /**
     * Constructor.
     *
     * @param integer $work_factor Work factor. Each increment doubles the time needed for encoding.
     */
    public function __construct(SecureRandomInterface $secureRandom, $work_factor = 15)
    {
        if (version_compare(PHP_VERSION, '5.3') < 0) {
            throw new \RuntimeException('Bcrypt requires PHP 5.3 or above.');
        }

        $this->secureRandom = $secureRandom;

        $work_factor = intval($work_factor);

        if ($work_factor < 4 || $work_factor > 31) {
            throw new \InvalidArgumentException('Bcrypt requires a work factor from 4 to 31.');
        }

        $this->work_factor = $work_factor;
    }

    /**
     * {@inheritdoc}
     */
    public function encodePassword($raw, $salt)
    {
        // ignore the salt parameter, we'll generate a random one ourselves

        /**
         * Security flaw in PHP 5.3.7
         * @see http://php.net/security/crypt_blowfish.php
         */
        $prefix = (version_compare(PHP_VERSION, '5.3.7') >= 0) ? '2y' : '2a';

        $encoded = sprintf(
            '$%s$%02d$%s',
            $prefix,
            $this->work_factor,
            substr(strtr(base64_encode($this->generateRandomBytes(16)), '+', '.'), 0, 22)
        );

        $hash = crypt($raw, $encoded);

        if (!is_string($hash) || strlen($hash) <= 13) {
            throw new \RuntimeException('Error during bcrypt generation');
        }

        return $hash;
    }

    private function generateRandomBytes($length = 16)
    {
        $randomBytes = '';
        $length = abs(intval($length));

        if (@is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb')) !== false) {
            $randomBytes = fread($fh, $length);
            fclose($fh);
        } elseif (function_exists('mcrypt_create_iv')) {
            $randomBytes = mcrypt_create_iv($length, \MCRYPT_DEV_URANDOM);
        } elseif (function_exists('openssl_random_pseudo_bytes') && (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) {
            $randomBytes = openssl_random_pseudo_bytes($length, $strong);
            if ($strong !== true) {
                $randomBytes = '';
            }
        }

        if (!$randomBytes) {
            $randomBytes = $this->secureRandom->nextBytes($length);
        }

        return $randomBytes;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        // ignore the salt!
        return crypt($raw, $encoded) === $encoded;
    }
}
