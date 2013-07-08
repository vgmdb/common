<?php

namespace VGMdb\Component\OAuthServer\Security\Core\Authentication\Provider;

use VGMdb\Component\OAuthServer\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;

/**
 * Authentication provider handling OAuth Authentication requests.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class OAuthServerAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $userChecker;
    private $providerKey;
    private $oauthService;

    /**
     * @param UserProviderInterface $userProvider The user provider.
     * @param UserCheckerInterface  $userChecker  The user checker.
     * @param string                $providerKey
     * @param OAuth2                $oauthService The OAuth2 server service.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, OAuth2 $oauthService)
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->userProvider = $userProvider;
        $this->userChecker  = $userChecker;
        $this->providerKey  = $providerKey;
        $this->oauthService = $oauthService;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        try {
            $tokenString = $token->getToken();

            if ($accessToken = $this->oauthService->verifyAccessToken($tokenString)) {
                $scope = $accessToken->getScope();
                $user  = $accessToken->getUser();

                $roles = (null !== $user) ? $user->getRoles() : array();

                if (!empty($scope)) {
                    foreach (explode(' ', $scope) as $role) {
                        $roles[] = 'ROLE_API_' . strtoupper($role);
                    }
                }

                $authenticatedToken = new OAuthToken($this->providerKey, $roles);
                $authenticatedToken->setAuthenticated(true);
                $authenticatedToken->setToken($tokenString);

                if (null !== $user) {
                    $this->userChecker->checkPostAuth($user);
                    $authenticatedToken->setUser($user);
                }

                return $authenticatedToken;
            }
        } catch (OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }

        throw new AuthenticationException('OAuth2 authentication failed');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken && $this->providerKey === $token->getProviderKey();
    }
}
