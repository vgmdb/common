<?php

namespace VGMdb\Component\Security;

use VGMdb\Component\Security\EventListener\FrameOptionsListener;
use VGMdb\Component\Security\EventListener\XssProtectionListener;
use VGMdb\Component\Security\EventListener\TransportSecurityListener;
use VGMdb\Component\Security\Http\LazyFirewallMap;
use VGMdb\Component\Security\Http\Authentication\AuthenticationSuccessHandler;
use VGMdb\Component\Security\Http\Authentication\AuthenticationFailureHandler;
use VGMdb\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint;
use VGMdb\Component\Security\Core\User\StubUserProvider;
use VGMdb\Component\Routing\Generator\LazyUrlGenerator;
use Silex\Application;
use Silex\LazyUrlMatcher;
use Silex\Provider\SecurityServiceProvider as BaseSecurityServiceProvider;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Http\Firewall;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\Firewall\LogoutListener;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\Security\Http\HttpUtils;

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

        // copied straight from the Silex SecurityServiceProvider, to be removed pending a pull request
        $app['security.firewall_map.configs'] = $app->share(function ($app) {
            $positions = array('logout', 'pre_auth', 'form', 'http', 'remember_me', 'anonymous');
            $providers = array();
            $configs = array();
            foreach ($app['security.firewalls'] as $name => $firewall) {
                $entryPoint = null;
                $pattern = isset($firewall['pattern']) ? $firewall['pattern'] : null;
                $users = isset($firewall['users']) ? $firewall['users'] : array();
                $security = isset($firewall['security']) ? (Boolean) $firewall['security'] : true;
                $stateless = isset($firewall['stateless']) ? (Boolean) $firewall['stateless'] : false;
                unset($firewall['pattern'], $firewall['users'], $firewall['security'], $firewall['stateless']);

                $protected = false === $security ? false : count($firewall);

                $listeners = array('security.channel_listener');

                if ($protected) {
                    if (!isset($app['security.context_listener.'.$name])) {
                        if (!isset($app['security.user_provider.'.$name])) {
                            $app['security.user_provider.'.$name] = is_array($users) ? $app['security.user_provider.inmemory._proto']($users) : $users;
                        }

                        $app['security.context_listener.'.$name] = $app['security.context_listener._proto']($name, array($app['security.user_provider.'.$name]));
                    }

                    if (false === $stateless) {
                        $listeners[] = 'security.context_listener.'.$name;
                    }

                    $factories = array();
                    foreach ($positions as $position) {
                        $factories[$position] = array();
                    }

                    foreach ($firewall as $type => $options) {
                        if ('switch_user' === $type) {
                            continue;
                        }

                        // normalize options
                        if (!is_array($options)) {
                            if (!$options) {
                                continue;
                            }

                            $options = array();
                        }

                        if (!isset($app['security.authentication_listener.factory.'.$type])) {
                            throw new \LogicException(sprintf('The "%s" authentication entry is not registered.', $type));
                        }

                        list($providerId, $listenerId, $entryPointId, $position) = $app['security.authentication_listener.factory.'.$type]($name, $options);

                        if (null !== $entryPointId) {
                            $entryPoint = $entryPointId;
                        }

                        $factories[$position][] = $listenerId;
                        $providers[] = $providerId;
                    }

                    foreach ($positions as $position) {
                        foreach ($factories[$position] as $listener) {
                            $listeners[] = $listener;
                        }
                    }

                    $listeners[] = 'security.access_listener';

                    if (isset($firewall['switch_user'])) {
                        $app['security.switch_user.'.$name] = $app['security.authentication_listener.switch_user._proto']($name, $firewall['switch_user']);

                        $listeners[] = 'security.switch_user.'.$name;
                    }

                    if (!isset($app['security.exception_listener.'.$name])) {
                        if (null == $entryPoint) {
                            $app[$entryPoint = 'security.entry_point.'.$name.'.form'] = $app['security.entry_point.form._proto']($name, array());
                        }
                        $app['security.exception_listener.'.$name] = $app['security.exception_listener._proto']($entryPoint, $name);
                    }
                }

                $configs[$name] = array($pattern, $listeners, $protected);
            }

            $app['security.authentication_providers'] = array_map(function ($provider) use ($app) {
                return $app[$provider];
            }, array_unique($providers));

            return $configs;
        });

        $app['security.lazy_firewall_map'] = $app->share(function ($app) {
            $map = new LazyFirewallMap($app);
            foreach ($app['security.firewall_map.configs'] as $name => $config) {
                $map->add(
                    is_string($config[0]) ? new RequestMatcher($config[0]) : $config[0],
                    array_map(function ($listenerId) use ($app, $name) {
                        return function () use ($app, $name, $listenerId) {
                            $listener = $app[$listenerId];

                            if (isset($app['security.remember_me.service.'.$name])) {
                                if ($listener instanceof AbstractAuthenticationListener) {
                                    $listener->setRememberMeServices($app['security.remember_me.service.'.$name]);
                                }
                                if ($listener instanceof LogoutListener) {
                                    $listener->addHandler($app['security.remember_me.service.'.$name]);
                                }
                            }

                            return $listener;
                        };
                    }, $config[1]),
                    $config[2] ? $app['security.exception_listener.'.$name] : null
                );
            }

            return $map;
        });

        $app['security.firewall'] = $app->share(function ($app) {
            foreach ($app['security.firewalls'] as $name => $firewall) {
                if (!isset($app['security.user_provider.'.$name])) {
                    $app['security.user_provider.'.$name] = $app->share(function ($app) {
                        if (!isset($app['user_provider']) || !$app['user_provider'] instanceof UserProviderInterface) {
                            $app['user_provider'] = new StubUserProvider();
                        }

                        return $app['user_provider'];
                    });
                }
            }

            return new Firewall($app['security.lazy_firewall_map'], $app['dispatcher']);
        });

        // replace AccessMap so that it recognizes named parameters
        $app['security.access_map'] = $app->share(function ($app) {
            $map = new AccessMap();

            foreach ($app['security.access_rules'] as $rule) {
                if (isset($rule[0])) {
                    if (is_string($rule[0])) {
                        $rule[0] = new RequestMatcher($rule[0]);
                    }

                    $map->add($rule[0], (array) $rule[1], isset($rule[2]) ? $rule[2] : null);
                } else {
                    $rules = array_replace(array(
                        'path' => null,
                        'host' => null,
                        'methods' => null,
                        'ip' => null,
                        'attributes' => array(),
                        'roles' => array(),
                        'requires_channel' => null
                    ), $rule);

                    $map->add(
                        new RequestMatcher($rules['path'], $rules['host'], $rules['methods'], $rules['ip'], $rules['attributes']),
                        (array) $rules['roles'],
                        $rules['requires_channel']
                    );
                }
            }

            return $map;
        });

        // replace HttpUtils so that it loads UrlGenerator and UrlMatcher lazily
        $app['security.http_utils'] = $app->share(function ($app) {
            return new HttpUtils(
                isset($app['url_generator']) ? new LazyUrlGenerator(function () use ($app) {
                    return $app['url_generator'];
                }) : null,
                new LazyUrlMatcher(function () use ($app) {
                    return $app['url_matcher'];
                })
            );
        });

        $app['security.secure_random'] = $app->share(function ($app) {
            return new SecureRandom($app['security.secure_random.seed'], $app['logger']);
        });

        /**
         * Special handling for authentication exceptions thrown by API routes.
         * Normally the handler redirects to an entry point, however in this case
         * we just return an exception to the client (usually 401 Unauthorized)
         */
        $app['security.entry_point.form._proto'] = $app->protect(function ($name, array $options) use ($app) {
            return $app->share(function () use ($app, $options) {
                $loginPath = isset($options['login_path']) ? $options['login_path'] : '/login';
                $useForward = isset($options['use_forward']) ? $options['use_forward'] : false;

                return new FormAuthenticationEntryPoint($app, $app['security.http_utils'], $loginPath, $useForward);
            });
        });

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

        $app['security.frame_options_listener'] = $app->share(function ($app) {
            return new FrameOptionsListener($app['security.frame_options']['paths']);
        });

        $app['security.xss_protection_listener'] = $app->share(function ($app) {
            return new XssProtectionListener($app['security.xss_protection']['mode']);
        });

        $app['security.transport_security_listener'] = $app->share(function ($app) {
            return new TransportSecurityListener(
                $app['request_context'],
                $app['security.transport_security']['max_age'],
                $app['security.transport_security']['subdomains']
            );
        });
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addSubscriber($app['security.firewall']);
        $app['dispatcher']->addSubscriber($app['security.frame_options_listener']);
        $app['dispatcher']->addSubscriber($app['security.xss_protection_listener']);
        if ($app['env'] === 'prod') {
            $app['dispatcher']->addSubscriber($app['security.transport_security_listener']);
        }
    }
}
