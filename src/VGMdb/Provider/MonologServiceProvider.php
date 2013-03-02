<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\Provider\MonologServiceProvider as BaseMonologServiceProvider;
use Symfony\Bridge\Monolog\Handler\ChromePHPHandler;
use Symfony\Bridge\Monolog\Handler\FirePHPHandler;
use Symfony\Component\HttpKernel\KernelEvents;
use Monolog\Handler\NullHandler;

/**
 * Monolog Provider without the cruft in boot().
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class MonologServiceProvider extends BaseMonologServiceProvider
{
    public function register(Application $app)
    {
        parent::register($app);

        $app['monolog.handlers'] = array();

        $app['monolog.handler'] = $app->share(function ($app) {
            $handlers = $app['monolog.handlers'];

            if (isset($handlers['chromephp']) && $handlers['chromephp'] === true) {
                return new ChromePHPHandler();
            }
            if (isset($handlers['firephp']) && $handlers['firephp'] === true) {
                return new FirePHPHandler();
            }

            return new NullHandler();
        });
    }

    public function boot(Application $app)
    {
        if ($app['monolog.handler'] instanceof ChromePHPHandler || $app['monolog.handler'] instanceof FirePHPHandler) {
            $app['dispatcher']->addListener(KernelEvents::RESPONSE, array($app['monolog.handler'], 'onKernelResponse'), 0);
        }
    }
}
