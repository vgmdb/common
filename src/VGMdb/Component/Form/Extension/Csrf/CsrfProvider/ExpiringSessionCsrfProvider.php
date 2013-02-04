<?php

namespace VGMdb\Component\Form\Extension\Csrf\CsrfProvider;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * This provider expires a token after a time limit.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class ExpiringSessionCsrfProvider extends SessionCsrfProvider
{
    /**
     * The expiry time limit.
     *
     * @var integer
     */
    protected $ttl;

    /**
     * Initializes the provider with a Session object, secret value and expiry time.
     *
     * @param Session $session The user session
     * @param string  $secret  A secret value included in the CSRF token
     * @param integer $ttl     Expiry time.
     */
    public function __construct(Session $session, $secret, $ttl = 30)
    {
        parent::__construct($session, $secret);

        $this->ttl = intval($ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function generateCsrfToken($intention, $previous = false)
    {
        $timeshift = $previous ? $this->ttl : 0;
        $timestamp = $this->generateTimestamp($timeshift);

        return sha1($this->secret.$intention.$timestamp.$this->getSessionId());
    }

    /**
     * Generates a timestamp that changes every $ttl seconds.
     *
     * @param integer $timeshift How much to shift the current timestamp backwards.
     *
     * @return integer
     */
    public function generateTimestamp($timeshift = 0)
    {
        return ceil((time() - intval($timeshift)) / $this->ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function isCsrfTokenValid($intention, $token)
    {
        return $token === $this->generateCsrfToken($intention) ||
               $token === $this->generateCsrfToken($intention, true);
    }
}
