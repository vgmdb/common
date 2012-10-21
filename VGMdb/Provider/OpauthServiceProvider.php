<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint;
use VGMdb\Component\Security\Http\Firewall\OpauthAuthenticationListener;
use VGMdb\Component\Security\Core\Authentication\Provider\OpauthAuthenticationProvider;

/**
 * @brief       Opauth authentication library integration.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class OpauthServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['opauth'] = $app->share(function () use ($app) {
            $config = $app['opauth.config'];
            $config['path'] = $app['opauth.path'] . '/';
            return new \Opauth($config, false);
        });

        // generate the authentication factories
        foreach (array('facebook', 'google', 'twitter') as $type) {
            $app['security.authentication_listener.factory.opauth.'.$type] = $app->protect(function($name, $options) use ($type, $app) {
                if (!isset($app['security.authentication_listener.'.$name.'.opauth.'.$type])) {
                    $app['security.authentication_listener.'.$name.'.opauth.'.$type] = $app['security.authentication_listener.opauth._proto']($name, $options);
                }

                if (!isset($app['security.authentication_provider.'.$name.'.opauth'])) {
                    $app['security.authentication_provider.'.$name.'.opauth'] = $app['security.authentication_provider.opauth._proto']($name);
                }
                return array(
                    'security.authentication_provider.'.$name.'.opauth',
                    'security.authentication_listener.'.$name.'.opauth.'.$type,
                    null,
                    'pre_auth'
                );
            });
        }

        $app['security.authentication_listener.opauth._proto'] = $app->protect(function ($providerKey, $options) use ($app) {
            return $app->share(function () use ($app, $providerKey, $options) {
                if (!isset($app['security.authentication.success_handler.opauth.'.$providerKey])) {
                    $app['security.authentication.success_handler.opauth.'.$providerKey] = $app['security.authentication.success_handler._proto']($providerKey, $options);
                }

                if (!isset($app['security.authentication.failure_handler.'.$providerKey])) {
                    $app['security.authentication.failure_handler.opauth.'.$providerKey] = $app['security.authentication.failure_handler._proto']($providerKey, $options);
                }
                return new OpauthAuthenticationListener(
                    $app['security'],
                    $app['security.authentication_manager'],
                    $app['security.session_strategy'],
                    $app['security.http_utils'],
                    $providerKey,
                    $app['opauth'],
                    $app['security.authentication.success_handler.opauth.'.$providerKey],
                    $app['security.authentication.failure_handler.opauth.'.$providerKey],
                    $options,
                    $app['logger'],
                    $app['dispatcher'],
                    isset($options['with_csrf']) && $options['with_csrf'] && isset($app['form.csrf_provider']) ? $app['form.csrf_provider'] : null
                );
            });
        });

        $app['security.authentication_provider.opauth._proto'] = $app->protect(function ($name) use ($app) {
            return $app->share(function () use ($app, $name) {
                return new OpauthAuthenticationProvider(
                    $app['security.user_provider.' . $app['opauth.firewall_name']],
                    $name
                );
            });
        });
    }

    public function boot(Application $app)
    {
        // fake route which will be handled by auth listener
        $app->match('/auth/{strategy}', function() {});

        // this route must be unsecured
        $app->match('/login/{strategy}/{callback}', function ($strategy, $callback) use ($app) {
            $app['opauth']->run();
        });
    }
}