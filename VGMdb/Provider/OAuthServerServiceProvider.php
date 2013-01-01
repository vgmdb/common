<?php

namespace VGMdb\Provider;

use VGMdb\Component\OAuthServer\Storage\OAuthStorage;
use VGMdb\Component\OAuthServer\Form\Handler\ClientRegistrationFormHandler;
use VGMdb\Component\OAuthServer\Form\Type\ClientRegistrationFormType;
use VGMdb\Component\OAuthServer\Model\Entity\ClientManager;
use VGMdb\Component\OAuthServer\Model\Entity\AccessTokenManager;
use VGMdb\Component\OAuthServer\Model\Entity\RefreshTokenManager;
use VGMdb\Component\OAuthServer\Model\Entity\AuthCodeManager;
use VGMdb\Component\Security\Http\Firewall\OAuthListener;
use VGMdb\Component\Security\Core\Authentication\Provider\OAuthAuthenticationProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OAuth2\OAuth2;

/**
 * OAuth server-side authentication library integration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class OAuthServerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['oauth_server.config'] = array(
            OAuth2::CONFIG_SUPPORTED_SCOPES => array(
                'listing_read'
            )
        );

        $app['oauth_server.client.class'] = '';
        $app['oauth_server.access_token.class'] = '';
        $app['oauth_server.refresh_token.class'] = '';
        $app['oauth_server.auth_code.class'] = '';

        $app['oauth_server'] = $app->share(function () use ($app) {
            return new OAuth2($app['oauth_server.storage'], $app['oauth_server.config']);
        });

        $app['oauth_server.storage'] = $app->share(function () use ($app) {
            return new OAuthStorage(
                $app['oauth_server.client_manager'],
                $app['oauth_server.access_token_manager'],
                $app['oauth_server.refresh_token_manager'],
                $app['oauth_server.auth_code_manager'],
                $app['user_provider'],
                $app['security.encoder_factory']
            );
        });

        $app['oauth_server.client_manager'] = $app->share(function () use ($app) {
            return new ClientManager($app['entity_manager'], $app['oauth_server.client.class']);
        });

        $app['oauth_server.access_token_manager'] = $app->share(function () use ($app) {
            return new AccessTokenManager($app['entity_manager'], $app['oauth_server.access_token.class']);
        });

        $app['oauth_server.refresh_token_manager'] = $app->share(function () use ($app) {
            return new RefreshTokenManager($app['entity_manager'], $app['oauth_server.refresh_token.class']);
        });

        $app['oauth_server.auth_code_manager'] = $app->share(function () use ($app) {
            return new AuthCodeManager($app['entity_manager'], $app['oauth_server.auth_code.class']);
        });

        $app['oauth_server.client_registration.form'] = $app->share(function ($app) {
            $form = $app['form.factory']->create(new ClientRegistrationFormType($app['oauth_server.client.class']));
            return $form;
        });

        $app['oauth_server.client_registration.form_handler'] = $app->share(function ($app) {
            return new ClientRegistrationFormHandler(
                $app['oauth_server.client_registration.form'],
                $app['request'],
                $app['oauth_server.client_manager']
            );
        });

        // generate the authentication factory
        $app['security.authentication_listener.factory.oauth'] = $app->protect(function($name, $options) use ($app) {
            if (!isset($app['security.authentication_listener.'.$name.'.oauth'])) {
                $app['security.authentication_listener.'.$name.'.oauth'] = $app['security.authentication_listener.oauth._proto']($name, $options);
            }

            if (!isset($app['security.authentication_provider.'.$name.'.oauth'])) {
                $app['security.authentication_provider.'.$name.'.oauth'] = $app['security.authentication_provider.oauth._proto']($name);
            }
            return array(
                'security.authentication_provider.'.$name.'.oauth',
                'security.authentication_listener.'.$name.'.oauth',
                null,
                'pre_auth'
            );
        });

        $app['security.authentication_listener.oauth._proto'] = $app->protect(function ($providerKey, $options) use ($app) {
            return $app->share(function () use ($app, $providerKey, $options) {
                return new OAuthListener(
                    $app['security'],
                    $app['security.authentication_manager'],
                    $app['security.http_utils'],
                    $providerKey,
                    $app['oauth_server'],
                    new $app['oauth_server.signature.class'](),
                    $app['oauth_server.client_manager'],
                    $options,
                    $app['logger'],
                    $app['dispatcher']
                );
            });
        });

        $app['security.authentication_provider.oauth._proto'] = $app->protect(function ($name) use ($app) {
            return $app->share(function () use ($app, $name) {
                return new OAuthAuthenticationProvider(
                    $app['user_provider'],
                    $app['security.user_checker'],
                    $name,
                    $app['oauth_server']
                );
            });
        });
    }

    public function boot(Application $app)
    {
    }
}
