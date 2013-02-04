<?php

namespace VGMdb\Component\OAuthServer\Model;

use OAuth2\Model\IOAuth2Token;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
interface TokenInterface extends IOAuth2Token
{
    /**
     * @param int $timestamp
     */
    function setExpiresAt($timestamp);

    /**
     * @return int
     */
    function getExpiresAt();

    /**
     * @param string $token
     */
    function setToken($token);

    /**
     * @param string $scope
     */
    function setScope($scope);

    /**
     * @param UserInterface $user
     */
    function setUser(UserInterface $user);

    /**
     * @return UserInterface
     */
    function getUser();

    /**
     * @param ClientInterface $client
     */
    function setClient(ClientInterface $client);
}
