<?php

namespace VGMdb\Component\Propel1;

use VGMdb\Component\Propel1\Logger\PropelLogger;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Propel ORM integration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class Propel1ServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // default options
        $app['propel1.instance_pooling'] = false;
        $app['propel1.force_master_connection'] = false;

        $app['propel1.connection'] = $app->protect(function ($name = null, $mode = 'write') use ($app) {
            $mode = ($mode === 'write') ? \Propel::CONNECTION_WRITE : \Propel::CONNECTION_READ;

            return \Propel::getConnection($name, $mode);
        });

        $app['propel1.logger'] = $app->share(function ($app) {
            return new PropelLogger($app['logger']);
        });

        $app['propel1.configuration'] = $app->share(function ($app) {
            \Propel::setConfiguration($app['propel1.options']);
            $config = \Propel::getConfiguration(\PropelConfiguration::TYPE_OBJECT);

            if ($app['debug'] && isset($app['propel1.logger'])) {
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
        if (!\Propel::isInit() && $app['propel1.configuration']) {
            \Propel::setLogger($app['propel1.logger']);
            \Propel::initialize();
            if (!$app['propel1.instance_pooling']) {
                \Propel::disableInstancePooling();
            }
            \Propel::setForceMasterConnection($app['propel1.force_master_connection']);
            spl_autoload_unregister(array('Propel', 'autoload')); // get your autoloader out of my framework

            class_alias('VGMdb\\Component\\Propel1\\BasePeer', 'BasePeer');
        }
    }
}
