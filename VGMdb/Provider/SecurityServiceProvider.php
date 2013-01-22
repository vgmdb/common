<?php

namespace VGMdb\Provider;

use VGMdb\Component\Security\Http\Authentication\AuthenticationSuccessHandler;
use VGMdb\Component\Security\Http\Authentication\AuthenticationFailureHandler;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider as BaseSecurityServiceProvider;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\Firewall\LogoutListener;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;

/**
 * Security component provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class SecurityServiceProvider extends BaseSecurityServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $that = $this;

        $app['security.secure_random'] = $app->share(function ($app) {
            return new SecureRandom($app['security.secure_random.seed'], $app['logger']);
        });

        /**
         * This adds support for invalidate_session option in /logout
         * At this time, Symfony 2.1 has a bug on PHP 5.4 where a fatal error is thrown upon logout
         * This only occurs if the session is invalidated through SessionLogoutHandler
         * invalidate_session = false will prevent the handler from getting registered
         *
         * @todo Verify status of the bug on Symfony 2.2 or 2.3
         */
        $app['security.authentication_listener.logout._proto'] = $app->protect(function ($name, $options) use ($app, $that) {
            return $app->share(function () use ($app, $name, $options, $that) {
                $that->addFakeRoute(
                    'get',
                    $tmp = isset($options['logout_path']) ? $options['logout_path'] : '/logout',
                    str_replace('/', '_', ltrim($tmp, '/'))
                );

                if (!isset($app['security.authentication.logout_handler.'.$name])) {
                    $app['security.authentication.logout_handler.'.$name] = $app['security.authentication.logout_handler._proto']($name, $options);
                }

                $listener = new LogoutListener(
                    $app['security'],
                    $app['security.http_utils'],
                    $app['security.authentication.logout_handler.'.$name],
                    $options,
                    isset($options['with_csrf']) && $options['with_csrf'] && isset($app['form.csrf_provider']) ? $app['form.csrf_provider'] : null
                );

                if (!(isset($options['invalidate_session']) && $options['invalidate_session'] === false)) {
                    $listener->addHandler(new SessionLogoutHandler());
                }

                return $listener;
            });
        });

        /**
         * Special handling for authentication exceptions thrown by the /api route.
         * Normally the exception listener redirects to an entry point, however in this case
         * we just return an exception to the client (usually 401 Unauthorized)
         *
         * @todo Create an AccessDeniedHandler that converts exceptions to appropriate responses
         */
        $app['security.exception_listener.api'] = $app->share(function ($app) {
            return new ExceptionListener(
                $app['security'],
                $app['security.trust_resolver'],
                $app['security.http_utils'],
                'api',
                null,
                null, // errorPage
                null, // AccessDeniedHandlerInterface
                $app['logger']
            );
        });

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

        $app['security.authentication.failure_handler._proto'] = $app->protect(function ($name, $options) use ($app) {
            return $app->share(function () use ($name, $options, $app) {
                return new AuthenticationFailureHandler(
                    $app,
                    $app['security.http_utils'],
                    $options,
                    $app['logger']
                );
            });
        });
    }
}
