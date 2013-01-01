<?php

namespace VGMdb\Component\Security\DataCollector;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * SecurityDataCollector.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecurityDataCollector extends DataCollector
{
    private $context;

    public function __construct(SecurityContextInterface $context = null)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if (null === $this->context) {
            $this->data = array(
                'enabled'       => false,
                'authenticated' => false,
                'token_class'   => null,
                'user'          => '',
                'user_class'    => '',
                'roles'         => array(),
            );
        } elseif (null === $token = $this->context->getToken()) {
            $this->data = array(
                'enabled'       => true,
                'authenticated' => false,
                'token_class'   => null,
                'user'          => '',
                'user_class'    => '',
                'roles'         => array(),
            );
        } else {
            $this->data = array(
                'enabled'       => true,
                'authenticated' => $token->isAuthenticated(),
                'token_class'   => get_class($token),
                'user'          => $token->getUsername(),
                'user_class'    => is_object($token->getUser()) ? get_class($token->getUser()) : '',
                'roles'         => array_map(function ($role){ return $role->getRole();}, $token->getRoles()),
            );
        }
    }

    /**
     * Checks if security is enabled.
     *
     * @return Boolean true if security is enabled, false otherwise
     */
    public function isEnabled()
    {
        return $this->data['enabled'];
    }

    /**
     * Gets the user.
     *
     * @return string The user
     */
    public function getUser()
    {
        return $this->data['user'];
    }

    /**
     * Gets the roles of the user.
     *
     * @return array The roles
     */
    public function getRoles()
    {
        return $this->data['roles'];
    }

    /**
     * Checks if the user is authenticated or not.
     *
     * @return Boolean true if the user is authenticated, false otherwise
     */
    public function isAuthenticated()
    {
        return $this->data['authenticated'];
    }

    /**
     * Get the class name of the security token.
     *
     * @return String The token
     */
    public function getTokenClass()
    {
        return $this->data['token_class'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'security';
    }
}
