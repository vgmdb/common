<?php

namespace VGMdb\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Propel ORM integration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PropelServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // default options
        $app['propel.instance_pooling'] = true;
        $app['propel.force_master_connection'] = false;

        $app['propel.connection'] = $app->protect(function ($name = null, $mode = 'write') use ($app) {
            $mode = ($mode === 'write') ? \Propel::CONNECTION_WRITE : \Propel::CONNECTION_READ;

            return \Propel::getConnection($name, $mode);
        });
    }

    public function boot(Application $app)
    {
        \Propel::setConfiguration($app['propel.options']);
        \Propel::initialize();
        spl_autoload_unregister(array('Propel', 'autoload')); // get your autoloader out of my framework
    }
}
