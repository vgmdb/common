<?php

namespace VGMdb\Component\Silex\Loader\Pass;

use VGMdb\Component\Silex\Loader\ConfigPassInterface;

/**
 * Processes the queue configuration for each provider.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class QueuePass implements ConfigPassInterface
{
    public function process(array $config)
    {
        if (!isset($config['services']) || !is_array($config['services'])) {
            return $config;
        }

        if (isset($config['services']['queue'])) {
            $configs = array();
            foreach ($config['app.queues'] as $name => $params) {
                $params = array_replace($config['app.queue'], $params);
                $configs[$name] = array(
                    'provider' => $params['provider'],
                    'options' => array(
                        'queue' => $params['url'],
                        'sqs_options' => array(
                            'key' => $params['key'],
                            'secret' => $params['secret']
                        )
                    )
                );
            }
            $config['services']['queue']['configs'] = array_replace($config['services']['queue']['configs'], $configs);
        }

        unset($config['app.queue']);
        unset($config['app.queues']);

        return $config;
    }
}
