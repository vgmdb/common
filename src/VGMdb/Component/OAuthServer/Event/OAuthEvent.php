<?php

namespace VGMdb\Component\OAuthServer\Event;

use VGMdb\Component\OAuthServer\Model\ClientInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
class OAuthEvent extends Event
{
    const PRE_AUTHORIZATION_PROCESS  = 'oauth_server.pre_auth';

    const POST_AUTHORIZATION_PROCESS = 'oauth_server.post_auth';

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Boolean
     */
    private $isAuthorizedClient;

    /**
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user, ClientInterface $client, $isAuthorizedClient = false)
    {
        $this->user = $user;
        $this->client = $client;
        $this->isAuthorizedClient = $isAuthorizedClient;
    }

    /**
     * return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Boolean $isAuthorizedClient
     */
    public function setAuthorizedClient($isAuthorizedClient)
    {
        $this->isAuthorizedClient = $isAuthorizedClient;
    }

    /**
     * @return Boolean
     */
    public function isAuthorizedClient()
    {
        return $this->isAuthorizedClient;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
}
