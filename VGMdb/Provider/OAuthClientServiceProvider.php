<?php

namespace VGMdb\Provider;

use VGMdb\Component\Security\Http\Firewall\OpauthAuthenticationListener;
use VGMdb\Component\Security\Core\Authentication\Provider\OpauthAuthenticationProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint;

/**
 * Opauth authentication library integration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class OAuthClientServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['opauth'] = $app->share(function ($app) {
            $config = $app['opauth.config'];
            $config['path'] = $app['opauth.path'] . '/';
            return new \Opauth($config, false);
        });

        $app['opauth.controller'] = $app->protect(function ($strategy, $callback) use ($app) {
            if ($strategy === 'facebook' || $strategy === 'twitter' || $strategy === 'google') {
                $app['opauth']->run();
            }

            throw new NotFoundHttpException();
        });

        // generate the authentication factories
        foreach (array('facebook', 'google', 'twitter') as $type) {
            $app['security.authentication_listener.factory.opauth.'.$type] = $app->protect(function($name, $options) use ($type, $app) {
                if (!isset($app['security.authentication_listener.'.$name.'.opauth.'.$type])) {
                    $app['security.authentication_listener.'.$name.'.opauth.'.$type] = $app['security.authentication_listener.opauth._proto']($name, $type, $options);
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

        $app['security.authentication_listener.opauth._proto'] = $app->protect(function ($providerKey, $provider, $options) use ($app) {
            return $app->share(function () use ($app, $providerKey, $provider, $options) {
                if (!isset($app['security.authentication.success_handler.'.$providerKey.'.opauth.'.$provider])) {
                    $app['security.authentication.success_handler.'.$providerKey.'.opauth.'.$provider] = $app['security.authentication.success_handler._proto']($providerKey, $options);
                }

                if (!isset($app['security.authentication.failure_handler.'.$providerKey.'.opauth.'.$provider])) {
                    $app['security.authentication.failure_handler.'.$providerKey.'.opauth.'.$provider] = $app['security.authentication.failure_handler._proto']($providerKey, $options);
                }
                return new OpauthAuthenticationListener(
                    $app['security'],
                    $app['security.authentication_manager'],
                    $app['security.session_strategy'],
                    $app['security.http_utils'],
                    $providerKey,
                    $app['opauth'],
                    $app['security.trust_resolver'],
                    $app['user_manipulator'],
                    $app['security.authentication.success_handler.'.$providerKey.'.opauth.'.$provider],
                    $app['security.authentication.failure_handler.'.$providerKey.'.opauth.'.$provider],
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
                    $app['security.user_checker'],
                    $name
                );
            });
        });
    }

    public function boot(Application $app)
    {
        $app->match($app['opauth.path'], null);

        // fake route which will be handled by auth listener
        $app->match($app['opauth.path'] . '/{strategy}', null);

        // this route must be unsecured
        $app->match('/login/{strategy}/{callback}', 'opauth.controller');
    }
}
