<?php

namespace VGMdb\Component\OAuthServer\Model;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 */
interface TokenManagerInterface
{
    /**
     * Create a new TokenInterface.
     *
     * @return TokenInterface
     */
    function createToken();

    /**
     * Return the class name of the Token.
     *
     * @return string
     */
    function getClass();

    /**
     * Retrieve a token using a set of criteria.
     *
     * @param array $criteria
     * @return TokenInterface|null
     */
    function findTokenBy(array $criteria);

    /**
     * Retrieve a token (object) by its token string.
     *
     * @param string $token  A token.
     * @return TokenInterface|null
     */
    function findTokenByToken($token);

    /**
     * Save or update a given token.
     *
     * @param TokenInterface $token The token to save or update.
     */
    function updateToken(TokenInterface $token);

    /**
     * Delete a given token.
     *
     * @param TokenInterface $token The token to delete.
     */
    function deleteToken(TokenInterface $token);

    /**
     * Delete expired tokens.
     *
     * @return int  The number of tokens deleted.
     */
    function deleteExpired();
}
