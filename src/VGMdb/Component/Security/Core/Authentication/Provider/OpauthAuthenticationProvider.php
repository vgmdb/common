<?php

namespace VGMdb\Component\Security\Core\Authentication\Provider;

use VGMdb\Component\User\Provider\UserProvider;
use VGMdb\Component\Security\Core\Authentication\Token\OpauthToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Authentication provider handling OAuth Authentication responses.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class OpauthAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $userChecker;
    private $providerKey;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey)
    {
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
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

        if (!$this->userProvider instanceof UserProvider) {
            return null;
        }

        $user = $this->userProvider->loadUserByAuthProvider($token->provider, $token->providerId);

        if (!$user) {
            throw new BadCredentialsException('No user found for given credentials.');
        }

        $this->userChecker->checkPostAuth($user);

        $authenticatedToken = new OpauthToken($this->providerKey, $user->getRoles());
        $authenticatedToken->setAuthenticated(true);
        $authenticatedToken->setUser($user);

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OpauthToken && $this->providerKey === $token->getProviderKey();
    }
}
