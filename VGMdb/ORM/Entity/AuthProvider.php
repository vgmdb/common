<?php

namespace VGMdb\ORM\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VGMdb\ORM\Entity\AuthProvider
 *
 * @ORM\Entity(repositoryClass="VGMdb\ORM\Repository\AuthProviderRepository")
 * @ORM\Table(name="auth_provider", indexes={@ORM\Index(name="user_id_idx", columns={"user_id"})})
 */
class AuthProvider
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $provider;

    /**
     * @ORM\Column(type="bigint")
     */
    protected $provider_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $access_token;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="authProviders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $user;

    public function __construct()
    {
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \VGMdb\ORM\Entity\AuthProvider
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
     * @return \VGMdb\ORM\Entity\AuthProvider
     */
    public function setProvider($provider)
    {
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
     * @return \VGMdb\ORM\Entity\AuthProvider
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
     * @return \VGMdb\ORM\Entity\AuthProvider
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
     * @return \VGMdb\ORM\Entity\AuthProvider
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
     * Set User entity (many to one).
     *
     * @param \VGMdb\ORM\Entity\User $user
     * @return \VGMdb\ORM\Entity\AuthProvider
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get User entity (many to one).
     *
     * @return \VGMdb\ORM\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function __sleep()
    {
        return array('id', 'user_id', 'provider', 'provider_id', 'access_token', 'enabled');
    }
}