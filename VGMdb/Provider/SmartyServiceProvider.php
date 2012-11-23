<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Smarty templating integration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class SmartyServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['smarty'] = $app->share(function () use ($app) {
            $smarty = new \Smarty();
            $smarty->setTemplateDir($app['smarty.template_dir']);
            $smarty->setCompileDir($app['smarty.compile_dir']);
            //$smarty->setCacheDir($app['smarty.cache_dir']);
            $smarty->setCompileId($app['locale']);
            $smarty->setCompileCheck($app['debug']);
            $smarty->cache_locking = true;

            if (isset($app['translate'])) {

            } else {

            }

            return $smarty;
        });
    }

    public function boot(Application $app)
    {
    }
}
