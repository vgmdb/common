<?php

namespace VGMdb\Provider;

use VGMdb\Component\HttpFoundation\Request;
use VGMdb\Component\HttpFoundation\Response;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

/**
 * @brief       Provides API route definitions.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class ApiControllerProvider implements ControllerProviderInterface
{
    private $version;

    public function __construct($version = null)
    {
        $this->version = (string) $version;
    }

    public function connect(Application $app)
    {
        $api = $app['controllers_factory'];
        $api->value('version', $this->version ?: (string) \VGMdb\Application::VERSION);

        // Rules: each method must be bound to a default route name, no duplications
        $api->get('/user/{username}', function ($username, $version) use ($app) {
            try {
                $data = $app['data.user']($username, $version);
            } catch (InsufficientAuthenticationException $e) {
                if ($username === 'me' && $app['request']->getRequestFormat() !== 'html') {
                    throw new HttpException(401, 'Unauthorised: Authentication credentials were missing or incorrect.');
                }
            }
            $view = $app['view']('userbox')->nest($app['view']('user', $data));
            return $view;
        })->bind('user' . ($this->version ? '_v' . $this->version : ''));

        return $api;
    }
}