<?php

namespace VGMdb\Component\OAuthServer\Model;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
abstract class TokenManager implements TokenManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createToken()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     * {@inheritdoc}
     */
    public function findTokenByToken($token)
    {
        return $this->findTokenBy(array('token' => $token));
    }
}
