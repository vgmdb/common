<?php

namespace VGMdb\Component\OAuthServer\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Token for OAuth Authentication requests.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class OAuthToken extends AbstractToken
{
    protected $token;
    private $providerKey;

    public function __construct($providerKey, array $roles = array())
    {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->providerKey = $providerKey;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function getCredentials()
    {
        return $this->token;
    }

    public function serialize()
    {
        return serialize(array($this->token, parent::serialize()));
    }

    public function unserialize($str)
    {
        list($this->token, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }
}
