<?php

namespace VGMdb\Provider;

use VGMdb\Component\User\Form\Type\RegistrationFormType;
use VGMdb\Component\User\Form\Handler\RegistrationFormHandler;
use VGMdb\Component\User\Model\UserInterface;
use VGMdb\Component\User\Model\Doctrine\UserManager;
use VGMdb\Component\User\Provider\UserProvider;
use VGMdb\Component\User\Security\LoginManager;
use VGMdb\Component\User\Security\InteractiveLoginListener;
use VGMdb\Component\User\Security\Core\Encoder\BlowfishPasswordEncoder;
use VGMdb\Component\User\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use VGMdb\Component\User\Util\Canonicalizer;
use VGMdb\Component\User\Util\EmailCanonicalizer;
use VGMdb\Component\User\Util\TokenGenerator;
use VGMdb\Component\User\Mailer\MockMailer;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
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
                //$app['db'],
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
            return new Canonicalizer();
        });

        $app['user.util.email_canonicalizer'] = $app->share(function ($app) {
            return new EmailCanonicalizer();
        });

        $app['user.token_generator'] = $app->share(function ($app) {
            return new TokenGenerator();
        });

        $app['user_provider'] = $app->share(function ($app) {
            return new UserProvider($app['user_manager']);
        });

        $app['user.security.interactive_login_listener'] = $app->share(function ($app) {
            return new InteractiveLoginListener($app['user_manager']);
        });

        $app['user.security.login_manager'] = $app->share(function ($app) {
            return new LoginManager(
                $app['security'],
                $app['security.user_checker'],
                $app['security.session_strategy'],
                $app
            );
        });

        $app['user.registration.form'] = $app->share(function ($app) {
            $form = $app['form.factory']->create(new RegistrationFormType($app['user.model.user_class']));
            return $form;
        });

        $app['user.registration.form_handler'] = $app->share(function ($app) {
            return new RegistrationFormHandler(
                $app['user.registration.form'],
                $app['request'],
                $app['user_manager'],
                $app['user.mailer'],
                $app['user.token_generator']
            );
        });

        $app['user.mailer'] = $app->share(function ($app) {
            return new MockMailer($app['logger']);
        });

        $app['data.user'] = $app->protect(function ($username, $version = \VGMdb\Application::VERSION) use ($app) {
            if ($username === 'me') {
                $token = $app['security']->getToken();
                if (!($token instanceof TokenInterface)) {
                    throw new TokenNotFoundException('Token not found.');
                }
                if ($app['security.trust_resolver']->isAnonymous($token)) {
                    throw new InsufficientAuthenticationException('Not logged in.');
                }
                $user = $token->getUser();
                if (!($user instanceof UserInterface)) {
                    throw new UnsupportedUserException(sprintf('Expected an instance of %s, got %s instead.', $app['user.model.user_class'], get_class($user)));
                }
                $username = $user->getUsername();
                $roles = $token->getRoles();
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
                $roles = $user->getRoles();
                $auth = $user->getAuthProviders();
                $uid = $auth[0]->getProviderId();
                $provider = 'Facebook';
                $token = null;
            }
            $email = $user->getEmailCanonical();
            $email_hash = md5($email);

            return array(
                'username' => $username,
                'email' => $email,
                'email_hash' => $email_hash,
                'uid' => $uid,
                'provider' => $provider,
                'version' => $version,
                'roles' => $roles,
                'token' => $token,
                'urls' => array(
                    'logout' => $app['security.firewalls'][$app['user.firewall_name']]['logout']['logout_path']
                )
            );
        });

        $app['data.login'] = $app->protect(function () use ($app) {
            return array(
                'error' => $app['security.last_error']($app['request']),
                'last_username' => $app['session']->get('_security.last_username'),
                'tokens' => array(
                    'login' => $app['form.csrf_provider']->generateCsrfToken('authenticate'),
                    'oauth' => $app['form.csrf_provider']->generateCsrfToken('oauth')
                ),
                'urls' => array(
                    'login_check' => $app['security.firewalls'][$app['user.firewall_name']]['form']['check_path'],
                    'login_facebook' => $app['security.firewalls'][$app['user.firewall_name']]['opauth.facebook']['login_path'],
                    'login_twitter' => $app['security.firewalls'][$app['user.firewall_name']]['opauth.twitter']['login_path'],
                    'login_google' => $app['security.firewalls'][$app['user.firewall_name']]['opauth.google']['login_path']
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