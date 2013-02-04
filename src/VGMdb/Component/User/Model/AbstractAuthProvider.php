<?php

namespace VGMdb\Component\User\Model;

/**
 * Auth provider basics.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
abstract class AbstractAuthProvider
{
    protected $id;

    /**
     * @var integer
     */
    protected $provider;

    /**
     * @var string
     */
    protected $provider_id;

    /**
     * @var array
     */
    protected static $providerMap = array(
        'facebook' => 1,
        'twitter'  => 2,
        'google'   => 3
    );

    /**
     * Get the numeric value of provider.
     *
     * @param string $provider
     * @return integer
     */
    public static function getProviderFromName($provider)
    {
        $provider = strtolower($provider);

        if (!isset(static::$providerMap[$provider])) {
            return 0;
        }

        return static::$providerMap[$provider];
    }

    /**
     * Get the string value of provider.
     *
     * @param integer $provider
     * @return string
     */
    public static function getNameFromProvider($provider)
    {
        $provider = intval($provider);

        $providerMap = array_flip(static::$providerMap);

        if (!isset($providerMap[$provider])) {
            return 'unknown';
        }

        return $providerMap[$provider];
    }

    public function __toString()
    {
        return (string) $this->getProvider() . '|' . $this->getProviderId();
    }
}
