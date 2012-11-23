<?php

namespace VGMdb\Component\Security\Core\Authentication\Provider;

use VGMdb\Component\Security\Core\Authentication\Token\OpauthToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Authentication provider handling OAuth Authentication requests.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class OpauthAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $userChecker;
    private $providerKey;
    private $encoderFactory;

    public function __construct(UserProviderInterface $userProvider, $providerKey)
    {
        $this->userProvider = $userProvider;
        $this->providerKey  = $providerKey;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        $user = $this->userProvider->loadUserByAuthProvider($token->provider, $token->providerId);

        if ($user) {
            $authenticatedToken = new OpauthToken($this->providerKey, $user->getRoles());
            $authenticatedToken->setAuthenticated(true);
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationServiceException('Authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OpauthToken && $this->providerKey === $token->getProviderKey();
    }
}
