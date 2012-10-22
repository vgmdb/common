<?php

namespace VGMdb\Provider;

use VGMdb\Component\Security\Http\Authentication\AuthenticationSuccessHandler;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider as BaseSecurityServiceProvider;

/**
 * @brief       Security component provider.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class SecurityServiceProvider extends BaseSecurityServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['security.authentication.success_handler._proto'] = $app->protect(function ($name, $options) use ($app) {
            return $app->share(function () use ($name, $options, $app) {
                $handler = new AuthenticationSuccessHandler(
                    $app['security.http_utils'],
                    $options
                );
                $handler->setProviderKey($name);

                return $handler;
            });
        });
    }
}