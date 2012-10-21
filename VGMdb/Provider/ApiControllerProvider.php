<?php

namespace VGMdb\Provider;

use VGMdb\Request;
use VGMdb\Response;
use Silex\Application;
use Silex\ControllerProviderInterface;

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
        $api->get('/user/{username}',
            function ($username, $version) use ($app) {
                $data = $app['data.user']($username, $version);
                $view = $app['view']('userbox', $data);
                return $view;
            }
        )->bind('user' . ($this->version ? '_v' . $this->version : ''));

        return $api;
    }
}