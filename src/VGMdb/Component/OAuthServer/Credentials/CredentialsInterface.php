<?php

namespace VGMdb\Component\OAuthServer\Credentials;

/**
 * Provides access to the credentials used for accessing API services: the
 * access key ID, secret access key, and security token. These credentials
 * are used to securely sign API requests.
 *
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 */
interface CredentialsInterface extends \Serializable
{
    /**
     * @var string Access Key ID
     */
    const KEY = 'key';

    /**
     * @var string Secret access key. This will never be transmitted over the wire.
     */
    const SECRET = 'secret';

    /**
     * @var string Security access token to use with request authentication
     */
    const TOKEN = 'token';

    /**
     * @var string UNIX timestamp for when the custom credentials expire
     */
    const TOKEN_TTD = 'token.ttd';

    /**
     * Returns the access key ID for this credentials object.
     *
     * @return string
     */
    public function getAccessKeyId();

    /**
     * Returns the secret access key for this credentials object.
     *
     * @return string
     */
    public function getSecretKey();

    /**
     * Get the associated security token if available
     *
     * @return string|null
     */
    public function getSecurityToken();

    /**
     * Get the UNIX timestamp in which the credentials will expire
     *
     * @return int|null
     */
    public function getExpiration();

    /**
     * Set the access key ID for this credentials object.
     *
     * @param string $key Access key ID
     *
     * @return self
     */
    public function setAccessKeyId($key);

    /**
     * Set the secret access key for this credentials object.
     *
     * @param string $secret Secret access key
     *
     * @return CredentialsInterface
     */
    public function setSecretKey($secret);

    /**
     * Set the security token to use with this credentials object
     *
     * @param string $token Security token
     *
     * @return self
     */
    public function setSecurityToken($token);

    /**
     * Set the UNIX timestamp in which the credentials will expire
     *
     * @param int $timestamp UNIX timestamp expiration
     *
     * @return self
     */
    public function setExpiration($timestamp);

    /**
     * Check if the credentials are expired
     *
     * @return bool
     */
    public function isExpired();
}
