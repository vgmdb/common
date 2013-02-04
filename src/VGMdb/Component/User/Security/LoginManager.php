<?php

namespace VGMdb\Component\User\Security;

use VGMdb\Application;
use VGMdb\Component\User\Model\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

/**
 * Abstracts process for manually logging in a user.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @copyright (c) 2010-2012 FriendsOfSymfony
 */
class LoginManager implements LoginManagerInterface
{
    private $securityContext;
    private $userChecker;
    private $sessionStrategy;
    private $app;

    public function __construct(SecurityContextInterface $context, UserCheckerInterface $userChecker, SessionAuthenticationStrategyInterface $sessionStrategy, Application $app)
    {
        $this->securityContext = $context;
        $this->userChecker = $userChecker;
        $this->sessionStrategy = $sessionStrategy;
        $this->app = $app;
    }

    final public function loginUser($firewallName, UserInterface $user, Response $response = null)
    {
        $this->userChecker->checkPostAuth($user);
        $token = $this->createToken($firewallName, $user);
        $this->sessionStrategy->onAuthentication($this->app['request'], $token);

        if (null !== $response) {
            $rememberMeServices = null;
            if (isset($this->app['security.authentication.rememberme.services.persistent.'.$firewallName])) {
                $rememberMeServices = $this->app['security.authentication.rememberme.services.persistent.'.$firewallName];
            } elseif (isset($this->app['security.authentication.rememberme.services.simplehash.'.$firewallName])) {
                $rememberMeServices = $this->app['security.authentication.rememberme.services.simplehash.'.$firewallName];
            }

            if ($rememberMeServices instanceof RememberMeServicesInterface) {
                $rememberMeServices->loginSuccess($this->app['request'], $response, $token);
            }
        }

        $this->securityContext->setToken($token);
    }

    protected function createToken($firewall, UserInterface $user)
    {
        return new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
    }
}
