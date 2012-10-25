<?php

namespace VGMdb\Component\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * @brief       Token for OAuth Authentication requests.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class OpauthToken extends AbstractToken
{
    public $provider;
    public $providerId;
    private $providerKey;

    public function __construct($providerKey, array $roles = array())
    {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->providerKey = $providerKey;

        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return '';
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function serialize()
    {
        return serialize(array($this->provider, $this->providerId, $this->providerKey, parent::serialize()));
    }

    public function unserialize($str)
    {
        list($this->provider, $this->providerId, $this->providerKey, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }
}