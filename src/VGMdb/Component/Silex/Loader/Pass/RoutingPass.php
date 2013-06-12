<?php

namespace VGMdb\Component\Silex\Loader\Pass;

use VGMdb\Component\Silex\Loader\ConfigPassInterface;

/**
 * Manipulates the replacement parameters for the routing service.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RoutingPass implements ConfigPassInterface
{
    public function process(array $config)
    {
        if (isset($config['services']['routing'])) {
            $configs = array();
            foreach ($config['app.hosts'] as $key => $value) {
                $configs['routing.hosts.'.$key] = $value;
            }
            $config['services']['routing']['parameters'] = array_replace($config['services']['routing']['parameters'], $configs);
        }

        return $config;
    }
}
