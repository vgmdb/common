<?php

namespace VGMdb\Component\OAuthServer\Model;

use OAuth2\Model\IOAuth2Client;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
interface ClientInterface extends IOAuth2Client
{
    /**
     * @param string $random
     */
    function setRandomId($random);

    /**
     * @return string
     */
    function getRandomId();

    /**
     * @param string $secret
     */
    function setSecret($secret);

    /**
     * @param $secret
     * @return Boolean
     */
    function checkSecret($secret);

    /**
     * @return string
     */
    function getSecret();

    /**
     * @param mixed $redirectUris
     */
    function setRedirectUris($redirectUris);

    /**
     * @param mixed $grantTypes
     */
    function setAllowedGrantTypes($grantTypes);

    /**
     * @return array
     */
    function getAllowedGrantTypes();
}
