<?php

namespace VGMdb\Component\OAuthServer\Model;

use VGMdb\Component\OAuthServer\Util\TokenGenerator;
use OAuth2\OAuth2;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
class Client implements ClientInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $random_id;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var array
     */
    protected $redirect_uris;

    /**
     * @var array
     */
    protected $allowed_grant_types;

    public function __construct()
    {
        $this->redirect_uris = array();
        $this->allowed_grant_types = array(
            OAuth2::GRANT_TYPE_AUTH_CODE,
            OAuth2::GRANT_TYPE_IMPLICIT,
        );

        $this->setRandomId(TokenGenerator::generateToken());
        $this->setSecret(TokenGenerator::generateToken());
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setRandomId($random)
    {
        $this->random_id = $random;
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomId()
    {
        return $this->random_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicId()
    {
        return sprintf('%s.%s', $this->getId(), $this->getRandomId());
    }

    /**
     * {@inheritdoc}
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * {@inheritdoc}
     */
    public function checkSecret($secret)
    {
        return (null === $this->secret || $secret === $this->secret);
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectUris($redirectUris)
    {
        if (!is_array($redirectUris)) {
            $redirectUris = array($redirectUris);
        }
        $this->redirect_uris = $redirectUris;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUris()
    {
        return $this->redirect_uris;
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowedGrantTypes($grantTypes)
    {
        if (!is_array($grantTypes)) {
            $grantTypes = array($grantTypes);
        }
        $this->allowed_grant_types = $grantTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedGrantTypes()
    {
        return $this->allowed_grant_types;
    }
}
