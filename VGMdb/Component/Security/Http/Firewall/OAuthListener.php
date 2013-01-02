<?php

namespace VGMdb\Component\Security\Http\Firewall;

use VGMdb\Component\Security\Core\Authentication\Provider\OAuthAuthenticationProvider;
use VGMdb\Component\Security\Core\Authentication\Token\OAuthToken;
use VGMdb\Component\Guzzle\Plugin\Signature\SignatureInterface;
use VGMdb\Component\HttpFoundation\JsonResponse;
use VGMdb\Component\OAuthServer\Model\ClientManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Guzzle\Http\Message\Request as GuzzleRequest;

/**
 * Authentication listener handling OAuth Authentication requests.
 *
 * We do not extend AbstractAuthenticationListener because this method is not session based.
 *
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class OAuthListener implements ListenerInterface
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
    private $signatureService;
    private $clientManager;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface               $securityContext       A SecurityContext instance
     * @param AuthenticationManagerInterface         $authenticationManager An AuthenticationManagerInterface instance
     * @param HttpUtils                              $httpUtils             An HttpUtilsInterface instance
     * @param string                                 $providerKey
     * @param OAuth2                                 $oauthService          OAuth2 Server
     * @param array                                  $options               An array of options for the processing of a
     *                                                                      successful, or failed authentication attempt
     * @param LoggerInterface                        $logger                A LoggerInterface instance
     * @param EventDispatcherInterface               $dispatcher            An EventDispatcherInterface instance
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, HttpUtils $httpUtils, $providerKey, OAuth2 $oauthService, SignatureInterface $signatureService, ClientManagerInterface $clientManager, array $options = array(), LoggerInterface $logger = null, EventDispatcherInterface $dispatcher = null)
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->httpUtils = $httpUtils;
        $this->providerKey = $providerKey;
        $this->oauthService = $oauthService;
        $this->signatureService = $signatureService;
        $this->clientManager = $clientManager;
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
                    $this->logger->info('Authentication request failed: Bad request');
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
            $response = $this->onFailure($event, $request, $e);
        }

        $event->setResponse($response);
    }

    protected function attemptAuthentication(Request $request)
    {
        $tokenType = 'mac';
        $oauthToken = null;

        /**
         * Step 1: Check for MAC-type token with custom headers
         *
         * @todo: move this to a more suitable class
         */
        $returnValue = $this->getMacToken($request);
        if (is_array($returnValue)) {
            list($oauthToken, $attributes, $dateHeader) = $returnValue;
        }

        /**
         * Step 2: Check for Bearer-type token
         */
        if (null === $oauthToken) {
            $oauthToken = $this->oauthService->getBearerToken($request, true);
            $tokenType = 'bearer';
        }

        if (null === $oauthToken) {
            return null;
        }

        $token = new OAuthToken($this->providerKey);
        $token->setToken($oauthToken);

        $returnValue = $this->authenticationManager->authenticate($token);

        // Step 3: Valid credentials found, now verify HMAC if applicable
        if ($returnValue instanceof TokenInterface && $tokenType === 'mac') {
            if (!$this->verifySignature($request, $attributes, $dateHeader)) {
                return null;
            }
        }

        return $returnValue;
    }

    private function getMacToken(Request $request)
    {
        $tokenHeader = $request->headers->get($this->signatureService->prefixHeader('security-token'));
        $token = trim($tokenHeader);

        if (null === $token) {
            return null;
        }

        // If date header is not within permissible range, reject immediately
        $dateHeader = $request->headers->get($this->signatureService->prefixHeader('date'));
        if (!$this->signatureService->verifyFreshness($dateHeader, 300)) {
            return null;
        }

        // Check validity of authorization header
        $authorizationHeader = null;
        if (!$request->headers->has('authorization')) {
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                $headers = array_combine(array_map('strtolower', array_keys($headers)), array_values($headers));
                if (isset($headers['authorization'])) {
                    $authorizationHeader = $headers['authorization'];
                }
            }
        } else {
            $authorizationHeader = $request->headers->get('authorization');
        }

        if (null !== $authorizationHeader) {
            preg_match(
                '/' . strtoupper($this->signatureService->prefixNamespace())
                . '-HMAC-SHA256 Credential=([^,]+), SignedHeaders=([^,]+), Signature=(.+)/',
                $authorizationHeader,
                $matches
            );
            if (count($matches) < 4) {
                return null;
            }
        }

        return array($token, $matches, $dateHeader);
    }

    private function verifySignature(Request $request, $attributes, $longDate)
    {
        $signedHeaders = explode(';', $attributes[2]);
        $headers = array();
        foreach ($signedHeaders as $header) {
            $headers[$header] = $request->headers->get($header);
        }

        $params = $request->query->all();
        foreach ($params as $key => $value) {
            $params[$key] = $key . '=' . $value;
        }
        $queryString = implode('&', $params);

        $guzzleRequest = new GuzzleRequest($request->getMethod(), $request->getRequestUri() . '?' . $queryString, $headers);

        $credentials = explode('/', $attributes[1]);
        $publicId = array_shift($credentials);

        $client = $this->clientManager->findClientByPublicId($publicId);
        if (null === $client) {
            return null;
        }
        $secretKey = $client->getSecret();

        return $this->signatureService->verifySignature($guzzleRequest, $credentials, $attributes[3], $longDate, $secretKey);
    }

    private function onFailure(GetResponseEvent $event, Request $request, AuthenticationException $failed)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Authentication request failed: %s', $failed->getMessage()));
        }

        $this->securityContext->setToken(null);

        if (null !== $previous = $failed->getPrevious()) {
            $response = new JsonResponse(json_decode($previous->getResponseBody(), true), $previous->getHttpCode());
        } else {
            $response = new JsonResponse(array(
                'error' => 'unauthorized',
                'error_description' => $failed->getMessage()
            ), $failed->getCode());
        }

        return $response;
    }
}
