<?php

namespace VGMdb\Component\OAuthServer\Model;

use OAuth2\Model\IOAuth2Token;

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
     * @param mixed $user
     */
    function setUser($user);

    /**
     * @return UserInterface
     */
    function getUser();

    /**
     * @param mixed $client
     */
    function setClient($client);
}
