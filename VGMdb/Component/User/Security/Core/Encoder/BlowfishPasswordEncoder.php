<?php

namespace VGMdb\Component\User\Security\Core\Encoder;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

/**
 * @brief       Uses the bcrypt algorithm to encode passwords.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class BlowfishPasswordEncoder extends BasePasswordEncoder
{
    private $work_factor;

    /**
     * Constructor.
     *
     * @param integer $work_factor Work factor. Each increment doubles the time needed for encoding.
     */
    public function __construct($work_factor = 15)
    {
        if (version_compare(PHP_VERSION, '5.3') < 0) {
            throw new \RuntimeException('Bcrypt requires PHP 5.3 or above.');
        }
        if (!function_exists('openssl_random_pseudo_bytes')) {
            throw new \RuntimeException('Bcrypt requires the openssl PHP extension.');
        }

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
        // ignore the salt!
        $encoded = sprintf(
            '$2a$%s$%s',
            str_pad($this->work_factor, 2, '0', STR_PAD_LEFT),
            substr(strtr(base64_encode(openssl_random_pseudo_bytes(16)), '+', '.'), 0, 22)
        );

        return crypt($raw, $encoded);
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
