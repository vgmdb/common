<?php

namespace VGMdb\Component\OAuthServer\Model;

use OAuth2\Model\IOAuth2AuthCode;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface AuthCodeInterface extends TokenInterface, IOAuth2AuthCode
{
    /**
     * @param string $redirectUri
     */
    function setRedirectUri($redirectUri);
}
