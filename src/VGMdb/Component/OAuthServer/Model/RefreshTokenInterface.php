<?php

namespace VGMdb\Component\OAuthServer\Model;

use OAuth2\Model\IOAuth2RefreshToken;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
interface RefreshTokenInterface extends TokenInterface, IOAuth2RefreshToken
{
}
