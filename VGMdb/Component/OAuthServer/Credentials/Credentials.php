<?php

namespace VGMdb\Component\OAuthServer\Credentials;

/**
 * Basic implementation of the Credentials interface that allows callers to
 * pass in the public access key and secret access key in the constructor.
 *
 * Copyright 2010-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 */
class Credentials implements CredentialsInterface
{
    /**
     * @var string Access key ID
     */
    protected $key;

    /**
     * @var string Secret access key
     */
    protected $secret;

    /**
     * @var string Security token
     */
    protected $token;

    /**
     * @var int UNIX timestamp for token expiry
     */
    protected $ttd;

    /**
     * Get the available keys for the factory method
     *
     * @return array
     */
    public static function getConfigDefaults()
    {
        return array(
            self::KEY       => null,
            self::SECRET    => null,
            self::TOKEN     => null,
            self::TOKEN_TTD => null
        );
    }

    /**
     * Factory method for creating new credentials.  This factory method will
     * create the appropriate credentials object with appropriate decorators
     * based on the passed configuration options.
     *
     * @param array $config Options to use when instantiating the credentials
     *
     * @return CredentialsInterface
     */
    public static function factory($config = array())
    {
        // Add default key values
        foreach (self::getConfigDefaults() as $key => $value) {
            if (!isset($config[$key])) {
                $config[$key] = $value;
            }
        }

        $credentials = new static(
            $config[self::KEY],
            $config[self::SECRET],
            $config[self::TOKEN],
            $config[self::TOKEN_TTD]
        );

        // Add decorators here for this function to be actually useful

        return $credentials;
    }

    /**
     * Constructs a new Credentials object, with the specified access key and secret key
     *
     * @param string $accessKeyId     Access key ID
     * @param string $secretAccessKey Secret access key
     * @param string $token           Security token to use
     * @param int    $expiration      UNIX timestamp for when credentials expire
     */
    public function __construct($accessKeyId, $secretAccessKey, $token = null, $expiration = null)
    {
        $this->key = trim($accessKeyId);
        $this->secret = trim($secretAccessKey);
        $this->token = $token;
        $this->ttd = $expiration;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return json_encode(array(
            self::KEY       => $this->key,
            self::SECRET    => $this->secret,
            self::TOKEN     => $this->token,
            self::TOKEN_TTD => $this->ttd
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);
        $this->key    = $data[self::KEY];
        $this->secret = $data[self::SECRET];
        $this->token  = $data[self::TOKEN];
        $this->ttd    = $data[self::TOKEN_TTD];
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessKeyId()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecretKey()
    {
        return $this->secret;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityToken()
    {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiration()
    {
        return $this->ttd;
    }

    /**
     * {@inheritdoc}
     */
    public function isExpired()
    {
        return $this->ttd !== null && time() >= $this->ttd;
    }

    /**
     * {@inheritdoc}
     */
    public function setAccessKeyId($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSecretKey($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSecurityToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiration($timestamp)
    {
        $this->ttd = $timestamp;

        return $this;
    }
}
