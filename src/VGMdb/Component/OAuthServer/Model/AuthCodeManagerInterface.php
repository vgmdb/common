<?php

namespace VGMdb\Component\OAuthServer\Model;

/**
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface AuthCodeManagerInterface
{
    /**
     * Create a new auth code.
     *
     * @return AuthCodeInterface
     */
    function createAuthCode();

    /**
     * Return the class name.
     *
     * @return string
     */
    function getClass();

    /**
     * Retrieve an auth code using a set of criteria.
     *
     * @param array $criteria
     * @param AuthCodeInterface|null
     */
    function findAuthCodeBy(array $criteria);

    /**
     * Retrieve an auth code by its token.
     *
     * @param string $token
     * @param AuthCodeInterface|null
     */
    function findAuthCodeByToken($token);

    /**
     * Update a given auth code.
     *
     * @param AuthCodeInterface $authCode
     */
    function updateAuthCode(AuthCodeInterface $authCode);

    /**
     * Delete a given auth code.
     *
     * @param AuthCodeInterface $authCode
     */
    function deleteAuthCode(AuthCodeInterface $authCode);

    /**
     * Delete expired auth codes.
     *
     * @return int  The number of auth codes deleted.
     */
    function deleteExpired();
}
