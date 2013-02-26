<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\Provider\MonologServiceProvider as BaseMonologServiceProvider;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;
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
    }
}
