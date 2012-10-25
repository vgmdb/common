<?php

namespace VGMdb\ORM\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * VGMdb\ORM\Entity\User
 *
 * @ORM\Entity()
 * @ORM\Table(name="user", indexes={@ORM\Index(name="login_idx", columns={"email_canonical", "password"})}, uniqueConstraints={@ORM\UniqueConstraint(name="username_canonical_idx", columns={"username_canonical"})})
 */
class User extends \VGMdb\Component\User\Model\AbstractUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=75)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=75)
     */
    protected $username_canonical;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email_canonical;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $salt;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    protected $password;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $last_login;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $locked;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $expired;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $expires_at;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $confirmation_token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $password_requested_at;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $credentials_expired;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $credentials_expire_at;

    /**
     * @ORM\OneToMany(targetEntity="AuthProvider", mappedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $authProviders;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $roles;

    public function __construct()
    {
        $this->authProviders = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \VGMdb\ORM\Entity\User
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
     * Set the value of username.
     *
     * @param string $username
     * @return \VGMdb\ORM\Entity\User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username_canonical.
     *
     * @param string $username_canonical
     * @return \VGMdb\ORM\Entity\User
     */
    public function setUsernameCanonical($username_canonical)
    {
        $this->username_canonical = $username_canonical;

        return $this;
    }

    /**
     * Get the value of username_canonical.
     *
     * @return string
     */
    public function getUsernameCanonical()
    {
        return $this->username_canonical;
    }

    /**
     * Set the value of email.
     *
     * @param string $email
     * @return \VGMdb\ORM\Entity\User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email_canonical.
     *
     * @param string $email_canonical
     * @return \VGMdb\ORM\Entity\User
     */
    public function setEmailCanonical($email_canonical)
    {
        $this->email_canonical = $email_canonical;

        return $this;
    }

    /**
     * Get the value of email_canonical.
     *
     * @return string
     */
    public function getEmailCanonical()
    {
        return $this->email_canonical;
    }

    /**
     * Set the value of enabled.
     *
     * @param boolean $enabled
     * @return \VGMdb\ORM\Entity\User
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
     * Set the value of salt.
     *
     * @param string $salt
     * @return \VGMdb\ORM\Entity\User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get the value of salt.
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set the value of password.
     *
     * @param string $password
     * @return \VGMdb\ORM\Entity\User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of last_login.
     *
     * @param datetime $last_login
     * @return \VGMdb\ORM\Entity\User
     */
    public function setLastLogin(\DateTime $last_login)
    {
        $this->last_login = $last_login;

        return $this;
    }

    /**
     * Get the value of last_login.
     *
     * @return datetime
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }

    /**
     * Set the value of locked.
     *
     * @param boolean $locked
     * @return \VGMdb\ORM\Entity\User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get the value of locked.
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set the value of expired.
     *
     * @param boolean $expired
     * @return \VGMdb\ORM\Entity\User
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;

        return $this;
    }

    /**
     * Get the value of expired.
     *
     * @return boolean
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * Set the value of expires_at.
     *
     * @param datetime $expires_at
     * @return \VGMdb\ORM\Entity\User
     */
    public function setExpiresAt(\DateTime $expires_at)
    {
        $this->expires_at = $expires_at;

        return $this;
    }

    /**
     * Get the value of expires_at.
     *
     * @return datetime
     */
    public function getExpiresAt()
    {
        return $this->expires_at;
    }

    /**
     * Set the value of confirmation_token.
     *
     * @param string $confirmation_token
     * @return \VGMdb\ORM\Entity\User
     */
    public function setConfirmationToken($confirmation_token)
    {
        $this->confirmation_token = $confirmation_token;

        return $this;
    }

    /**
     * Get the value of confirmation_token.
     *
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmation_token;
    }

    /**
     * Set the value of password_requested_at.
     *
     * @param datetime $password_requested_at
     * @return \VGMdb\ORM\Entity\User
     */
    public function setPasswordRequestedAt(\DateTime $password_requested_at)
    {
        $this->password_requested_at = $password_requested_at;

        return $this;
    }

    /**
     * Get the value of password_requested_at.
     *
     * @return datetime
     */
    public function getPasswordRequestedAt()
    {
        return $this->password_requested_at;
    }

    /**
     * Set the value of credentials_expired.
     *
     * @param boolean $credentials_expired
     * @return \VGMdb\ORM\Entity\User
     */
    public function setCredentialsExpired($credentials_expired)
    {
        $this->credentials_expired = $credentials_expired;

        return $this;
    }

    /**
     * Get the value of credentials_expired.
     *
     * @return boolean
     */
    public function getCredentialsExpired()
    {
        return $this->credentials_expired;
    }

    /**
     * Set the value of credentials_expire_at.
     *
     * @param datetime $credentials_expire_at
     * @return \VGMdb\ORM\Entity\User
     */
    public function setCredentialsExpireAt(\DateTime $credentials_expire_at)
    {
        $this->credentials_expire_at = $credentials_expire_at;

        return $this;
    }

    /**
     * Get the value of credentials_expire_at.
     *
     * @return datetime
     */
    public function getCredentialsExpireAt()
    {
        return $this->credentials_expire_at;
    }

    /**
     * Add AuthProvider entity to collection (one to many).
     *
     * @param \VGMdb\ORM\Entity\AuthProvider $authProvider
     * @return \VGMdb\ORM\Entity\User
     */
    public function addAuthProvider(AuthProvider $authProvider)
    {
        $this->authProviders[] = $authProvider;

        return $this;
    }

    /**
     * Get AuthProvider entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthProviders()
    {
        return $this->authProviders->toArray();
    }

    /**
     * Add Role entity to collection (one to many).
     *
     * @param \VGMdb\ORM\Entity\Role $role
     * @return \VGMdb\ORM\Entity\User
     */
    public function addRole(Role $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Get Role entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    public function __sleep()
    {
        return array('id', 'username', 'username_canonical', 'email', 'email_canonical', 'enabled', 'salt', 'password', 'last_login', 'locked', 'expired', 'expires_at', 'confirmation_token', 'password_requested_at', 'credentials_expired', 'credentials_expire_at');
    }
}