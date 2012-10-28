<?php

namespace VGMdb\Component\User\Model;

/**
 * AuthProvider object
 */
class AuthProvider implements \Serializable
{
    protected $providers = array(
        'facebook' => 1,
        'twitter'  => 2,
        'google'   => 3
    );

    protected $id;

    protected $user_id;

    /**
     * @var integer
     */
    protected $provider;

    /**
     * @var integer
     */
    protected $provider_id;

    /**
     * @var string
     */
    protected $access_token;

    /**
     * @var Boolean
     */
    protected $enabled;

    public function __construct()
    {
        $this->enabled = true;
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return AuthProvider
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of provider.
     *
     * @param integer $provider
     * @return AuthProvider
     */
    public function setProvider($provider)
    {
        if (!is_numeric($provider)) {
            $provider = intval($this->providers[strtolower($provider)]);
        }

        $this->provider = $provider;

        return $this;
    }

    /**
     * Get the value of provider.
     *
     * @return integer
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set the value of provider_id.
     *
     * @param integer $provider_id
     * @return AuthProvider
     */
    public function setProviderId($provider_id)
    {
        $this->provider_id = $provider_id;

        return $this;
    }

    /**
     * Get the value of provider_id.
     *
     * @return integer
     */
    public function getProviderId()
    {
        return $this->provider_id;
    }

    /**
     * Set the value of access_token.
     *
     * @param string $access_token
     * @return AuthProvider
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;

        return $this;
    }

    /**
     * Get the value of access_token.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Set the value of enabled.
     *
     * @param boolean $enabled
     * @return AuthProvider
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get the value of enabled.
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set User id.
     *
     * @param integer $user_id
     * @return AuthProvider
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get User id.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Serializes the auth provider.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->user_id,
            $this->provider,
            $this->provider_id,
            $this->access_token,
            $this->enabled,
        ));
    }

    /**
     * Unserializes the auth provider.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        list(
            $this->id,
            $this->user_id,
            $this->provider,
            $this->provider_id,
            $this->access_token,
            $this->enabled,
        ) = $data;
    }
}