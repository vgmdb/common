<?php

namespace VGMdb\Component\OAuthServer\Security\Http\Firewall;

use VGMdb\Component\OAuthServer\Security\Core\Authentication\Token\OAuthToken;
use VGMdb\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;

/**
 * Stateless authentication listener handling Bearer token requests.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class BearerAuthenticationListener implements ListenerInterface
{
    private $options;
    private $logger;
    private $dispatcher;

    private $securityContext;
    private $authenticationManager;
    private $httpUtils;
    private $providerKey;
    private $successHandler;
    private $failureHandler;

    private $oauthService;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface               $securityContext       A SecurityContext instance
     * @param AuthenticationManagerInterface         $authenticationManager An AuthenticationManagerInterface instance
     * @param HttpUtils                              $httpUtils             An HttpUtilsInterface instance
     * @param string                                 $providerKey
     * @param OAuth2                                 $oauthService          OAuth2 Server
     * @param array                                  $options               An array of options
     * @param LoggerInterface                        $logger                A LoggerInterface instance
     * @param EventDispatcherInterface               $dispatcher            An EventDispatcherInterface instance
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, HttpUtils $httpUtils, $providerKey, OAuth2 $oauthService, array $options = array(), LoggerInterface $logger = null, EventDispatcherInterface $dispatcher = null)
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->httpUtils = $httpUtils;
        $this->providerKey = $providerKey;
        $this->oauthService = $oauthService;
        $this->options = $options;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        try {
            $returnValue = $this->attemptAuthentication($request);

            if (null === $returnValue) {
                if (null !== $this->logger) {
                    $this->logger->info('OAuth authentication request failed: Bad request');
                }

                return;
            }

            if ($returnValue instanceof TokenInterface) {
                if (null !== $this->logger) {
                    $this->logger->info(sprintf('User "%s" has been authenticated successfully', $returnValue->getUsername()));
                }

                return $this->securityContext->setToken($returnValue);
            }

            if ($returnValue instanceof Response) {
                $response = $returnValue;
            }
        } catch (AuthenticationException $e) {
            if ($response = $this->onFailure($event, $request, $e)) {
                $event->setResponse($response);
            } else {
                throw $e;
            }
        }
    }

    protected function attemptAuthentication(Request $request)
    {
        // the second parameter removes the authorization header if set to true
        // to chain another listener down the line, remember to set it to false
        $oauthToken = $this->oauthService->getBearerToken($request, true);

        if (null === $oauthToken) {
            return null;
        }

        $token = new OAuthToken($this->providerKey);
        $token->setToken($oauthToken);

        return $this->authenticationManager->authenticate($token);
    }

    protected function onFailure(GetResponseEvent $event, Request $request, AuthenticationException $failed)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('OAuth authentication request failed: %s', $failed->getMessage()));
        }

        $this->securityContext->setToken(null);
    }
}
