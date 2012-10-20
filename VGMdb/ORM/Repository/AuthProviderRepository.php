<?php

namespace VGMdb\ORM\Repository;

use Doctrine\ORM\EntityRepository;

class AuthProviderRepository extends EntityRepository
{
    const PROVIDER_FACEBOOK = 'facebook';
    const PROVIDER_TWITTER  = 'twitter';
    const PROVIDER_GOOGLE   = 'google';

    /**
     * Convert a provider string to integer id.
     *
     * @param string $provider
     * @return integer|null
     */
    public function translateProvider($provider)
    {
        $provider = strtolower($provider);
        $providerMap = array(
            self::PROVIDER_FACEBOOK => 1,
            self::PROVIDER_TWITTER  => 2,
            self::PROVIDER_GOOGLE   => 3
        );

        if (!isset($providerMap[$provider])) {
            return null;
        }

        return $providerMap[$provider];
    }
}