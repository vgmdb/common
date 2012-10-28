<?php

namespace VGMdb\Component\User\Model;

/**
 * User object
 */
class User extends AbstractUser
{
    protected $auth_providers;

    public function __construct()
    {
        $this->enabled = false;
        $this->locked = false;
        $this->expired = false;
        $this->roles = array();
        $this->auth_providers = array();
        $this->credentials_expired = false;
    }

    public function getAuthProviders()
    {
        return $this->auth_providers;
    }
}
