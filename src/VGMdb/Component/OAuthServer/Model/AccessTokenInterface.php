<?php

namespace VGMdb\Component\OAuthServer\Model;

use OAuth2\Model\IOAuth2AccessToken;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
interface AccessTokenInterface extends TokenInterface, IOAuth2AccessToken
{
}
