<?php

namespace VGMdb\Component\Monolog;

use Silex\Application;
use Silex\Provider\MonologServiceProvider as BaseMonologServiceProvider;
use Symfony\Bridge\Monolog\Handler\ChromePhpHandler;
use Symfony\Bridge\Monolog\Handler\FirePHPHandler;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Filesystem\Filesystem;
use Monolog\Handler\StreamHandler;
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

            if (isset($app['monolog.logfile']) && strlen($app['monolog.logfile'])) {
                if (!file_exists($app['monolog.logfile'])) {
                    $filesystem = new Filesystem();
                    $filesystem->mkdir(dirname($app['monolog.logfile']), 0777);
                    $filesystem->touch($app['monolog.logfile']);
                }
                return new StreamHandler($app['monolog.logfile'], $app['monolog.level']);
            }
            if (isset($handlers['chromephp']) && $handlers['chromephp'] === true) {
                return new ChromePhpHandler();
            }
            if (isset($handlers['firephp']) && $handlers['firephp'] === true) {
                return new FirePHPHandler();
            }

            return new NullHandler();
        });

        $app['logger.factory'] = $app->protect(function ($name = null) use ($app) {
            static $loggers = array();

            if (!strlen(trim($name))) {
                $name = $app['monolog.name'];
            }

            if (!isset($loggers[$name])) {
                $loggers[$name] = new $app['monolog.logger.class']($name);
                $loggers[$name]->pushHandler($app['monolog.handler']);
                if ($app['debug'] && isset($app['monolog.handler.debug'])) {
                    $loggers[$name]->pushHandler($app['monolog.handler.debug']);
                }
            }

            return $loggers[$name];
        });
    }

    public function boot(Application $app)
    {
        if ($app['monolog.handler'] instanceof ChromePhpHandler || $app['monolog.handler'] instanceof FirePHPHandler) {
            $app['dispatcher']->addListener(KernelEvents::RESPONSE, array($app['monolog.handler'], 'onKernelResponse'), 0);
        }
    }
}
