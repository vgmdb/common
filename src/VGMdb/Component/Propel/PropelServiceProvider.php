<?php

namespace VGMdb\Component\Propel;

use VGMdb\Component\Propel\Logger\PropelLogger;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Propel\Runtime\Propel;
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Runtime\Connection\ConnectionManagerMasterSlave;
use Propel\Runtime\ServiceContainer\ServiceContainerInterface;

/**
 * Propel2 ORM integration.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PropelServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        // default options
        $app['propel.options'] = array();
        $app['propel.instance_pooling'] = false;
        $app['propel.force_master_connection'] = false;

        $app['propel.connection'] = $app->protect(function ($name = null, $mode = 'write') use ($app) {
            $mode = ($mode === 'write')
                ? ServiceContainerInterface::CONNECTION_WRITE
                : ServiceContainerInterface::CONNECTION_READ;

            return Propel::getConnection($name, $mode);
        });

        $app['propel.logger'] = $app->share(function ($app) {
            return new PropelLogger($app['logger.factory']('propel'));
        });

        $app['propel.configuration'] = $app->share(function ($app) {
            $serviceContainer = Propel::getServiceContainer();
            $options = $app['propel.options']['dbal'];

            if (isset($options['connections']) && is_array($options['connections'])) {
                foreach ($options['connections'] as $name => $params) {
                    if (!is_array($params)) {
                        continue;
                    }

                    if (isset($params['driver'])) {
                        $serviceContainer->setAdapterClass($name, $params['driver']);
                        unset($params['driver']);
                    }

                    if (!$app['debug']) {
                        unset($params['classname']);
                    }

                    if (isset($params['slaves'])) {
                        $manager = new ConnectionManagerMasterSlave();
                        $manager->setReadConfiguration($params['slaves']);
                        unset($params['slaves']);
                        $manager->setWriteConfiguration($params);
                        $manager->setForceMasterConnection($app['propel.force_master_connection']);
                    } else {
                        $manager = new ConnectionManagerSingle();
                        $manager->setConfiguration($params);
                    }

                    $manager->setName($name);
                    $serviceContainer->setConnectionManager($name, $manager);

                    if ($app['debug']) {
                        $serviceContainer->setLogger($name, $app['propel.logger']);
                    }
                }

                if (isset($options['default_connection'])) {
                    $defaultDatasource = $options['default_connection'];
                } else {
                    $datasourceNames = array_keys($options['connections']);
                    $defaultDatasource = reset($datasourceNames);
                }

                $serviceContainer->setDefaultDatasource($defaultDatasource);
            }

            if ($app['debug']) {
                if (!isset($options['profiler'])) {
                    $options['profiler'] = array();
                }
                if (isset($options['profiler']['class'])) {
                    $serviceContainer->setProfilerClass($options['profiler']['class']);
                    unset($options['profiler']['class']);
                }
                // the default character is a pipe " | ", which upsets FirePHP
                $options['profiler']['outerGlue'] = '] ';
                $options['profiler']['innerGlue'] = ' [';
                $serviceContainer->setProfilerConfiguration($options['profiler']);
                $serviceContainer->setLogger('defaultLogger', $app['propel.logger']);
            }

            return $serviceContainer;
        });
    }

    public function boot(Application $app)
    {
        if ($app['propel.configuration'] && !$app['propel.instance_pooling']) {
            Propel::disableInstancePooling();
        }
    }
}
