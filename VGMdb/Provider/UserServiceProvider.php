<?php

namespace VGMdb\Provider;

use VGMdb\Component\User\Model\Doctrine\UserManager;
use VGMdb\Component\User\Provider\UserProvider;
use VGMdb\Component\User\Security\InteractiveLoginListener;
use VGMdb\Component\User\Security\Core\Encoder\BlowfishPasswordEncoder;
use VGMdb\Component\User\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use VGMdb\Component\User\Util\Canonicalizer;
use VGMdb\Component\User\Util\EmailCanonicalizer;
use VGMdb\ORM\Entity\User;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @brief       Provides user management.
 * @author      Gigablah <gigablah@vgmdb.net>
 */
class UserServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['user_manager'] = $app->share(function ($app) {
            return new UserManager(
                $app['security.encoder_factory'],
                $app['user.util.username_canonicalizer'],
                $app['user.util.email_canonicalizer'],
                $app['entity_manager'],
                $app['user.model.user_class'],
                $app['user.model.auth_class']
            );
        });

        $app['security.encoder_factory'] = $app->share(function ($app) {
            return new EncoderFactory(array(
                'Symfony\\Component\\Security\\Core\\User\\UserInterface' => $app['security.encoder.bcrypt'],
                'VGMdb\\Component\\User\\Model\\UserInterface' => $app['security.encoder.bcrypt']
            ));
        });

        $app['security.encoder.bcrypt'] = $app->share(function ($app) {
            return new BlowfishPasswordEncoder($app['user.security.bcrypt.work_factor']);
        });

        $app['user.util.username_canonicalizer'] = $app->share(function ($app) {
            return new Canonicalizer;
        });

        $app['user.util.email_canonicalizer'] = $app->share(function ($app) {
            return new EmailCanonicalizer;
        });

        $app['user_provider'] = $app->share(function ($app) {
            return new UserProvider($app['user_manager']);
        });

        $app['user.security.interactive_login_listener'] = $app->share(function ($app) {
            return new InteractiveLoginListener($app['user_manager']);
        });

        $app['data.user'] = $app->protect(function ($username, $version = \VGMdb\Application::VERSION) use ($app) {
            if ($username === 'me') {
                $token = $app['security']->getToken();
                if ($app['security.trust_resolver']->isAnonymous($token)) {
                    throw new InsufficientAuthenticationException('Not logged in.');
                }
                $user = $token->getUser();
                if (!($user instanceof User)) {
                    throw new UnsupportedUserException(sprintf('Expected an instance of %s, got %s instead.', $app['user.model.user_class'], get_class($user)));
                }
                $username = $user->getUsername();
                $roles = array_map('strval', $token->getRoles());
                $auth = $user->getAuthProviders();
                $uid = $auth[0]->getProviderId();
                $provider = 'Facebook';
                $token = $app['form.csrf_provider']->generateCsrfToken('logout');
            } else {
                $user = $app['user_manager']->findUserByUsername($username);
                if (!$user) {
                    throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
                }
                $username = $user->getUsername();
                $roles = array_map('strval', $user->getRoles());
                $auth = $user->getAuthProviders();
                $uid = $auth[0]->getProviderId();
                $provider = 'Facebook';
                $token = null;
            }

            return array(
                'name' => $username,
                'uid' => $uid,
                'provider' => $provider,
                'version' => $version,
                'roles' => $roles,
                'token' => $token,
                'urls' => array(
                    'logout' => $app['security.firewalls']['master']['logout']['logout_path']
                )
            );
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addListener(
            SecurityEvents::INTERACTIVE_LOGIN,
            array($app['user.security.interactive_login_listener'], 'onSecurityInteractiveLogin'),
            8
        );
    }
}