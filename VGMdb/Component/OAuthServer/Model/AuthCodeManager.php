<?php

namespace VGMdb\Component\OAuthServer\Model;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
abstract class AuthCodeManager implements AuthCodeManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createAuthCode()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     * {@inheritdoc}
     */
    public function findAuthCodeByToken($token)
    {
        return $this->findAuthCodeBy(array('token' => $token));
    }
}
