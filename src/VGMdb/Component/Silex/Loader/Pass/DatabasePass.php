<?php

namespace VGMdb\Component\Silex\Loader\Pass;

use VGMdb\Component\Silex\Loader\ConfigPassInterface;

/**
 * Processes the database configuration for each provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class DatabasePass implements ConfigPassInterface
{
    public function process(array $config)
    {
        if (!isset($config['services']) || !is_array($config['services'])) {
            return $config;
        }

        if (isset($config['services']['propel1'])) {
            $connections = array('propel' => array('datasources' => array(
                'default' => $config['services']['propel1']['options']['propel']['datasources']['default']
            )));
            foreach ($config['app.databases'] as $name => $params) {
                $params = array_replace($config['app.database'], $params);
                $connections['propel']['datasources'][$name] = array(
                    'adapter' => $params['driver'],
                    'connection' => array(
                        'dsn' => $params['driver'] . ':'
                            . ($params['host'] ? 'host=' . $params['host'] . ';' : '')
                            . ($params['port'] != 3306 ? 'port=' . $params['port'] . ';' : '')
                            . ($params['dbname'] ? 'dbname=' . $params['dbname'] . ';' : '')
                            . ($params['charset'] ? 'charset=' . $params['charset'] . ';' : ''),
                        'classname' => $config['services']['propel1']['connection_class'],
                        'user' => $params['user'],
                        'password' => $params['password']
                    )
                );
            }
            $config['services']['propel1']['options'] = array_replace($config['services']['propel1']['options'], $connections);
        }

        if (isset($config['services']['propel'])) {
            $connections = array('dbal' => array(
                'connections' => array(),
                'default_connection' => $config['services']['propel']['options']['dbal']['default_connection']
            ));
            foreach ($config['app.databases'] as $name => $params) {
                $params = array_replace($config['app.database'], $params);
                $connections['dbal']['connections'][$name] = array(
                    'driver' => $params['driver'],
                    'dsn' => $params['driver'] . ':'
                        . ($params['host'] ? 'host=' . $params['host'] . ';' : '')
                        . ($params['port'] != 3306 ? 'port=' . $params['port'] . ';' : '')
                        . ($params['dbname'] ? 'dbname=' . $params['dbname'] . ';' : '')
                        . ($params['charset'] ? 'charset=' . $params['charset'] . ';' : ''),
                    'classname' => $config['services']['propel']['connection_class'],
                    'user' => $params['user'],
                    'password' => $params['password']
                );
            }
            $config['services']['propel']['options'] = array_replace($config['services']['propel']['options'], $connections);
        }

        if (isset($config['services']['doctrine'])) {
            $connections = array();
            foreach ($config['app.databases'] as $name => $params) {
                $params = array_replace($config['app.database'], $params);
                $params['driver'] = 'pdo_' . $params['driver'];
                $connections[$name] = $params;
            }
            $config['services']['doctrine']['dbs.options'] = $connections;
        }

        unset($config['app.database']);
        unset($config['app.databases']);

        return $config;
    }
}
