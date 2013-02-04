<?php

namespace VGMdb\Provider;

use VGMdb\Component\User\Form\Type\RegistrationFormType;
use VGMdb\Component\User\Form\Flow\RegistrationFormFlow;
use VGMdb\Component\User\Form\Type\ResetPasswordFormType;
use VGMdb\Component\User\Form\Handler\RegistrationFormHandler;
use VGMdb\Component\User\Form\Handler\ResetPasswordFormHandler;
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
use VGMdb\Component\User\Util\UserManipulator;
use VGMdb\Component\User\Mailer\MustacheSwiftMailer;
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
 * Provides user management. Adapted from FriendsOfSymfony UserBundle.
 *
 * @author Gigablah <gigablah@vgmdb.net>
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

        $app['user_manipulator'] = $app->share(function ($app) {
            return new UserManipulator($app['user_manager']);
        });

        $app['security.encoder_factory'] = $app->share(function ($app) {
            return new EncoderFactory(array(
                'Symfony\\Component\\Security\\Core\\User\\UserInterface' => $app['security.encoder.bcrypt'],
                'VGMdb\\Component\\User\\Model\\UserInterface' => $app['security.encoder.bcrypt']
            ));
        });

        $app['security.encoder.bcrypt'] = $app->share(function ($app) {
            return new BlowfishPasswordEncoder($app['security.secure_random'], $app['user.security.bcrypt.work_factor']);
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
            $form = $app['form.factory']->create($app['user.registration.form_type']);

            return $form;
        });

        $app['user.registration.form_type'] = $app->share(function ($app) {
            return new RegistrationFormType($app['user.model.user_class']);
        });

        $app['user.registration.form_flow'] = $app->share(function ($app) {
            $flow = new RegistrationFormFlow();
            $flow->setFormType($app['user.registration.form_type']);
            $flow->setFormFactory($app['form.factory']);
            $flow->setRequest($app['request']);
            $flow->setStorage($app['form.flow.storage']);
            $flow->setEventDispatcher($app['dispatcher']);

            return $flow;
        });

        $app['user.registration.form_handler'] = $app->share(function ($app) {
            return new RegistrationFormHandler(
                $app['user.registration.form_flow'],
                $app['request'],
                $app['user_manager'],
                $app['user.mailer'],
                $app['user.token_generator']
            );
        });

        $app['user.resetpassword.form'] = $app->share(function ($app) {
            $form = $app['form.factory']->create(new ResetPasswordFormType($app['user.model.user_class']));
            return $form;
        });

        $app['user.resetpassword.form_handler'] = $app->share(function ($app) {
            return new ResetPasswordFormHandler(
                $app['user.resetpassword.form'],
                $app['request'],
                $app['user_manager'],
                $app['user.mailer']
            );
        });

        $app['user.mailer'] = $app->share(function ($app) {
            return new MustacheSwiftMailer(
                $app['mailer'],
                $app['url_generator'],
                $app['mustache'],
                $app['logger'],
                $app['user.mailer.config']
            );
        });

        $app['data.user'] = $app->protect(function ($username) use ($app) {
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
                $token = $app['form.csrf_provider']->generateCsrfToken('logout');
            } else {
                $user = $app['user_manager']->findUserByUsername($username);
                if (!$user) {
                    throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
                }
                $username = $user->getUsername();
                $roles = $user->getRoles();
                $auth = $user->getAuthProviders();
                $token = null;
            }
            $email = $user->getEmailCanonical();
            $email_hash = md5($email);

            return array(
                'username' => $username,
                'email' => $email,
                'email_hash' => $email_hash,
                'roles' => $roles,
                'tokens' => array(
                    'logout' => $token
                ),
                'urls' => array(
                    'logout' => $app['security.firewalls'][$app['user.firewall_name']]['logout']['logout_path']
                ),
                'is_authenticated' => true
            );
        });

        $app['data.login'] = $app->share(function ($app) {
            return array(
                'error' => $app['security.last_error']($app['request']),
                'last_username' => $app['session']->get('_security.last_username'),
                'tokens' => array(
                    'login' => $app['form.csrf_provider']->generateCsrfToken('authenticate'),
                    'oauth' => $app['form.csrf_provider']->generateCsrfToken('oauth')
                ),
                'urls' => array(
                    'login_check' => $app['security.firewalls'][$app['user.firewall_name']]['form']['check_path'],
                    'login_reset' => $app['security.firewalls'][$app['user.firewall_name']]['form']['reset_path'],
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
