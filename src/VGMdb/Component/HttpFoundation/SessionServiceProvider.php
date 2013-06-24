<?php

namespace VGMdb\Component\HttpFoundation;

use VGMdb\Component\HttpFoundation\Session\Storage\Handler\NativeRedisSessionHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\Provider\SessionServiceProvider as BaseSessionServiceProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Extends Session Provider with Redis support.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class SessionServiceProvider extends BaseSessionServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['session.storage.handler'] = $app->share(function ($app) {
            if (extension_loaded('redis')) {
                return new NativeRedisSessionHandler();
            } else {
                return new NativeFileSessionHandler($app['session.storage.save_path']);
            }
        });
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if ($session && $session->isStarted()) {
            $params = session_get_cookie_params();
            $event->getResponse()->headers->setCookie(new Cookie($session->getName(), $session->getId(), 0 === $params['lifetime'] ? 0 : time() + $params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly']));

            $session->save();
        }
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addListener(KernelEvents::REQUEST, array($this, 'onEarlyKernelRequest'), 128);
        $app['dispatcher']->addListener(KernelEvents::REQUEST, array($this, 'onKernelRequest'), 192);
        $app['dispatcher']->addListener(KernelEvents::RESPONSE, array($this, 'onKernelResponse'), -96);
    }
}
