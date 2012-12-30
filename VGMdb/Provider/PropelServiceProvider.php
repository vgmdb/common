<?php

namespace VGMdb\Provider;

use VGMdb\Component\Propel\Logger\PropelLogger;
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

        $app['propel.logger'] = $app->share(function () use ($app) {
            return new PropelLogger($app['logger']);
        });

        $app['propel.configuration'] = $app->share(function () use ($app) {
            \Propel::setConfiguration($app['propel.options']);
            $config = \Propel::getConfiguration(\PropelConfiguration::TYPE_OBJECT);

            if ($app['debug'] && isset($app['propel.logger'])) {
                // the default character is a pipe " | ", which upsets FirePHP
                $config->setParameter('debugpdo.logging.outerglue', '] ');
                $config->setParameter('debugpdo.logging.innerglue', ' [');
                $config->setParameter('debugpdo.logging.methods', array(
                    'PropelPDO::exec',
                    'PropelPDO::query',
                    'PropelPDO::prepare',
                    'DebugPDOStatement::execute',
                ), false);
                $config->setParameter('debugpdo.logging.details.time.enabled', true);
                $config->setParameter('debugpdo.logging.details.mem.enabled', true);
                $config->setParameter('debugpdo.logging.details.connection.enabled', true);
            }

            return $config;
        });
    }

    public function boot(Application $app)
    {
        if (!\Propel::isInit() && $app['propel.configuration']) {
            \Propel::setLogger($app['propel.logger']);
            \Propel::initialize();
            spl_autoload_unregister(array('Propel', 'autoload')); // get your autoloader out of my framework
        }
    }
}
